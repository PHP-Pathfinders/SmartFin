<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class RequestListener
{
    #[AsEventListener(event: KernelEvents::REQUEST)]
    public function onKernelRequest(RequestEvent $event): void
    {

        //TODO fix trim for all requests
        $request = $event->getRequest();
        // Trim request data
        $request->request->replace($this->trimArray($request->request->all()));
        $request->query->replace($this->trimArray($request->query->all()));
    }
    private function trimArray(array $data): array
    {
        return array_map(function ($item) {
            if (is_string($item)) {
                return trim($item);
            }
            if (is_array($item)) {
                return $this->trimArray($item);
            }
            return $item;
        }, $data);
    }

}
