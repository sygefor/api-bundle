<?php

namespace Sygefor\Bundle\ApiBundle\Controller\Account;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Sygefor\Bundle\CoreBundle\Entity\AbstractOrganization;
use Sygefor\Bundle\CoreBundle\Entity\AbstractInscription;
use Sygefor\Bundle\CoreBundle\Entity\Term\InscriptionStatus;
use Sygefor\Bundle\CoreBundle\Entity\AbstractTrainee;
use Sygefor\Bundle\CoreBundle\Entity\Term\EmailTemplate;
use Sygefor\Bundle\CoreBundle\Entity\AbstractSession;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * This controller regroup actions related to registration.
 *
 * @Route("/api/account")
 * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
 */
abstract class AbstractRegistrationAccountController extends Controller
{
    protected $inscriptionClass = AbstractInscription::class;

    protected $sendCheckoutNotificationTemplates = 'account/registration/authorization.pdf.twig';

    protected $authorizationTemplate = 'account/registration/authorization.pdf.twig';

    /**
     * Checkout registrations cart.
     *
     * @Route("/checkout", name="api.account.checkout", defaults={"_format" = "json"})
     * @Rest\View(serializerGroups={"api", "api.inscription"})
     * @Method("POST")
     */
    public function checkoutAction(Request $request, $sessions = array())
    {
        /** @var AbstractTrainee $trainee */
        $trainee = $this->getUser();

        $sessions = empty($sessions) ? $request->get('sessions') : $sessions;
        if (!$sessions) {
            throw new BadRequestHttpException('You must provide a list of session id.');
        }

        /** @var EntityManager $em */
        $em = $this->get('doctrine')->getManager();
        $repository = $em->getRepository(AbstractSession::class);

        // query builder
        $qb = $repository->createQueryBuilder('s')
            ->where('s.id = :session')
            ->andWhere('s.registration >= :registration')
            ->setParameter('registration', AbstractSession::REGISTRATION_PRIVATE); // limitRegistrationDate is empty OR >= NOW

        // get all sessions
        foreach ($sessions as $key => $id) {
            /** @var AbstractSession $session */
            $session = $qb
                ->setParameter('session', $id)
                ->getQuery()
                ->getOneOrNullResult();

            if (!$session) {
                throw new BadRequestHttpException('This session id is invalid : '.$id);
            }
            // check registrable
            if (!$session->isRegistrable()) {
                throw new AccessDeniedException('This session is not registrable : '.$id);
            }

            $sessions[$key] = $session;
        }
        // filter array
        $sessions = array_filter($sessions);

        // create inscriptions
        $inscriptions = array();
        $repository = $em->getRepository(AbstractInscription::class);
        foreach ($sessions as $session) {
            // try to find any existent inscription for this trainee
            /** @var AbstractInscription $inscription */
            $inscription = $repository->findOneBy(array(
                'session' => $session,
                'trainee' => $trainee,
            ));

            // if inscription do not exists OR the trainee desisted
            if (!$inscription) {
                // if not, create it
                if (!$inscription) {
                    $inscription = new $this->inscriptionClass();
                    $inscription->setTrainee($trainee);
                    $inscription->setSession($session);
                }
                $inscription->setInscriptionStatus(null); // reset the inscription status
                $em->persist($inscription);
                $inscriptions[] = $inscription;
            }
        }
        $em->flush();

        // send a recap to the trainee
        $count = count($inscriptions);
        if ($count) {
            $this->sendCheckoutNotification($inscriptions, $trainee);
        }

        // return created inscriptions
        return array('inscriptions' => $inscriptions);
    }

    /**
     * Registrations.
     *
     * @Route("/registrations", name="api.account.registrations", defaults={"_format" = "json"})
     * @Rest\View(serializerGroups={"api", "api.inscription"})
     * @Method("GET")
     */
    public function registrationsAction(Request $request)
    {
        /** @var AbstractTrainee $trainee */
        $trainee = $this->getUser();

        return $trainee->getInscriptions();
    }

