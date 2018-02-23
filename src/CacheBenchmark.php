<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use Symfony\Component\Cache\Simple\RedisCache as SymfonyRedisCache;

/**
 * @BeforeMethods({"init", "fake"})
 * @Revs(20000)
 */
class CacheBenchmark
{
    private $libraries;
    private $fakeUuid;
    private $fakeText;

    public function init()
    {
        $dotenv = new Dotenv\Dotenv(dirname(__DIR__));
        $dotenv->load();

        $host = getenv('REDIS_HOST');
        $port = getenv('REDIS_PORT');

        $this->libraries = array(
            'PhpRedis' => new Redis(),
            'Predis' => new Predis\Client(array(
                'host' => $host,
                'port' => $port,
            )),
            // 'Stash' => new Stash\Driver\Redis(),
            'phpFastCache' => phpFastCache\CacheManager::getInstance('redis', array(
                'host' => $host,
                'port' => $port,
            )),
            'symfonyCache' => new SymfonyRedisCache(
                SymfonyRedisCache::createConnection(sprintf('redis://%s:%d', $host, $port))
            ),
        );

        $this->libraries['PhpRedis']->connect($host, $port);
        // $this->libraries['Stash']->setOptions(array('servers' => array(
        //     $host, $port
        // )));
    }

    public function fake()
    {
        $faker = Faker\Factory::create();

        $this->fakeUuid = $faker->uuid;
        $this->fakeText = $faker->text;
    }

    public function benchPhpRedisSet()
    {
        $redis = $this->libraries['PhpRedis'];
        $redis->set($this->fakeUuid, $this->fakeText);
    }

    public function benchPhpRedisGet()
    {
        $redis = $this->libraries['PhpRedis'];
        $redis->get($this->fakeUuid);
    }

    public function benchPredisSet()
    {
        $redis = $this->libraries['Predis'];
        $redis->set($this->fakeUuid, $this->fakeText);
    }

    public function benchPredisGet()
    {
        $redis = $this->libraries['Predis'];
        $redis->get($this->fakeUuid);
    }

    // public function benchStashSet()
    // {
    //     $redis = $this->libraries['Stash'];
    //     $redis->set($this->fakeUuid, $this->fakeText);
    // }

    // public function benchStashGet()
    // {
    //     $redis = $this->libraries['Stash'];
    //     $redis->get($this->fakeUuid);
    // }

    public function benchPhpFastCacheSet()
    {
        $redis = $this->libraries['phpFastCache'];
        $item = $redis->getItem($this->fakeUuid);
        $item->set($this->fakeText);
        $redis->save($item);
    }

    public function benchPhpFastCacheGet()
    {
        $redis = $this->libraries['phpFastCache'];
        $item = $redis->getItem($this->fakeUuid);
        $item->get();
    }

    public function benchSymfonyRedisCacheSet()
    {
        $redis = $this->libraries['symfonyCache'];
        $redis->set($this->fakeUuid, $this->fakeText);
    }

    public function benchSymfonyRedisCacheGet()
    {
        $redis = $this->libraries['symfonyCache'];
        $redis->get($this->fakeUuid);
    }
}
