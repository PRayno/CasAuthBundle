<?php

namespace PRayno\CasAuthBundle\Session;

use Psr\Log\LoggerInterface;

class FileSystemSessionTerminator implements SessionTerminatorInterface
{
    public function terminate(string $sessionId): void
    {
        $file = session_save_path().'/sess_'.$sessionId;
        if (file_exists($file)) {
            unlink($file);
        }
    }
}
