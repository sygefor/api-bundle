<?php

namespace Sygefor\Bundle\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class AbstractController.
 */
abstract class AbstractController extends Controller
{
    /**
     * @var array
     */
    protected static $authorizedFields = array();

    /**
     * Protected function to help build authorized fields array.
     *
     * @param $source
     * @param string $prefix
     *
     * @return array
     */
    protected static function buildAuthorizedFieldsArray($source, $prefix = '')
    {
        $array = array();
        foreach (static::$authorizedFields[$source] as $key) {
            $array[] = ($prefix ? $prefix.'.' : '').$key;
        }

        return $array;
    }
}