    /**
     * Desist a registration.
     *
     * @Route("/registration/{id}/desist", name="api.account.registration.desist", defaults={"_format" = "json"})
     * @Rest\View(serializerGroups={"api", "api.inscription"})
     * @Method("POST")
     */
    public function desistAction($id, Request $request)
    {
        $trainee = $this->getUser();

        /** @var EntityManager $em */
        $em = $this->get('doctrine')->getManager();
        $repository = $em->getRepository($this->inscriptionClass);

        /** @var AbstractInscription $inscription */
        $inscription = $repository->findOneBy(array(
            'id' => $id,
            'trainee' => $trainee,
        ));

        if (!$inscription) {
            throw new NotFoundHttpException('Unknown registration.');
        }

        // check date
        if ($inscription->getSession()->getDateBegin() < new \DateTime()) {
            throw new BadRequestHttpException('You cannot desist from a past session.');
        }

        // check status
        if ($inscription->getInscriptionStatus()->getStatus() > InscriptionStatus::STATUS_ACCEPTED) {
            throw new BadRequestHttpException('Your registration has already been rejected.');
        }

        // ok, let's go
        if ($inscription->getInscriptionStatus()->getStatus() === InscriptionStatus::STATUS_PENDING) {
            // if the inscription is pending, just delete it
            $em->remove($inscription);
        } else {
            // else set the status to "Desist"
            $status = $this->getDesistInscriptionStatus($trainee);
            $inscription->setInscriptionStatus($status);
        }

        $em->flush();

        return array('desisted' => true);
    }

