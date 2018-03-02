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

        $phpRedis = new Redis();
        $phpRedis->connect($host, $port);

        $this->libraries = array(
            'PhpRedis' => $phpRedis,
            'Predis' => new Predis\Client(array(
                'host' => $host,
                'port' => $port,
            )),
        );

        // Symfony Cache Component
        $this->libraries['symfonyCache'] = new SymfonyRedisCache(
            SymfonyRedisCache::createConnection(sprintf('redis://%s:%d', $host, $port))
        );

        // Scrapbook
        $this->libraries['Scrapbook'] = new \MatthiasMullie\Scrapbook\Adapters\Redis($phpRedis);

        // CakePHP Caching Library
        $this->libraries['CakePHP'] = Cake\Cache\Cache::config('default', [
            'className' => 'Redis',
            'host' => $host,
            'port' => $port,
        ]);

        // Doctrine Cache
        $this->libraries['Doctrine'] = new \Doctrine\Common\Cache\RedisCache();
        $this->libraries['Doctrine']->setRedis($phpRedis);
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

    public function benchSymfonyCacheSet()
    {
        $redis = $this->libraries['symfonyCache'];
        $redis->set($this->fakeUuid, $this->fakeText);
    }

    public function benchSymfonyCacheGet()
    {
        $redis = $this->libraries['symfonyCache'];
        $redis->get($this->fakeUuid);
    }

    public function benchScrapbookSet()
    {
        $redis = $this->libraries['Scrapbook'];
        $redis->set($this->fakeUuid, $this->fakeText);
    }

    public function benchScrapbookGet()
    {
        $redis = $this->libraries['Scrapbook'];
        $redis->get($this->fakeUuid);
    }

    public function benchCakePHPSet()
    {
        \Cake\Cache\Cache::write($this->fakeUuid, $this->fakeText);
    }

    public function benchCakePHPGet()
    {
        \Cake\Cache\Cache::read($this->fakeUuid);
    }

    public function benchDoctrineSet()
    {
        $redis = $this->libraries['Doctrine'];
        $redis->save($this->fakeUuid, $this->fakeText);
    }

    public function benchDoctrineGet()
    {
        $redis = $this->libraries['Doctrine'];
        $redis->fetch($this->fakeUuid);
    }
}
