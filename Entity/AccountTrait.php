<?php

/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 5/25/16
 * Time: 10:21 AM.
 */

namespace Sygefor\Bundle\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class AccountTrait.
 */
trait AccountTrait
{
    /**
     * @ORM\Column(type="string", length=32)
     * @Serializer\Exclude
     */
    private $salt;

    /**
     * string.
     *
     * @Serializer\Exclude
     */
    private $plainPassword;

    /**
     * @ORM\Column(type="string")
     * @Serializer\Exclude
     */
    private $password;

    /**
     * @ORM\Column(name="is_active", type="boolean")
     * @Serializer\Groups({"trainee"})
     */
    private $isActive;

    /**
     * @ORM\Column(name="shibboleth_persistent_id", type="string", nullable=true)
     * @Serializer\Groups({"api.token", "api.profile"})
     */
    private $shibbolethPersistentId;

    /**
     * @ORM\Column(name="data", type="array", nullable=true)
     * @Serializer\Exclude
     */
    private $data;

    /**
     * @var bool
     * @Serializer\Exclude
     */
    private $sendCredentialsMail;

    /**
     * @var mixed
     * @Serializer\Exclude
     * This properties is used to automatically send a activation link to the trainee.
     * true or array of options
     */
    private $sendActivationMail;

	/**
	 * @ORM\Column(name="newsletter", type="boolean", options={"default": true})
	 * @Serializer\Groups({"trainee", "api.profile"})
	 */
	protected $newsletter;

	/**
	 * @ORM\Column(name="cgu", type="boolean", options={"default": false})
	 * @Serializer\Groups({"api", "api.token"})
	 */
	protected $cgu;

	/**
	 * @ORM\Column(name="consent", type="boolean", options={"default": false})
	 * @Serializer\Groups({"api", "api.token"})
	 */
	protected $consent;

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        return $this->email;
    }

    /**
     * {@inheritdoc}
     */
    public function setUsername($username)
    {
        $this->email = $username;
    }

    /**
     * @return mixed
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * @param mixed $salt
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;
    }

    /**
     * @return mixed
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * @param mixed $plainPassword
     */
    public function setPlainPassword($plainPassword)
    {
        $this->plainPassword = $plainPassword;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return mixed
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * @param mixed $isActive
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
    }

    /**
     * @return mixed
     */
    public function getShibbolethPersistentId()
    {
        return $this->shibbolethPersistentId;
    }

    /**
     * @param mixed $shibbolethPersistentId
     */
    public function setShibbolethPersistentId($shibbolethPersistentId)
    {
        $this->shibbolethPersistentId = $shibbolethPersistentId;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return bool
     */
    public function isSendCredentialsMail()
    {
        return $this->sendCredentialsMail;
    }

    /**
     * @param bool $sendCredentialsMail
     */
    public function setSendCredentialsMail($sendCredentialsMail)
    {
        $this->sendCredentialsMail = $sendCredentialsMail;
    }

    /**
     * @return mixed
     */
    public function getSendActivationMail()
    {
        return $this->sendActivationMail;
    }

    /**
     * @param mixed $sendActivationMail
     */
    public function setSendActivationMail($sendActivationMail)
    {
        $this->sendActivationMail = $sendActivationMail;
    }

	/**
	 * @return mixed
	 */
	public function getNewsletter()
	{
		return $this->newsletter;
	}

	/**
	 * @param mixed $newsletter
	 */
	public function setNewsletter($newsletter)
	{
		$this->newsletter = $newsletter;
	}

	/**
	 * @return mixed
	 */
	public function getCgu()
	{
		return $this->cgu;
	}

	/**
	 * @param mixed $cgu
	 */
	public function setCgu($cgu)
	{
		$this->cgu = $cgu;
	}

	/**
	 * @return mixed
	 */
	public function getConsent()
	{
		return $this->consent;
	}

	/**
	 * @param mixed $consent
	 */
	public function setConsent($consent)
	{
		$this->consent = $consent;
	}

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
    }

    /**
     * @see Symfony\Component\Security\Core\User\AdvancedUserInterface
     *
     * @return bool
     */
    public function isAccountNonExpired()
    {
        return true;
    }

    /**
     * @see Symfony\Component\Security\Core\User\AdvancedUserInterface
     *
     * @return bool
     */
    public function isAccountNonLocked()
    {
        return true;
    }

    /**
     * @see Symfony\Component\Security\Core\User\AdvancedUserInterface
     *
     * @return bool
     */
    public function isCredentialsNonExpired()
    {
        return true;
    }

    /**
     * @see Symfony\Component\Security\Core\User\AdvancedUserInterface
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->isActive;
    }
}
