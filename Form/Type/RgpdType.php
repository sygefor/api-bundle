<?php

namespace Sygefor\Bundle\ApiBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class RgpdType.
 */
class RgpdType extends AbstractType
{
	/**
	 * @param FormBuilderInterface $builder
	 * @param array                $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('cgu', CheckboxType::class, [
				'label' => 'Conditions générales d\'utilisation',
			])
			->add('consent', CheckboxType::class, [
				'label' => 'Consentement explicite d\'utilisation des données',
			])
		;
	}

	/**
	 * @param $resolver
	 */
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'csrf_protection' => false,
			'validation_groups' => array('Default', 'trainee', 'api.rgpd'),
		));
	}
}
