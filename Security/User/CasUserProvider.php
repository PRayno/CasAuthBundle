<?php
// src/AppBundle/Security/User/CasProvider.php
namespace PRayno\CasAuthBundle\Security\User;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

class CasUserProvider implements UserProviderInterface, CasUserCredentialStoreInterface
{

    protected $user_credentials = array();

    /**
     * @param array $credentials
     */
    public function storeUserCredentials(array $credentials) {
        if ($credentials['user']) {
            $this->user_credentials[$credentials['user']] = $credentials;
        } else {
            throw new \InvalidArgumentException('Credentials must contain a user property');
        }
    }

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

            if (!empty($this->user_credentials[$username])) {
                $attributes = $this->getCasAttributes($this->user_credentials[$username]);
            } else {
                $attributes = array();
            }
            $user = new CasUser($username, $password, $salt, $roles, $attributes);

            return $user;
        }

        throw new UsernameNotFoundException(
            sprintf('Username "%s" does not exist.', $username)
        );
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

    /**
     * @param $credentials
     * @retun array
     */
    protected function getCasAttributes($credentials) {
        $attras = array();

        // "Jasig Style" & CAS 3.0 Attributes:
        //
        //   <cas:serviceResponse xmlns:cas='http://www.yale.edu/tp/cas'>
        //     <cas:authenticationSuccess>
        //       <cas:user>jsmith</cas:user>
        //       <cas:attributes>
        //         <cas:attraStyle>Jasig</cas:attraStyle>
        //         <cas:surname>Smith</cas:surname>
        //         <cas:givenName>John</cas:givenName>
        //         <cas:memberOf>CN=Staff,OU=Groups,DC=example,DC=edu</cas:memberOf>
        //         <cas:memberOf>CN=Spanish Department,OU=Departments,OU=Groups,DC=example,DC=edu</cas:memberOf>
        //       </cas:attributes>
        //       <cas:proxyGrantingTicket>PGTIOU-84678-8a9d2sfa23casd</cas:proxyGrantingTicket>
        //     </cas:authenticationSuccess>
        //   </cas:serviceResponse>
        //
        if (isset($credentials['attributes'])) {
            foreach ($credentials['attributes'] as $attribute) {
                $name = $attribute->getName();
                $value = $attribute->__toString();
                // Make an attribute multi-valued on the second one seen.
                if (isset($attras[$name]) && !is_array($attras[$name])) {
                    $tmp = $attras[$name];
                    $attras[$name] = array($tmp);
                }
                // Add multi-valued attributes.
                if (isset($attras[$name])) {
                    $attras[$name][] = $value;
                }
                // Single-valued attributes.
                else {
                    $attras[$name] = $value;
                }
            }
        } else {
            // "Name-Value" attributes.
            //
            // Attribute format from these mailing list thread:
            // http://jasig.275507.n4.nabble.com/CAS-attributes-and-how-they-appear-in-the-CAS-response-td264272.html
            // Note: This is a less widely used format, but in use by at least two institutions.
            //
            //   <cas:serviceResponse xmlns:cas='http://www.yale.edu/tp/cas'>
            //     <cas:authenticationSuccess>
            //       <cas:user>jsmith</cas:user>
            //
            //       <cas:attribute name='attraStyle' value='Name-Value' />
            //       <cas:attribute name='surname' value='Smith' />
            //       <cas:attribute name='givenName' value='John' />
            //       <cas:attribute name='memberOf' value='CN=Staff,OU=Groups,DC=example,DC=edu' />
            //       <cas:attribute name='memberOf' value='CN=Spanish Department,OU=Departments,OU=Groups,DC=example,DC=edu' />
            //
            //       <cas:proxyGrantingTicket>PGTIOU-84678-8a9d2sfa23casd</cas:proxyGrantingTicket>
            //     </cas:authenticationSuccess>
            //   </cas:serviceResponse>
            //
            if (isset($credentials['attribute']) && isset($credentials['attribute'][0]->attributes()['name']) && isset($credentials['attribute'][0]->attributes()['value'])) {
                foreach ($credentials['attribute'] as $attribute) {
                    $name = (string)$attribute->attributes()['name'];
                    $value = (string)$attribute->attributes()['value'];
                    // Make an attribute multi-valued on the second one seen.
                    if (isset($attras[$name]) && !is_array($attras[$name])) {
                        $tmp = $attras[$name];
                        $attras[$name] = array($tmp);
                    }
                    // Add multi-valued attributes.
                    if (isset($attras[$name])) {
                        $attras[$name][] = $value;
                    }
                    // Single-valued attributes.
                    else {
                        $attras[$name] = $value;
                    }
                }
            }
            // "RubyCAS Style" attributes
            //
            //   <cas:serviceResponse xmlns:cas='http://www.yale.edu/tp/cas'>
            //     <cas:authenticationSuccess>
            //       <cas:user>jsmith</cas:user>
            //
            //       <cas:attraStyle>RubyCAS</cas:attraStyle>
            //       <cas:surname>Smith</cas:surname>
            //       <cas:givenName>John</cas:givenName>
            //       <cas:memberOf>CN=Staff,OU=Groups,DC=example,DC=edu</cas:memberOf>
            //       <cas:memberOf>CN=Spanish Department,OU=Departments,OU=Groups,DC=example,DC=edu</cas:memberOf>
            //
            //       <cas:proxyGrantingTicket>PGTIOU-84678-8a9d2sfa23casd</cas:proxyGrantingTicket>
            //     </cas:authenticationSuccess>
            //   </cas:serviceResponse>
            //
            else {
                // Test for elements other than our allowed list know to the protocol.
                $skip = array('user', 'proxyGrantingTicket');
                foreach ($credentials as $name => $value) {
                    if (!in_array($name, $skip)) {
                        $attras[$name] = $value;
                    }
                }
            }
        }

        return $attras;
    }
}
