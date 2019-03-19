<?php

namespace Sygefor\Bundle\ApiBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Sygefor\Bundle\CoreBundle\Form\Type\AbstractTraineeType;

/**
 * Class ProfileType.
 */
class ProfileType extends AbstractType
{
	/**
	 * @param FormBuilderInterface $builder
	 * @param array                $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		parent::buildForm($builder, $options);

		$builder
			->add('newsletter', NewsletterType::class, [
				'label' => 'Inscriptions aux lettres d\'informations',
				'required' => false,
			])
			->add('cgu', CguType::class, [
				'label' => 'Conditions générales d\'utilisation'
			])
			->add('consent', ConsentType::class, [
				'label' => 'Consentement explicite d\'utilisation des données'
			])
		;
	}

	/**
	 * @return string
	 */
	public function getParent()
	{
		return AbstractTraineeType::class;
	}

	/**
	 * @param $resolver
	 */
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(array(
			'csrf_protection' => false,
			'validation_groups' => array('Default', 'trainee', 'api.profile'),
			'enable_security_check' => false,
			'allow_extra_fields' => true,
		));
	}

    /**
     * Helper : request data extractor.
     *
     * @param Request       $request
     * @param FormInterface $form
     *
     * @return array
     */
    public static function extractRequestData(Request $request, FormInterface $form)
    {
        return $request->request->all();
    }
}
