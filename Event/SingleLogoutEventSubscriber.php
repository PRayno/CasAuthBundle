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
            KernelEvents::REQUEST => ['singleLogout']
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
        $session = $this->extractSession($xmlData);
        if ($session) {
            $this->terminateSession($session);
        }
    }

    private function extractSession($xmlData): ?string
    {
        $namespaces = $xmlData->getNameSpaces();
        if (isset($namespaces['samlp'])) {
            $element = $xmlData->children($namespaces['samlp'])->SessionIndex;
        } else {
            $element = $xmlData->xpath('SessionIndex');
        }

        return is_object($element) ? trim((string) $element[0]) : null;
    }

    private function terminateSession(string $session): void
    {
        $this->sessionTerminator->terminate($session);
        $this->event->setResponse(new Response());
    }
}
