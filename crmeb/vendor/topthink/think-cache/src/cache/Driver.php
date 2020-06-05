<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2019 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace think\cache;

use DateInterval;
use DateTime;
use DateTimeInterface;
use Opis\Closure\SerializableClosure;
use Psr\SimpleCache\CacheInterface;
use think\cache\exception\InvalidArgumentException;
use think\Container;

/**
 * 缓存基础类
 */
abstract class Driver implements CacheInterface
{
    /**
     * 驱动句柄
     * @var object
     */
    protected $handler = null;

    /**
     * 缓存读取次数
     * @var integer
     */
    protected $readTimes = 0;

    /**
     * 缓存写入次数
     * @var integer
     */
    protected $writeTimes = 0;

    /**
     * 缓存参数
     * @var array
     */
    protected $options = [];

    /**
     * 缓存标签
     * @var array
     */
    protected $tag = [];

    /**
     * 获取有效期
     * @access protected
     * @param  integer|DateTimeInterface|DateInterval $expire 有效期
     * @return int
     */
    protected function getExpireTime($expire): int
    {
        if ($expire instanceof DateTimeInterface) {
            $expire = $expire->getTimestamp() - time();
        } elseif ($expire instanceof DateInterval) {
            $expire = DateTime::createFromFormat('U', (string) time())
                ->add($expire)
                ->format('U') - time();
        }

        return (int) $expire;
    }

    /**
     * 获取实际的缓存标识
     * @access public
     * @param  string $name 缓存名
     * @return string
     */
    public function getCacheKey(string $name): string
    {
        return $this->options['prefix'] . $name;
    }

    /**
     * 读取缓存并删除
     * @access public
     * @param  string $name 缓存变量名
     * @return mixed
     */
    public function pull(string $name)
    {
        $result = $this->get($name, false);

        if ($result) {
            $this->delete($name);
            return $result;
        }
    }

    /**
     * 追加（数组）缓存
     * @access public
     * @param  string $name 缓存变量名
     * @param  mixed  $value  存储数据
     * @return void
     */
    public function push(string $name, $value): void
    {
        $item = $this->get($name, []);

        if (!is_array($item)) {
            throw new InvalidArgumentException('only array cache can be push');
        }

        $item[] = $value;

        if (count($item) > 1000) {
            array_shift($item);
        }

        $item = array_unique($item);

        $this->set($name, $item);
    }

    /**
     * 如果不存在则写入缓存
     * @access public
     * @param  string $name 缓存变量名
     * @param  mixed  $value  存储数据
     * @param  int    $expire  有效时间 0为永久
     * @return mixed
     */
    public function remember(string $name, $value, $expire = null)
    {
        if ($this->has($name)) {
            return $this->get($name);
        }

        $time = time();

        while ($time + 5 > time() && $this->has($name . '_lock')) {
            // 存在锁定则等待
            usleep(200000);
        }

        try {
            // 锁定
            $this->set($name . '_lock', true);

            if ($value instanceof \Closure) {
                // 获取缓存数据
                $value = Container::getInstance()->invokeFunction($value);
            }

            // 缓存数据
            $this->set($name, $value, $expire);

            // 解锁
            $this->delete($name . '_lock');
        } catch (\Exception | \throwable $e) {
            $this->delete($name . '_lock');
            throw $e;
        }

        return $value;
    }

    /**
     * 缓存标签
     * @access public
     * @param  string|array $name 标签名
     * @return $this
     */
    public function tag($name)
    {
        $name = (array) $name;
        $key  = implode('-', $name);

        if (!isset($this->tag[$key])) {
            $name = array_map(function ($val) {
                return $this->getTagKey($val);
            }, $name);
            $this->tag[$key] = new TagSet($name, $this);
        }

        return $this->tag[$key];
    }

    /**
     * 获取标签包含的缓存标识
     * @access public
     * @param  string $tag 标签标识
     * @return array
     */
    public function getTagItems(string $tag): array
    {
        $name = $this->getTagKey($tag);
        return $this->get($name, []);
    }

    /**
     * 获取实际标签名
     * @access public
     * @param  string $tag 标签名
     * @return string
     */
    public function getTagKey(string $tag): string
    {
        return $this->options['tag_prefix'] . md5($tag);
    }

    /**
     * 序列化数据
     * @access protected
     * @param  mixed $data 缓存数据
     * @return string
     */
    protected function serialize($data): string
    {
        $serialize = $this->options['serialize'][0] ?? function ($data) {
            SerializableClosure::enterContext();
            SerializableClosure::wrapClosures($data);
            $data = \serialize($data);
            SerializableClosure::exitContext();
            return $data;
        };

        return $serialize($data);
    }

    /**
     * 反序列化数据
     * @access protected
     * @param  string $data 缓存数据
     * @return mixed
     */
    protected function unserialize(string $data)
    {
        $unserialize = $this->options['serialize'][1] ?? function ($data) {
            SerializableClosure::enterContext();
            $data = \unserialize($data);
            SerializableClosure::unwrapClosures($data);
            SerializableClosure::exitContext();
            return $data;
        };

        return $unserialize($data);
    }

    /**
     * 返回句柄对象，可执行其它高级方法
     *
     * @access public
     * @return object
     */
    public function handler()
    {
        return $this->handler;
    }

    /**
     * 返回缓存读取次数
     * @access public
     * @return int
     */
    public function getReadTimes(): int
    {
        return $this->readTimes;
    }

    /**
     * 返回缓存写入次数
     * @access public
     * @return int
     */
    public function getWriteTimes(): int
    {
        return $this->writeTimes;
    }

    /**
     * 读取缓存
     * @access public
     * @param  iterable $keys 缓存变量名
     * @param  mixed    $default 默认值
     * @return iterable
     * @throws InvalidArgumentException
     */
    public function getMultiple($keys, $default = null): iterable
    {
        $result = [];

        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }

        return $result;
    }

    /**
     * 写入缓存
     * @access public
     * @param  iterable               $values 缓存数据
     * @param  null|int|\DateInterval $ttl    有效时间 0为永久
     * @return bool
     */
    public function setMultiple($values, $ttl = null): bool
    {
        foreach ($values as $key => $val) {
            $result = $this->set($key, $val, $ttl);

            if (false === $result) {
                return false;
            }
        }

        return true;
    }

    /**
     * 删除缓存
     * @access public
     * @param iterable $keys 缓存变量名
     * @return bool
     * @throws InvalidArgumentException
     */
    public function deleteMultiple($keys): bool
    {
        foreach ($keys as $key) {
            $result = $this->delete($key);

            if (false === $result) {
                return false;
            }
        }

        return true;
    }

    public function __call($method, $args)
    {
        return call_user_func_array([$this->handler, $method], $args);
    }
}
