<?php

namespace PRayno\CasAuthBundle\Event;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;

class LogoutSuccessHandler implements LogoutSuccessHandlerInterface
{
    private $logoutUrl;

    public function __construct(string $logoutUrl)
    {
        $this->logoutUrl = $logoutUrl;
    }

    public function onLogoutSuccess(Request $request)
    {
        return new RedirectResponse($this->logoutUrl);
    }
}