<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class RequestListener
{
    /**
     * On each request all inputs from query params and json payload are trimmed
     * @param RequestEvent $event
     * @return void
     */
    #[AsEventListener(event: KernelEvents::REQUEST)]
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
//         Trim query parameters
        $request->query->replace($this->trimArray($request->query->all()));

//         Trim JSON payload
        $content = $request->getContent();
        if ($content) {
            $jsonArr = json_decode($content, true);
            if (is_array($jsonArr)) {
                $trimmedJsonArr = $this->trimArray($jsonArr);
//                Reinitialize request object with trimmed json and query params
                $request->initialize(
                    $request->query->all(),
                    $request->request->all(),
                    $request->attributes->all(),
                    $request->cookies->all(),
                    $request->files->all(),
                    $request->server->all(),
                    json_encode($trimmedJsonArr)
                );
            }
        }
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
