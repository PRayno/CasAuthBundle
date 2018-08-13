<?php

namespace PRayno\CasAuthBundle\Session;

interface SessionTerminatorInterface
{
    public function terminate(string $sessionId): void;
}
