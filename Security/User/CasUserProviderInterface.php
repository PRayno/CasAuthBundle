<?php


namespace PRayno\CasAuthBundle\Security\User;


use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Interface CasUserProviderInterface
 * @package Security\User
 */
interface CasUserProviderInterface extends UserProviderInterface
{
    /**
     * @param string $username
     * @param array $attributes
     * @return UserInterface
     * @throws UsernameNotFoundException if the user is not found
     */
    public function loadUserByUsernameAndAttributes(string $username, array $attributes);

}