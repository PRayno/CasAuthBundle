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

    public function terminate(string $sessionId): void
    {
        $file = $this->sessionSavePath.'/sess_'.$sessionId;
        if (file_exists($file)) {
            unlink($file);
        }
    }
}
