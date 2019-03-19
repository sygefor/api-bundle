<?php

namespace Sygefor\Bundle\ApiBundle\Controller\Account;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sygefor\Bundle\ApiBundle\Form\Type\RgpdType;
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
     * @var Request $request
     *
     * @Route("/profile", name="api.account.profile", defaults={"_format" = "json"})
     * @Rest\View(serializerGroups={"api", "api.profile"})
     * @Method({"GET", "POST"})
     *
     * @return mixed
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
            }
            else {
                /* @var FormError $error */
                $parser = $this->get('sygefor_api.form_errors.parser');

                return new View(array('errors' => $parser->parseErrors($form)), 422);
            }
        }

        return $trainee;
    }

	/**
	 * @var Request $request
	 *
	 * @Route("/rgpd", name="api.account.rgpd", defaults={"_format" = "json"})
	 * @Rest\View(serializerGroups={"api", "api.profile"})
	 *
	 * @return mixed
	 */
	public function rgpdAction(Request $request)
	{
		/** @var AbstractTrainee $trainee */
		$trainee = $this->getUser();
		$form = $this->createForm(new RgpdType(), $trainee);
		if ($request->getMethod() == 'POST') {
			$form->submit($request->request->all(), true);
			$form->handleRequest($request);
			if ($form->isValid()) {
				$this->getDoctrine()->getManager()->flush();
				return ['updated' => true];
			}
			else {
				/** @var FormError $error */
				$parser = $this->get('sygefor_api.form_errors.parser');

				return new View(['errors' => $parser->parseErrors($form)], 422);
			}
		}

		return $trainee;
	}
}
