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
			->add('cgu', CguType::class, [
				'label' => 'Conditions générales d\'utilisation'
			])
			->add('consent', ConsentType::class, [
				'label' => 'Consentement de l\'utilisation de mes données',
				'widget_checkbox_label' => 'label',
				'help_block' => 'En cochant cette case, j\'accepte que les données recueillies soient utilisées dans le cadre de mon inscription à des événements organisés et proposés par la plateforme.',
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
