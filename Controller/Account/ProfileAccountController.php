<?php

namespace Sygefor\Bundle\ApiBundle\Controller\Account;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sygefor\Bundle\CoreBundle\Entity\AbstractTrainee;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;

/**
 * This controller regroup actions related to account profile.
 *
 * @Route("/api/account")
 * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
 */
class ProfileAccountController extends Controller
{
    /**
     * Profile.
     *
     * @Route("/profile", name="api.account.profile", defaults={"_format" = "json"})
     * @Rest\View(serializerGroups={"api", "api.profile"})
     * @Method({"GET", "POST"})
     */
    public function profileAction(Request $request)
    {
        /** @var AbstractTrainee $trainee */
        $trainee = $this->getUser();
        if ($request->getMethod() === 'POST') {
            $profileTypeClass = $trainee::getProfileFormType();
            $form = $this->createForm($trainee::getProfileFormType(), $trainee);
            $data = $profileTypeClass::extractRequestData($request, $form);
            $form->submit($data, true);
            if ($form->isValid()) {
                $this->getDoctrine()->getManager()->flush();

                return array('updated' => true);
            } else {
                /* @var FormError $error */
                $parser = $this->get('sygefor_api.form_errors.parser');

                return new View(array('errors' => $parser->parseErrors($form)), 422);
            }
        }

        return $trainee;
    }
}
