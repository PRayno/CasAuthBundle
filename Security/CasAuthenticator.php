<?php
// src/AppBundle/Security/TokenAuthenticator.php
namespace PRayno\CasAuthBundle\Security;

use GuzzleHttp\Client;
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
    protected $server_login_url;
    protected $server_validation_url;
    protected $xml_namespace;
    protected $username_attribute;
    protected $query_ticket_parameter;
    protected $query_service_parameter;
    protected $options;

    /**
     * Process configuration
     * @param array $config
     */
    public function __construct($config)
    {
        $this->server_login_url = $config['server_login_url'];
        $this->server_validation_url = $config['server_validation_url'];
        $this->xml_namespace = $config['xml_namespace'];
        $this->username_attribute = $config['username_attribute'];
        $this->query_service_parameter = $config['query_service_parameter'];
        $this->query_ticket_parameter = $config['query_ticket_parameter'];
        $this->options = $config['options'];
    }

    /**
     * Does the authenticator support the given Request?
     *
     * If this returns false, the authenticator will be skipped.
     *
     * @param Request $request
     *
     * @return bool
     */
    public function supports(Request $request)
    {
        return (bool) $request->get($this->query_ticket_parameter);
    }

    /**
     * Called on every request. Return whatever credentials you want,
     * or null to stop authentication.
     */
    public function getCredentials(Request $request)
    {
        $url = $this->server_validation_url.'?'.$this->query_ticket_parameter.'='.
            $request->get($this->query_ticket_parameter).'&'.
            $this->query_service_parameter.'='.urlencode($this->removeCasTicket($request->getUri()));

        $client = new Client();
        $response = $client->request('GET', $url, $this->options);

        $string = $response->getBody()->getContents();

        $xml = new \SimpleXMLElement($string, 0, false, $this->xml_namespace, true);

        if (isset($xml->authenticationSuccess)) {
            return (array) $xml->authenticationSuccess;
        }
    }

    /**
     * Calls the UserProvider providing a valid User
     * @param array $credentials
     * @param UserProviderInterface $userProvider
     * @return bool
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        if (isset($credentials[$this->username_attribute])) {
            return $userProvider->loadUserByUsername($credentials[$this->username_attribute]);
        } else {
            return null;
        }
    }

    /**
     * Mandatory but not in use in a remote authentication
     * @param $credentials
     * @param UserInterface $user
     * @return bool
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    /**
     * Mandatory but not in use in a remote authentication
     * @param Request $request
     * @param TokenInterface $token
     * @param $providerKey
     * @return null
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // If authentication was successful, redirect to the current URI with
        // the ticket parameter removed so that it is hidden from end-users.
        if ($request->query->has($this->query_ticket_parameter)) {
            return new RedirectResponse($this->removeCasTicket($request->getUri()));
        } else {
            return null;
        }
    }

    /**
     * Mandatory but not in use in a remote authentication
     * @param Request $request
     * @param AuthenticationException $exception
     * @return JsonResponse
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $data = array(
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData())
        );

        return new JsonResponse($data, 403);
    }

    /**
     * Called when authentication is needed, redirect to your CAS server authentication form
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new RedirectResponse($this->server_login_url.'?'.$this->query_service_parameter.'='.urlencode($request->getUri()));
    }

    /**
     * Mandatory but not in use in a remote authentication
     * @return bool
     */
    public function supportsRememberMe()
    {
        return false;
    }

    /**
     * Strip the CAS 'ticket' parameter from a uri.
     */
    protected function removeCasTicket($uri) {
      $parsed_url = parse_url($uri);
      // If there are no query parameters, then there is nothing to do.
      if (empty($parsed_url['query'])) {
          return $uri;
      }
      parse_str($parsed_url['query'], $query_params);
      // If there is no 'ticket' parameter, there is nothing to do.
      if (!isset($query_params[$this->query_ticket_parameter])) {
          return $uri;
      }
      // Remove the ticket parameter and rebuild the query string.
      unset($query_params[$this->query_ticket_parameter]);
      if (empty($query_params)) {
          unset($parsed_url['query']);
      } else {
          $parsed_url['query'] = http_build_query($query_params);
      }

      // Rebuild the URI from the parsed components.
      // Source: https://secure.php.net/manual/en/function.parse-url.php#106731
      $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
      $host     = isset($parsed_url['host']) ? $parsed_url['host'] : '';
      $port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
      $user     = isset($parsed_url['user']) ? $parsed_url['user'] : '';
      $pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : '';
      $pass     = ($user || $pass) ? "$pass@" : '';
      $path     = isset($parsed_url['path']) ? $parsed_url['path'] : '';
      $query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
      $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';
      return "$scheme$user$pass$host$port$path$query$fragment";
    }
}
