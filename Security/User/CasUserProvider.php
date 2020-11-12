<?php
// src/AppBundle/Security/User/CasProvider.php
namespace PRayno\CasAuthBundle\Security\User;

use Security\User\CasUserProviderInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

class CasUserProvider implements CasUserProviderInterface
{
    /**
     * Provides the authenticated user a ROLE_USER
     * @param $username
     * @return CasUser
     * @throws UsernameNotFoundException
     */
    public function loadUserByUsername($username)
    {
        if ($username) {
            $password = '...';
            $salt = "";
            $roles = ["ROLE_USER"];

            return new CasUser($username, $password, $salt, $roles);
        }

        throw new UsernameNotFoundException(
            sprintf('Username "%s" does not exist.', $username)
        );
    }

    /**
     * @param string $username
     * @param array $attributes
     * @return CasUser
     */
    public function loadUserByUsernameAndAttributes(string $username, array $attributes)
    {
        /**
         * Instanciate your own User class and set its properties with attributes returned by CAS
         * $user = new User();
         * $user->setUsername($username);
         * $user->setLastname($attibutes['sn']);
         * return $user;
         */

        return $this->loadUserByUsername($username);
    }

    /**
     * @param UserInterface $user
     * @return CasUser
     * @throws UnsupportedUserException
     * @throws UsernameNotFoundException
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof CasUser) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * @param $class
     * @return bool
     */
    public function supportsClass($class)
    {
        return $class === 'PRayno\CasAuthBundle\Security\User\CasUser';
    }
}