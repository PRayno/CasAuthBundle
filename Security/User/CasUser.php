<?php

// src/AppBundle/Security/User/CasUser.php
namespace PRayno\CasAuthBundle\Security\User;

use Symfony\Component\Security\Core\User\UserInterface;

class CasUser implements UserInterface
{
    private $username;
    private $password;
    private $salt;
    private $roles;

    /**
     * @param string $username
     * @param string $password
     * @param string $salt
     * @param array $roles
     */
    public function __construct($username, $password, $salt, array $roles)
    {
        $this->username = $username;
        $this->password = $password;
        $this->salt = $salt;
        $this->roles = $roles;
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
}