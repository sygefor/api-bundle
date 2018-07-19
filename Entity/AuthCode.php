<?php

namespace Sygefor\Bundle\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\OAuthServerBundle\Entity\AuthCode as BaseAuthCode;

/**
 * @ORM\Entity
 * @ORM\Table(name="auth_code")
 */
class AuthCode extends BaseAuthCode
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Client")
     * @ORM\JoinColumn(nullable=false, onDelete="cascade")
     */
    protected $client;

    /**
     * @ORM\ManyToOne(targetEntity="Sygefor\Bundle\CoreBundle\Entity\AbstractTrainee")
     * @ORM\JoinColumn(onDelete="cascade")
     */
    protected $user;
}
