<?php

namespace PRayno\CasAuthBundle\Session;

use Psr\Log\LoggerInterface;

class FileSystemSessionTerminator implements SessionTerminatorInterface
{
    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function terminate(string $sessionId): void
    {
        $file = session_save_path().'/sess_'.$sessionId;
        if (file_exists($file)) {
            unlink($file);
        }
        $this->logger->info('User session identified by "{session}" was terminated by CAS Single Logout.', ['session' => $sessionId]);
    }
}
