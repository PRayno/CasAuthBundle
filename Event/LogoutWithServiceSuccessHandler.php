<?php
/**
 * Usage instructions.
 *
 * Add this to your config/services.yaml file
 *
 * ```yaml
 * # config/services.yaml
 * app.logout_success_handler:
 *     class: PRayno\CasAuthBundle\Event\LogoutWithServiceSuccessHandler
 *     arguments:
 *         $casLogoutUrl: "https://cas.example.com/cas/logout"
 *         $routeName: "homepage"
 *         $routeParameters: { "parameter": "value" }  # optional
 * ```
 *
 * And add this to your firewall config.  Don't forget to define a /logout route
 * ```yaml
 * # config/packages/security.yaml
 * security:
 *     firewalls:
 *         main:
 *             logout:
 *                 path: /logout
 *                 success_handler: app.logout_success_handler
 * ```
 */

namespace PRayno\CasAuthBundle\Event;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;

/**
 * This class will be used to include a service URL to CAS.  This is useful
 * when you want your users to be able to return when they log back in
 * to the CAS server.
 *
 * Intended to work the same way as  phpCAS::logoutWithRedirectService($url);
 */
class LogoutWithServiceSuccessHandler implements LogoutSuccessHandlerInterface
{
    /**
     * URL to the CAS Logout page
     * i.e. https://cas.example.com/cas/logout.
     *
     * @var string
     */
    private $casLogoutUrl;
    /**
     * @var RouterInterface
     */
    private $router;
    /**
     * Route to generate a URL to come back to.
     *
     * @var string
     */
    private $routeName;
    /**
     * Any required route parameters.
     */
    private $routeParameters;

    /**
     * @param string          $casLogoutUrl    Logout URL
     * @param string          $routeName       Route name to return to after logout
     * @param array           $routeParameters Route parameters
     * @param RouterInterface $router          Symfony router to generate a return URL
     */
    public function __construct(string $casLogoutUrl, string $routeName, array $routeParameters = [], RouterInterface $router)
    {
        $this->router = $router;
        $this->casLogoutUrl = $casLogoutUrl;
        $this->routeName = $routeName;
        $this->routeParameters = $routeParameters;
    }

    /**
     * Redirects to the CAS server, but with a service parameter that directs the
     * user back here if they wish.
     */
    public function onLogoutSuccess(Request $request)
    {
        $serviceUrl = $this->router->generate($this->routeName, $this->routeParameters, RouterInterface::ABSOLUTE_URL);

        return new RedirectResponse($this->casLogoutUrl.'?'.http_build_query(['service' => $serviceUrl]));
    }
}
