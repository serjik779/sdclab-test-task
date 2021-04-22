<?php

namespace App\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class ExceptionListener
 * @package App\EventListener
 */
class ExceptionListener
{
    /**
     * @var array 
     */
    private static $exceptionCodesMapping = [
        AccessDeniedHttpException::class => Response::HTTP_FORBIDDEN,
        AccessDeniedException::class => Response::HTTP_FORBIDDEN,
        NotFoundHttpException::class => Response::HTTP_NOT_FOUND,
    ];

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var bool
     */
    private $debug;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * ExceptionListener constructor.
     * @param LoggerInterface $logger
     * @param EventDispatcherInterface $dispatcher
     * @param bool $debug
     */
    public function __construct(
        LoggerInterface $logger,
        EventDispatcherInterface $dispatcher,
        bool $debug = false
    ) {
        $this->logger = $logger;
        $this->debug = $debug;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param ExceptionEvent $event
     */
    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        $isVisibleException = array_key_exists(get_class($exception), self::$exceptionCodesMapping);

        if (!$isVisibleException) {
            $this->logExceptionError($exception);
        }

        $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;

        if ($this->debug || $isVisibleException) {
            if (method_exists($exception, 'getStatusCode')) {
                $statusCode = $exception->getStatusCode();
            }
            $statusCode = self::$exceptionCodesMapping[get_class($exception)] ?? $statusCode;
        }
        $message = 'Something Went Wrong';

        $responseArr = [
            'status' => $statusCode,
            'timestamp' => (new \DateTime())->format(DATE_ATOM),
            'message' => $message,
        ];

        if (
            $isVisibleException
            && method_exists($exception, 'getKey')
            && $exception->getKey() !== null
        ) {
            $responseArr['key'] = $exception->getKey();
        }

        if ($this->debug) {
            $responseArr['trace'] = $exception->getTraceAsString();
        }

        $event->setResponse(
            new JsonResponse($responseArr, $statusCode)
        );
    }

    /**
     * @param \Throwable $exception
     */
    private function logExceptionError(\Throwable $exception)
    {
        if ($this->logger) {
            $this->logger->error(get_class($exception) . ': ' . $exception->getMessage());
        }
    }
}
