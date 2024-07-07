<?php

namespace App\EventListener;

use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Validator\ConstraintViolationList;
use Zenstruck\Assert\Not;

final class ExceptionListener
{
    #[AsEventListener(event: KernelEvents::EXCEPTION)]
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
//        dd($exception);
        // In case of duplicate entry
        if ($exception instanceof ConflictHttpException){
            $this->setResponse($event, Response::HTTP_CONFLICT);
        }
        // In case of invalid json
        elseif ($exception instanceof BadRequestHttpException){
            $this->setResponse($event,Response::HTTP_BAD_REQUEST);
        }
        // In Case of validation error
        elseif ($exception instanceof NotFoundHttpException) {
            $this->handleValidationErrors($event);
        }
        // In case of validation error
        elseif ($exception instanceof UnprocessableEntityHttpException){
            $this->handleValidationErrors($event);
        }
        // In case of RuntimeException
        elseif ($exception instanceof \RuntimeException){
            $this->setResponse($event,Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Prepares an response
     * @param ExceptionEvent $event
     * @param int $status
     * @return void
     */
    private function setResponse(ExceptionEvent $event, int $status):void
    {
        $exception = $event->getThrowable();
        $message = $exception->getMessage();
        $response = new JsonResponse([
            'success' => false,
            'message' => $message
        ], $status);
        $event->setResponse($response);
    }

    /**
     * Format and handle validation errors for response
     * @param ConstraintViolationList $violations
     * @return array
     */
    private function handleValidationErrors($event): void
    {
        $exception = $event->getThrowable();
        $validationException = $exception->getPrevious();
        $violations = $validationException->getViolations();
        $errors = [];
        foreach ($violations as $violation) {
            $propertyPath = $violation->getPropertyPath();
            $message = $violation->getMessage();
            $code = $violation->getCode();
            $errors[] = [
                'field' => $propertyPath,
                'message' => $message,
                'code'=>$code
            ];
        }
        $response = new JsonResponse([
            'success' => false,
            'errors' => $errors,
        ], Response::HTTP_BAD_REQUEST);
        $event->setResponse($response);
    }
}