    /**
     * Download a authorization form.
     *
     * @Route("/registration/{ids}/authorization", name="api.account.registration.authorization")
     * @Method("GET")
     */
    public function authorizationAction($ids, Request $request)
    {
        $registrations = explode(',', $ids);
        $trainee = $this->getUser();

        try {
            // get forms
            $formTemplates = $this->getAuthorizationForms($trainee, $registrations, $this->authorizationTemplate);
            $forms = array();
            foreach ($formTemplates as $org => $template) {
                foreach ($template as $html) {
                    $forms[$org] = $html;
                }
            }
        } catch (\InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        // join forms & return pdf
        $html = implode('<div style="page-break-after: always;"></div>', $forms);
        $filename = 'formulaire'.(count($forms) > 1 ? 's' : '').'_autorisation.pdf';

        return new Response(
          $this->get('knp_snappy.pdf')->getOutputFromHtml($html, array('print-media-type' => null)), 200,
          array(
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"', )
        );
    }

    /**
     * @param array           $inscriptions
     * @param AbstractTrainee $trainee
     */
    protected function sendCheckoutNotification($inscriptions, $trainee)
    {
        // send a recap to the trainee
        $inscriptionIdsByOrganization = array();
        foreach ($inscriptions as $inscription) {
            $inscriptionIdsByOrganization[$inscription->getSession()
                ->getTraining()
                ->getOrganization()
                ->getId()][] = $inscription->getId();
        }

        foreach ($inscriptionIdsByOrganization as $organizationId => $inscriptionIds) {
            /** @var AbstractOrganization $org */
            $org = $this->getDoctrine()->getRepository(AbstractOrganization::class)->find($organizationId);

            /** @var QueryBuilder $qb */
            $qb = $this->getDoctrine()->getRepository(InscriptionStatus::class)->createQueryBuilder('s');
            /** @var InscriptionStatus $inscriptionStatus */
            $inscriptionStatus = $qb
                ->andWhere('s.organization = :organization')
                ->orWhere('s.organization IS NULL')
                ->andWhere('s.status = :status')
                ->setParameter('status', InscriptionStatus::STATUS_PENDING)
                ->setParameter('organization', $org)
                ->setMaxResults(1)
                ->getQuery()->execute();

            if ($inscriptionStatus) {
                /** @var EmailTemplate $checkoutEmailTemplate */
                $checkoutEmailTemplate = $this->getDoctrine()->getRepository(EmailTemplate::class)
                    ->findOneBy(array(
                        'organization' => $this->getDoctrine()->getRepository(AbstractOrganization::class)->find($organizationId),
                        'inscriptionStatus' => $inscriptionStatus,
                    ));

                // generate authorization forms
                $attachments = array();
                // send the mail if attachment fails
                try {
                    // knp_snappy doest not work locally
                    if ($this->get('kernel')->getEnvironment() !== 'dev') {
                        $organizationInscriptions = $this->getDoctrine()
                            ->getRepository($this->inscriptionClass)
                            ->findBy(array('id' => $inscriptionIds));
                        $forms = $this->getAuthorizationForms($trainee, $organizationInscriptions, $this->sendCheckoutNotificationTemplates);
                        foreach ($forms as $code => $template) {
                            foreach ($template as $key => $html) {
                                $data = $this->get('knp_snappy.pdf')
                                    ->getOutputFromHtml($html, array('print-media-type' => null));
                                $attachments[] = \Swift_Attachment::newInstance($data, 'formulaire_'.$key.$code.'.pdf', 'application/pdf');
                            }
                        }
                    }
                } catch (\Exception $e) {
                    $this->get('logger')
                        ->emergency('Attachment generation error');
                    $this->get('logger')->emergency($e->getMessage());
                }

                if ($checkoutEmailTemplate) {
                    $this->get('sygefor_core.batch.email')->execute(
                        $inscriptionIds,
                        array(
                            'targetClass' => $this->inscriptionClass,
                            'preview' => false,
                            'subject' => $checkoutEmailTemplate->getSubject(),
                            'cc' => $checkoutEmailTemplate->getCc(),
                            'message' => $checkoutEmailTemplate->getBody(),
                            'attachment' => empty($attachments) ? null : $attachments,
                            'typeUser' => get_class($this->getUser()),
                        )
                    );
                }
            }
        }
    }

    /**
     * Generate authorization forms.
     *
     * @param $trainee
     * @param $registrations
     * @param $templates
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    protected function getAuthorizationForms($trainee, $registrations, $templates)
    {
        $repository = $this->get('doctrine')->getManager()->getRepository($this->inscriptionClass);
        $sessionsByOrg = array();

        // verify & group sessions by organization
        /** @var AbstractInscription $registration */
        foreach ($registrations as $registration) {
            if (!($registration instanceof $this->inscriptionClass)) {
                $id = (int) $registration;
                $registration = $repository->find($id);
                if (!$registration) {
                    throw new \InvalidArgumentException('The registration identifier is not valid : '.$id);
                }
            }
            if ($registration->getTrainee() !== $trainee) {
                throw new \InvalidArgumentException('The registration does not belong to the trainee : '.$registration->getId());
            }
            if ($registration->getInscriptionStatus()->getMachineName() !== 'desist') {
                $sessionsByOrg[$registration->getSession()->getTraining()->getOrganization()->getId()][] = $registration->getSession();
            }
        }

        if (is_string($templates)) {
            $templates = array($templates);
        }

        // build pages
        $forms = array();
        foreach ($sessionsByOrg as $org => $sessions) {
            // prepare pdf variables
            $organization = $sessions[0]->getTraining()->getOrganization();
            $variables = array(
              'organization' => $organization,
              'trainee' => $trainee,
              'sessions' => $sessions,
            );
            foreach ($templates as $key => $template) {
                $forms[$organization->getCode()][$key] = $this->renderView($template, $variables);
            }
        }

        return $forms;
    }

    /**
     * @param AbstractTrainee $trainee
     *
     * @return InscriptionStatus|null
     */
    protected function getDesistInscriptionStatus(AbstractTrainee $trainee)
    {
        $em = $this->getDoctrine()->getManager();
        $status = $em->getRepository(InscriptionStatus::class)->findOneBy(array('machineName' => 'desist', 'organization' => null));
        if (!$status) {
            $status = $em->getRepository(InscriptionStatus::class)->findOneBy(array('machineName' => 'desist', 'organization' => $trainee->getOrganization()));
        }

        return $status;
    }
}
