<?php
/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 3/19/19
 * Time: 11:41 AM
 */

namespace Sygefor\Bundle\ApiBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

/**
 * Class ConsentType.
 */
class ConsentType extends AbstractType
{
	/**
	 * @return string
	 */
	public function getParent()
	{
		return CheckboxType::class;
	}

	public function getName()
	{
		return 'consent';
	}
}
