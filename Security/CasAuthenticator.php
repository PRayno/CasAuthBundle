<?php
// src/AppBundle/Security/TokenAuthenticator.php
namespace PRayno\CasAuthBundle\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class CasAuthenticator extends AbstractGuardAuthenticator
{
    private $server_login_url;
    private $server_validation_url;
    private $xml_namespace;

    public function __construct($config)
    {
        $this->server_login_url = $config['server_login_url'];
        $this->server_validation_url = $config['server_validation_url'];
        $this->xml_namespace = $config['xml_namespace'];
    }

    /**
     * Called on every request. Return whatever credentials you want,
     * or null to stop authentication.
     */
    public function getCredentials(Request $request)
    {
        if ($request->get('ticket'))
        {
            // Validate ticket
            $string = file_get_contents($this->server_validation_url.'?ticket='.$request->get('ticket').'&service='.$request->getUri());

            $xml = new \SimpleXMLElement($string, 0, false, $this->xml_namespace, TRUE);

            if (isset($xml->authenticationSuccess))
            {
                return (array) $xml->authenticationSuccess;
            }
        }

        return null;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        if (isset($credentials['user']))
            return $userProvider->loadUserByUsername($credentials['user']);
        else
            return false;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $data = array(
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData())
        );

        return new JsonResponse($data, 403);
    }

    /**
     * Called when authentication is needed, but it's not sent
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new RedirectResponse($this->server_login_url.'?service='.$request->getUri());
    }

    public function supportsRememberMe()
    {
        return false;
    }
}