<?php

namespace PRayno\CasAuthBundle\Event;

use PRayno\CasAuthBundle\Session\SessionTerminatorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class SingleLogoutEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var GetResponseEvent
     */
    private $event;

    private $sessionTerminator;

    public function __construct(SessionTerminatorInterface $sessionTerminator)
    {
        $this->sessionTerminator = $sessionTerminator;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [
                ['singleLogout', 192],
            ]
        ];
    }

    public function singleLogout(GetResponseEvent $event)
    {
        if ($event->getRequest()->get("logoutRequest", false)) {
            $this->event = $event;
            $this->processLogoutRequest();
        }
    }

    private function processLogoutRequest(): void
    {
        $xmlString = utf8_encode(urldecode($this->event->getRequest()->get("logoutRequest")));
        $xmlData = new \SimpleXMLElement($xmlString);
        if (is_object($xmlData)) {
            $this->processXmlData($xmlData);
        }
    }

    private function processXmlData($xmlData): void
    {
        $sessionIndex = $this->extractSessionIndex($xmlData);
        if ($sessionIndex) {
            $this->terminateSession($sessionIndex);
        }
    }

    private function extractSessionIndex($xmlData): ?string
    {
        $namespaces = $xmlData->getNameSpaces();
        if (isset($namespaces['samlp'])) {
            $element = $xmlData->children($namespaces['samlp'])->SessionIndex;
        } else {
            $element = $xmlData->xpath('SessionIndex');
        }

        return is_object($element) ? trim((string) $element[0]) : null;
    }

    private function terminateSession(string $sessionIndex): void
    {
        $this->sessionTerminator->terminate($sessionIndex);
        $this->event->setResponse(new Response());
    }
}
