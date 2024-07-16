<?php

namespace App\EventListener;

use Cassandra\Exception\ValidationException;
use Doctrine\ORM\Exception\MissingIdentifierField;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Exception\ValidatorException;
use SymfonyCasts\Bundle\ResetPassword\Exception\ExpiredResetPasswordTokenException;
use SymfonyCasts\Bundle\ResetPassword\Exception\InvalidResetPasswordTokenException;
use SymfonyCasts\Bundle\ResetPassword\Exception\TooManyPasswordRequestsException;
use SymfonyCasts\Bundle\VerifyEmail\Exception\InvalidSignatureException;
use Zenstruck\Assert\Not;

final class ExceptionListener
{
    #[AsEventListener(event: KernelEvents::EXCEPTION)]
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
//         In case of duplicate entry
        if ($exception instanceof ConflictHttpException){
            $this->setResponse($event, Response::HTTP_CONFLICT);
        }
        elseif ($exception instanceof AccessDeniedHttpException){
            $this->setResponse($event, Response::HTTP_FORBIDDEN);
        }
//         In case of invalid json
        elseif ($exception instanceof BadRequestHttpException){
            $this->setResponse($event,Response::HTTP_BAD_REQUEST);
        }

//         In case of 404 (Query params validation error also handled here)
        elseif ($exception instanceof NotFoundHttpException) {
            $previousException = $exception->getPrevious();
            // If validation fails for query parameters
            if ($previousException instanceof ValidationFailedException){
                $this->handleValidationErrors($event);
            }else{
                $customMessage = null;
                if($exception->getMessage() === ''){
                    $customMessage='Missing query params';
                }
                $this->setResponse($event, Response::HTTP_NOT_FOUND,$customMessage);
            }
        }
//         In case of validation error
        elseif ($exception instanceof UnprocessableEntityHttpException){
            $this->handleValidationErrors($event);
        }
//        In case of invalid email token
        elseif ($exception instanceof InvalidSignatureException){
            $this->setResponse($event, Response::HTTP_BAD_REQUEST,'Invalid signature');
        }
//        In case of invalid reset password token
        elseif ($exception instanceof InvalidResetPasswordTokenException){
            $this->setResponse($event, Response::HTTP_FORBIDDEN,'Invalid reset password token');
        }
//        In the case of many password reset requests
        elseif ($exception instanceof TooManyPasswordRequestsException){
            $this->setResponse($event, Response::HTTP_TOO_MANY_REQUESTS, 'Too many reset password requests, try again later...');
        }
//        In case of expired reset password token
        elseif ($exception instanceof ExpiredResetPasswordTokenException){
            $this->setResponse($event, Response::HTTP_FORBIDDEN,'Expired reset password token');
        }
//         In case of any error
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
    private function setResponse(ExceptionEvent $event, int $status, string $customMessage= null):void
    {
        $exception = $event->getThrowable();
        $message = $customMessage ?? $exception->getMessage();
        $response = new JsonResponse([
            'success' => false,
            'message' => $message
        ], $status);
        $event->setResponse($response);
    }

    /**
     * Format and handle validation errors for response
     * @param $event
     * @return void
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
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
        $event->setResponse($response);
    }
}
