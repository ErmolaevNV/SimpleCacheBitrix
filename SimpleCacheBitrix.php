<?php

namespace ErmolaevNV;

use Bitrix\Main\Data\Cache;
use MatthiasMullie\Scrapbook\Psr16\InvalidArgumentException;
use Psr\SimpleCache\CacheInterface;

class SimpleCacheBitrix implements CacheInterface
{
    const KEY_INVALID_CHARACTERS = '{}()/\@:';

    protected $ttl;
    protected $baseDir;

    protected $initDir;

    protected $valNamePrefix = "val";

    public function __construct($ttl = PHP_INT_MAX, $initDir = '', $basedir = "cache")
    {
        $this->ttl = $ttl;
        $this->baseDir = $basedir;

        $this->initDir = $initDir;
    }

    /**
     * Fetches a value from the cache.
     *
     * @param string $key The unique key of this item in the cache.
     * @param mixed $default Default value to return if the key does not exist.
     *
     * @return mixed The value of the item from the cache, or $default in case of cache miss.
     *
     *   MUST be thrown if the $key string is not a legal value.
     */
    public function get($key, $default = null)
    {
        $this->assertValidKey($key);
        $obCache = Cache::createInstance();
        if ($obCache->InitCache($this->ttl, $key, $this->initDir, $this->baseDir)) {
            $vars = $obCache->GetVars();
            return $vars[$this->valNamePrefix];
        }
        return $default;
    }

    /**
     * Persists data in the cache, uniquely referenced by a key with an optional expiration TTL time.
     *
     * @param string $key The key of the item to store.
     * @param mixed $value The value of the item to store, must be serializable.
     * @param null|int|\DateInterval $ttl Optional. The TTL value of this item. If no value is sent and
     *                                      the driver supports TTL then the library may set a default value
     *                                      for it or let the driver take care of that.
     *
     * @return bool True on success and false on failure.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if the $key string is not a legal value.
     */
    public function set($key, $value, $ttl = null)
    {
        $this->assertValidKey($key);
        $obCache = Cache::createInstance();
        if ($ttl === null) {
            $ttl = $this->ttl;
        }
        $obCache->InitCache($ttl, $key, $this->initDir, $this->baseDir);
        $obCache->StartDataCache();
        $obCache->EndDataCache([$this->valNamePrefix => $value]);
        return true;
    }

    /**
     * Delete an item from the cache by its unique key.
     *
     * @param string $key The unique cache key of the item to delete.
     *
     * @return bool True if the item was successfully removed. False if there was an error.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if the $key string is not a legal value.
     */
    public function delete($key)
    {
        $this->assertValidKey($key);
        $obCache = Cache::createInstance();
        return $obCache->clean($key, $this->initDir, $this->baseDir);
    }

    /**
     * Wipes clean the entire cache's keys.
     *
     * @return bool True on success and false on failure.
     */
    public function clear()
    {
        $obCache = Cache::createInstance();
        $obCache->cleanDir($this->initDir, $this->baseDir);
    }

    /**
     * Obtains multiple cache items by their unique keys.
     *
     * @param iterable $keys A list of keys that can obtained in a single operation.
     * @param mixed $default Default value to return for keys that do not exist.
     *
     * @return iterable A list of key => value pairs. Cache keys that do not exist or are stale will have $default as value.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if $keys is neither an array nor a Traversable,
     *   or if any of the $keys are not a legal value.
     */
    public function getMultiple($keys, $default = null)
    {
        // TODO: Implement getMultiple() method.
    }

    /**
     * Persists a set of key => value pairs in the cache, with an optional TTL.
     *
     * @param iterable $values A list of key => value pairs for a multiple-set operation.
     * @param null|int|\DateInterval $ttl Optional. The TTL value of this item. If no value is sent and
     *                                       the driver supports TTL then the library may set a default value
     *                                       for it or let the driver take care of that.
     *
     * @return bool True on success and false on failure.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if $values is neither an array nor a Traversable,
     *   or if any of the $values are not a legal value.
     */
    public function setMultiple($values, $ttl = null)
    {
        // TODO: Implement setMultiple() method.
    }

    /**
     * Deletes multiple cache items in a single operation.
     *
     * @param iterable $keys A list of string-based keys to be deleted.
     *
     * @return bool True if the items were successfully removed. False if there was an error.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if $keys is neither an array nor a Traversable,
     *   or if any of the $keys are not a legal value.
     */
    public function deleteMultiple($keys)
    {
        // TODO: Implement deleteMultiple() method.
    }

    /**
     * Determines whether an item is present in the cache.
     *
     * NOTE: It is recommended that has() is only to be used for cache warming type purposes
     * and not to be used within your live applications operations for get/set, as this method
     * is subject to a race condition where your has() will return true and immediately after,
     * another script can remove it making the state of your app out of date.
     *
     * @param string $key The cache item key.
     *
     * @return bool
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if the $key string is not a legal value.
     */
    public function has($key)
    {
        // TODO: Implement has() method.
    }

    /**
     * Throws an exception if $key is invalid.
     *
     * @param string $key
     *
     * @throws InvalidArgumentException
     */
    protected function assertValidKey($key)
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException(
                'Invalid key: '.var_export($key, true).'. Key should be a string.'
            );
        }

        if ($key === '') {
            throw new InvalidArgumentException(
                'Invalid key. Key should not be empty.'
            );
        }

        // valid key according to PSR-16 rules
        $invalid = preg_quote(static::KEY_INVALID_CHARACTERS, '/');
        if (preg_match('/['.$invalid.']/', $key)) {
            throw new InvalidArgumentException(
                'Invalid key: '.$key.'. Contains (a) character(s) reserved '.
                'for future extension: '.static::KEY_INVALID_CHARACTERS
            );
        }
    }
}