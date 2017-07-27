<?php

namespace Sygefor\Bundle\ApiBundle\Form\Type;

use Sygefor\Bundle\CoreBundle\Form\Type\AbstractTraineeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ProfileType.
 */
abstract class AbstractProfileType extends AbstractType
{
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
        // remove extra fields
        $data = $request->request->all();
        $keys = array_keys($form->all());
        //        $keys = array_merge($keys, array('institution', 'disciplinary', 'professionalSituation'));
        //        $data = array_intersect_key($data, array_flip($keys));
        //        $data = array_merge(array("addressType" => 0), $data);
        return $data;
    }

    public function getParent()
    {
        return AbstractTraineeType::class;
    }
}
