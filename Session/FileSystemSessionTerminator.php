<?php

namespace PRayno\CasAuthBundle\Session;

use Psr\Log\LoggerInterface;

class FileSystemSessionTerminator implements SessionTerminatorInterface
{
    protected $sessionSavePath;

    public function __construct(string $sessionSavePath)
    {
        $this->sessionSavePath = $sessionSavePath;
    }

    public function terminate(string $sessionIndex): void
    {
        // @TODO Identify the session file to delete.
        $file = '';
        if (file_exists($file)) {
            unlink($file);
        }
    }
}
