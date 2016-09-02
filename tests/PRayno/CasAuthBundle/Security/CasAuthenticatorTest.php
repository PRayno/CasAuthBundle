<?php

namespace Tests\PRayno\CasAuthBundle\Security;

use PRayno\CasAuthBundle\Security\CasAuthenticator;
use PRayno\CasAuthBundle\Security\User\CasUserProvider;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpFoundation\Session\Session;

class CasAuthenticatorTest extends \PHPUnit_Framework_TestCase {

    public function setUp() {
        // Setup your guzzle client and mock
        $this->mockCasServer = new MockHandler();
        $handler = HandlerStack::create($this->mockCasServer);
        $client = new Client(['handler' => $handler]);

        $this->authenticator = new CasAuthenticator(array(
              'server_login_url' => 'https://cas.example.com/cas/',
              'server_validation_url' => 'https://cas.example.com/cas/serviceValidate',
              'server_logout_url' => 'https://cas.example.com/cas/logout',
              'xml_namespace' => 'cas',
              'options' => array(),
              'username_attribute' => 'user',
              'query_ticket_parameter' => 'ticket',
              'query_service_parameter' => 'service'
            ),
            $client);

        $this->provider = new CasUserProvider(new Session(new MockArraySessionStorage()));
      }

    public function test_get_user_with_name_only() {
        // Create a mock response.
        $response = new Response(
            200,
            array('Content-Type' => 'text/xml'),
            '<?xml version="1.0" encoding="UTF-8"?>
<cas:serviceResponse xmlns:cas="http://www.yale.edu/tp/cas">
    <cas:authenticationSuccess>
        <cas:user>testuser</cas:user>
    </cas:authenticationSuccess>
</cas:serviceResponse>'
        );
        $this->mockCasServer->append($response);

        $request = Request::create('http://app.example.com/?ticket=ABC123-1', 'GET');

        // Get the credentials.
        $credentials = $this->authenticator->getCredentials($request);
        $this->assertNotEmpty($credentials);
        $this->assertEquals('testuser', $credentials['user']);

        // Get the user object for the credentials.
        $user = $this->authenticator->getUser($credentials, $this->provider);
        $this->assertEquals('testuser', $user->getUsername());
        $this->assertEquals(array('ROLE_USER'), $user->getRoles());
    }

    public function test_get_user_with_jasig_attributes() {
        // Create a mock response.
        $response = new Response(
            200,
            array('Content-Type' => 'text/xml'),
            '<?xml version="1.0" encoding="UTF-8"?>
<cas:serviceResponse xmlns:cas="http://www.yale.edu/tp/cas">
    <cas:authenticationSuccess>
        <cas:user>testuser</cas:user>
        <cas:attributes>
            <cas:surname>Smith</cas:surname>
            <cas:givenName>John</cas:givenName>
            <cas:memberOf>CN=Staff,OU=Groups,DC=example,DC=edu</cas:memberOf>
            <cas:memberOf>CN=Spanish Department,OU=Departments,OU=Groups,DC=example,DC=edu</cas:memberOf>
        </cas:attributes>
    </cas:authenticationSuccess>
</cas:serviceResponse>'
        );
        $this->mockCasServer->append($response);

        $request = Request::create('http://app.example.com/?ticket=ABC123-1', 'GET');

        // Get the credentials.
        $credentials = $this->authenticator->getCredentials($request);
        $this->assertNotEmpty($credentials);
        $this->assertEquals('testuser', $credentials['user']);

        // Get the user object for the credentials.
        $user = $this->authenticator->getUser($credentials, $this->provider);
        $this->assertEquals('testuser', $user->getUsername());
        $this->assertEquals(array('ROLE_USER'), $user->getRoles());
        $this->assertEquals('Smith', $user->getAttribute('surname'));
        $this->assertEquals('John', $user->getAttribute('givenName'));
        $this->assertEquals(array('CN=Staff,OU=Groups,DC=example,DC=edu', 'CN=Spanish Department,OU=Departments,OU=Groups,DC=example,DC=edu'), $user->getAttribute('memberOf'));
    }

    public function test_get_user_with_name_value_attributes() {
        // Create a mock response.
        $response = new Response(
            200,
            array('Content-Type' => 'text/xml'),
            '<?xml version="1.0" encoding="UTF-8"?>
<cas:serviceResponse xmlns:cas="http://www.yale.edu/tp/cas">
    <cas:authenticationSuccess>
        <cas:user>testuser</cas:user>
        <cas:attribute name="surname" value="Smith" />
        <cas:attribute name="givenName" value="John" />
        <cas:attribute name="memberOf" value="CN=Staff,OU=Groups,DC=example,DC=edu" />
        <cas:attribute name="memberOf" value="CN=Spanish Department,OU=Departments,OU=Groups,DC=example,DC=edu" />
    </cas:authenticationSuccess>
</cas:serviceResponse>'
        );
        $this->mockCasServer->append($response);

        $request = Request::create('http://app.example.com/?ticket=ABC123-1', 'GET');

        // Get the credentials.
        $credentials = $this->authenticator->getCredentials($request);
        $this->assertNotEmpty($credentials);
        $this->assertEquals('testuser', $credentials['user']);

        // Get the user object for the credentials.
        $user = $this->authenticator->getUser($credentials, $this->provider);
        $this->assertEquals('testuser', $user->getUsername());
        $this->assertEquals(array('ROLE_USER'), $user->getRoles());
        $this->assertEquals('Smith', $user->getAttribute('surname'));
        $this->assertEquals('John', $user->getAttribute('givenName'));
        $this->assertEquals(array('CN=Staff,OU=Groups,DC=example,DC=edu', 'CN=Spanish Department,OU=Departments,OU=Groups,DC=example,DC=edu'), $user->getAttribute('memberOf'));
    }

    public function test_get_user_with_rubycas_attributes() {
        // Create a mock response.
        $response = new Response(
            200,
            array('Content-Type' => 'text/xml'),
            '<?xml version="1.0" encoding="UTF-8"?>
<cas:serviceResponse xmlns:cas="http://www.yale.edu/tp/cas">
    <cas:authenticationSuccess>
        <cas:user>testuser</cas:user>
        <cas:surname>Smith</cas:surname>
        <cas:givenName>John</cas:givenName>
        <cas:memberOf>CN=Staff,OU=Groups,DC=example,DC=edu</cas:memberOf>
        <cas:memberOf>CN=Spanish Department,OU=Departments,OU=Groups,DC=example,DC=edu</cas:memberOf>
    </cas:authenticationSuccess>
</cas:serviceResponse>'
        );
        $this->mockCasServer->append($response);

        $request = Request::create('http://app.example.com/?ticket=ABC123-1', 'GET');

        // Get the credentials.
        $credentials = $this->authenticator->getCredentials($request);
        $this->assertNotEmpty($credentials);
        $this->assertEquals('testuser', $credentials['user']);

        // Get the user object for the credentials.
        $user = $this->authenticator->getUser($credentials, $this->provider);
        $this->assertEquals('testuser', $user->getUsername());
        $this->assertEquals(array('ROLE_USER'), $user->getRoles());
        $this->assertEquals('Smith', $user->getAttribute('surname'));
        $this->assertEquals('John', $user->getAttribute('givenName'));
        $this->assertEquals(array('CN=Staff,OU=Groups,DC=example,DC=edu', 'CN=Spanish Department,OU=Departments,OU=Groups,DC=example,DC=edu'), $user->getAttribute('memberOf'));
    }


}
