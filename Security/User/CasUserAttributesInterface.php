<?php

namespace PRayno\CasAuthBundle\Security\User;

interface CasUserAttributesInterface
{

    /**
     * @return array
     */
    public function getAttributes();

    /**
     * @return mixed string, array, or null if not found.
     */
    public function getAttribute($name);

}
