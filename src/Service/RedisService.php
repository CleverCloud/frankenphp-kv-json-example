<?php

namespace Demo\Service;

use Predis\Client;
use Predis\Connection\ConnectionException;

/**
 * Service to handle Redis operations
 */
class RedisService
{
    private ?Client $redis = null;
    private bool $connected = false;
    private array $connectionParams = [];
    private ?string $lastError = null;

    /**
     * Constructor
     *
     * @param string $host Redis host
     * @param int $port Redis port
     * @param string|null $password Redis password
     * @param bool $tls Use TLS for connection
     */
    public function __construct(string $host = 'materiakv.eu-fr-1.services.clever-cloud.com', int $port = 6378, ?string $password = null, bool $tls = false)
    {
        $this->connectionParams = [
            'scheme' => $tls ? 'tls' : 'tcp',
            'host'   => $host,
            'port'   => $port,
        ];

        if ($password) {
            $this->connectionParams['password'] = $password;
        }

        if ($tls) {
            $this->connectionParams['tls'] = ['verify_peer' => false];
        }

        try {
            $this->redis = new Client($this->connectionParams);
            $this->redis->ping();
            $this->connected = true;
        } catch (ConnectionException $e) {
            $this->lastError = 'Redis connection error: ' . $e->getMessage();
            error_log($this->lastError);
        } catch (\Exception $e) {
            $this->lastError = 'Redis error: ' . $e->getMessage();
            error_log($this->lastError);
        }
    }

    /**
     * Get Redis client
     *
     * @return Client|null
     */
    public function getClient(): ?Client
    {
        return $this->redis;
    }

    /**
     * Check if Redis is connected
     *
     * @return bool
     */
    public function isConnected(): bool
    {
        return $this->connected;
    }

    /**
     * Get last error message
     *
     * @return string|null
     */
    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    /**
     * Set a string value
     *
     * @param string $key Key
     * @param string $value Value
     * @return bool
     */
    public function set(string $key, string $value): bool
    {
        if (!$this->connected || !$this->redis) {
            return false;
        }

        try {
            return $this->redis->set($key, $value) === 'OK';
        } catch (\Exception $e) {
            $this->lastError = 'Redis set error: ' . $e->getMessage();
            error_log($this->lastError);
            return false;
        }
    }

    /**
     * Get a string value
     *
     * @param string $key Key
     * @return string|null
     */
    public function get(string $key): ?string
    {
        if (!$this->connected || !$this->redis) {
            return null;
        }

        try {
            $value = $this->redis->get($key);
            return $value !== null ? (string) $value : null;
        } catch (\Exception $e) {
            $this->lastError = 'Redis get error: ' . $e->getMessage();
            error_log($this->lastError);
            return null;
        }
    }

    /**
     * Delete a key
     *
     * @param string $key Key
     * @return bool
     */
    public function del(string $key): bool
    {
        if (!$this->connected || !$this->redis) {
            return false;
        }

        try {
            return $this->redis->del($key) > 0;
        } catch (\Exception $e) {
            $this->lastError = 'Redis del error: ' . $e->getMessage();
            error_log($this->lastError);
            return false;
        }
    }

    /**
     * Delete a JSON path using RedisJSON
     *
     * @param string $key Key
     * @param string $path JSON path
     * @return bool
     */
    public function jsonDel(string $key, string $path = '$'): bool
    {
        if (!$this->connected || !$this->redis) {
            return false;
        }

        try {
            $result = $this->redis->executeRaw(['JSON.DEL', $key, $path]);
            return $result > 0;
        } catch (\Exception $e) {
            $this->lastError = 'Redis JSON.DEL error: ' . $e->getMessage();
            error_log($this->lastError);
            return false;
        }
    }

    /**
     * Set a JSON value using RedisJSON
     *
     * @param string $key Key
     * @param string $path JSON path (default: root '$')
     * @param mixed $value Value
     * @return bool
     */
    public function jsonSet(string $key, $value, string $path = '$'): bool
    {
        if (!$this->connected || !$this->redis) {
            return false;
        }

        try {
            $jsonValue = is_string($value) ? $value : json_encode($value);
            $response = $this->redis->executeRaw(['JSON.SET', $key, $path, $jsonValue]);
            return $response === 'OK';
        } catch (\Exception $e) {
            $this->lastError = 'Redis JSON.SET error: ' . $e->getMessage();
            error_log($this->lastError);
            return false;
        }
    }

    /**
     * Get a JSON value using RedisJSON
     *
     * @param string $key Key
     * @param string|array $paths JSON path(s) (default: root '$')
     * @return mixed
     */
    public function jsonGet(string $key, $paths = '$')
    {
        if (!$this->connected || !$this->redis) {
            return null;
        }

        try {
            $args = ['JSON.GET', $key];

            if (is_array($paths)) {
                foreach ($paths as $path) {
                    $args[] = $path;
                }
            } else {
                $args[] = $paths;
            }

            $result = $this->redis->executeRaw($args);

            if ($result === null) {
                return null;
            }

            $decoded = json_decode((string)$result, true);

            if ($paths === '$' && is_array($decoded) && isset($decoded[0])) {
                return $decoded[0];
            }

            return $decoded;
        } catch (\Exception $e) {
            $this->lastError = 'Redis JSON.GET error: ' . $e->getMessage();
            error_log($this->lastError);
            return null;
        }
    }

    /**
     * Check if a key exists
     *
     * @param string $key Key
     * @return bool
     */
    public function exists(string $key): bool
    {
        if (!$this->connected || !$this->redis) {
            return false;
        }

        try {
            return (bool) $this->redis->exists($key);
        } catch (\Exception $e) {
            $this->lastError = 'Redis exists error: ' . $e->getMessage();
            error_log($this->lastError);
            return false;
        }
    }

    /**
     * Get all keys matching a pattern
     *
     * @param string $pattern Pattern
     * @return array
     */
    public function keys(string $pattern): array
    {
        if (!$this->connected || !$this->redis) {
            return [];
        }

        try {
            return $this->redis->keys($pattern);
        } catch (\Exception $e) {
            $this->lastError = 'Redis keys error: ' . $e->getMessage();
            error_log($this->lastError);
            return [];
        }
    }

    /**
     * Execute a raw Redis command
     *
     * @param array $command Command arguments
     * @return mixed
     */
    public function executeRaw(array $command)
    {
        if (!$this->connected || !$this->redis) {
            return null;
        }

        try {
            return $this->redis->executeRaw($command);
        } catch (\Exception $e) {
            $this->lastError = 'Redis executeRaw error: ' . $e->getMessage();
            error_log($this->lastError);
            return null;
        }
    }
}
