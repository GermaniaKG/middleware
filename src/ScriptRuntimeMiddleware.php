<?php

namespace Germania\Middleware;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;

class ScriptRuntimeMiddleware implements MiddlewareInterface, LoggerAwareInterface
{
    use LoggerAwareTrait,
        LogLevelTrait;


    /**
     * @var float
     */
    public $start_time;

    /**
     * @param LoggerInterface $logger
     * @param float           $start_time Script start time as float, defaults to "now"
     */
    public function __construct(LoggerInterface $logger, $start_time = null)
    {
        $this->setLogger($logger);
        $this->start_time = $start_time ?: microtime('float');
    }



    /**
     * PSR-15 Single Pass
     *
     * @param  ServerRequestInterface  $request Server reuest instance
     * @param  RequestHandlerInterface $handler Request handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $response = $handler->handle($request);
        $this->logRuntime();
        return $response;
    }



    /**
     * PSR-7 Double Pass
     *
     * @param RequestInterface   $request
     * @param ResponseInterface  $response
     * @param callable           $next
     *
     * @return ResponseInterface
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next)
    {
        $response = $next($request, $response);
        $this->logRuntime();
        return $response;
    }


    protected function logRuntime()
    {
        $this->logger->log($this->loglevel, 'Script runtime: ', [
            'seconds' => (microtime('float') - $this->start_time)
        ]);
    }
}
