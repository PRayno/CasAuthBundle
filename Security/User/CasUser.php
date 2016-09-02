<?php

// src/AppBundle/Security/User/CasUser.php
namespace PRayno\CasAuthBundle\Security\User;

use Symfony\Component\Security\Core\User\UserInterface;
use PRayno\CasAuthBundle\Security\User\CasUserAttributesInterface;

class CasUser implements UserInterface, CasUserAttributesInterface
{
    private $username;
    private $password;
    private $salt;
    private $roles;
    private $attributes;

    /**
     * @param $username
     * @param $password
     * @param $salt
     * @param array $roles
     */
    public function __construct($username, $password, $salt, array $roles, array $attributes = array())
    {
        $this->username = $username;
        $this->password = $password;
        $this->salt = $salt;
        $this->roles = $roles;
        $this->attributes = $attributes;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @return mixed
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     *
     */
    public function eraseCredentials()
    {

    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @return mixed string, array, or null if not found.
     */
    public function getAttribute($name)
    {
        if (isset($this->attributes[$name])) {
            return $this->attributes[$name];
        } else {
            return null;
        }

    }
}
