<?php

namespace src\Integration;

interface DataProviderInterface
{
    public function get(array $request);
}

class DataProvider implements DataProviderInterface
{
    private $host;
    private $user;
    private $password;

    /**
     * @param $host
     * @param $user
     * @param $password
     */
    public function __construct($host, $user, $password)
    {
        $this->host = $host;
        $this->user = $user;
        $this->password = $password;
    }

    /**
     * @param array $request
     *
     * @return array
     */
    public function get(array $request)
    {
        // returns a response from external service
    }
}

namespace src\Decorator;

use DateTime;
use Exception;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use src\Integration\DataProvider;
use src\Integration\DataProviderInterface;

class DataProviderLoggerDecorator implements DataProviderInterface
{
    private $logger;
    private $dataProvider;

    /**
     * @param DataProviderInterface $dataProvider
     * @param LoggerInterface $logger
     */
    public function __construct(DataProviderInterface $dataProvider, LoggerInterface $logger)
    {
        $this->dataProvider = $dataProvider;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function get(array $request)
    {
        try {
            $result = parent::get($request);
            return $result;
        } catch (Exception $e) {
            $this->logger->critical('Error');
        }

        return [];
    }
}

class DataProviderCacheDecorator implements DataProviderInterface
{
    private $cache;
    private $dataProvider;

    /**
     * @param DataProviderInterface $dataProvider
     * @param CacheItemPoolInterface $cache
     */
    public function __construct(DataProviderInterface $dataProvider, CacheItemPoolInterface $cache)
    {
        $this->cache = $cache;
        $this->dataProvider = $dataProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function get(array $request)
    {
        $cacheKey = $this->getCacheKey($request);
        $cacheItem = $this->cache->getItem($cacheKey);
        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $result = parent::get($request);

        $cacheItem
            ->set($result)
            ->expiresAt(
                (new DateTime())->modify('+1 day')
            );

        return $result;


    }

    public function getCacheKey(array $request)
    {
        return json_encode($request);
    }
}

$dataProvider = new DataProvider($host, $user, $password);
$dataProvider = new DataProviderCacheDecorator($dataProvider);
$dataProvider = new DataProviderLoggerDecorator($dataProvider);
