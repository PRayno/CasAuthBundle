<?php

namespace PRayno\CasAuthBundle\Security\User;

interface CasUserCredentialStoreInterface
{

  /**
   * @param array $credentials
   */
  public function storeUserCredentials(array $credentials);

}
