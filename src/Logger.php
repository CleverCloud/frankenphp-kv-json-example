<?php

namespace Demo;

use Monolog\Level;
use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;

class Logger
{
    private MonologLogger $logger;

    public function __construct()
    {
        $this->logger = new MonologLogger('demo');
        $this->logger->pushHandler(new StreamHandler('php://stdout', Level::Debug));
    }

    public function log(string $message): void
    {
        $this->logger->info($message);
    }
}
