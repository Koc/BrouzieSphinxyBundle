<?php

namespace Brouzie\Bundle\SphinxyBundle\Logger;

use Brouzie\Sphinxy\Logging\LoggerInterface as SphinxyLoggerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class SphinxyLogger implements SphinxyLoggerInterface
{
    const MAX_STRING_LENGTH = 32;
    const BINARY_DATA_VALUE = '(binary value)';

    protected $logger;
    protected $stopwatch;

    /**
     * Constructor.
     *
     * @param LoggerInterface $logger    A LoggerInterface instance
     * @param Stopwatch       $stopwatch A Stopwatch instance
     */
    public function __construct(LoggerInterface $logger = null, Stopwatch $stopwatch = null)
    {
        $this->logger = $logger;
        $this->stopwatch = $stopwatch;
    }

    /**
     * {@inheritdoc}
     */
    public function startQuery($sql, array $params = null)
    {
        if (null !== $this->stopwatch) {
            $this->stopwatch->start('sphinxy', 'sphinxy');
        }

        if (is_array($params)) {
            foreach ($params as $index => $param) {
                if (!is_string($params[$index])) {
                    continue;
                }

                // non utf-8 strings break json encoding
                if (!preg_match('#[\p{L}\p{N} ]#u', $params[$index])) {
                    $params[$index] = self::BINARY_DATA_VALUE;
                    continue;
                }

                // detect if the too long string must be shorten
                if (function_exists('mb_detect_encoding') && false !== $encoding = mb_detect_encoding($params[$index])) {
                    if (self::MAX_STRING_LENGTH < mb_strlen($params[$index], $encoding)) {
                        $params[$index] = mb_substr($params[$index], 0, self::MAX_STRING_LENGTH - 6, $encoding).' [...]';
                        continue;
                    }
                } else {
                    if (self::MAX_STRING_LENGTH < strlen($params[$index])) {
                        $params[$index] = substr($params[$index], 0, self::MAX_STRING_LENGTH - 6).' [...]';
                        continue;
                    }
                }
            }
        }

        if (null !== $this->logger) {
            $this->log($sql, null === $params ? array() : $params);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function stopQuery()
    {
        if (null !== $this->stopwatch) {
            $this->stopwatch->stop('sphinxy');
        }
    }

    /**
     * Logs a message.
     *
     * @param string $message A message to log
     * @param array  $params  The context
     */
    protected function log($message, array $params)
    {
        $this->logger->debug($message, $params);
    }
}
