# CasAuthBundle
Basic CAS (SSO) authenticator for Symfony 3

This bundle provides a -very- basic CAS (http://jasig.github.io/cas/4.1.x/index.html) authentication client for Symfony 3.

## Installation

Install the library via [Composer](https://getcomposer.org/) by
running the following command:

```bash
composer require prayno/casauth-bundle
```

Next, enable the bundle in your `app/AppKernel.php` file:

```php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new PRayno\CasAuthBundle\PRaynoCasAuthBundle(),
        // ...
    );
}
```

Add your settings to your config.yml :
```yaml
p_rayno_cas_auth:
    server_login_url: https://mycasserver/cas/
    server_validation_url: https://mycasserver/cas/serviceValidate
    server_logout_url: https://mycasserver/cas/logout
    xml_namespace: cas
    options:[] see http://docs.guzzlephp.org/en/latest/request-options.html
```
Note : the xml_namespace and options parameters are optionals

If you want any to access user attributes from the CAS response, inject the session into the user-provider in services.yml :
```yaml
services:
    prayno.cas_user_provider:
        class: PRayno\CasAuthBundle\Security\User\CasUserProvider
        arguments: ['@session']
```

Modify your security.xml with the following values (the provider in the following settings should not be used as it's just a very basic example) :
```yaml
security:
    providers:
        cas:
          id: prayno.cas_user_provider

    firewalls:
        dev:
            pattern: ^/(_(profiler|wdt)|css|images|js)/
            security: false
        main:
            anonymous: ~
            logout: ~
            guard:
                authenticators:
                    - prayno.cas_authenticator

    access_control:
        - { path: ^/, roles: ROLE_USER }
  ```
  
  And voila ! Your secured route should redirect you to your CAS login page which should authenticate you.
