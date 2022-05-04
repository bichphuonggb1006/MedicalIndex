<?php


namespace Company\Cache;


class Redis implements CacheInterface
{
    protected $redis;
    protected static $config;

    public function __construct() {
        $this->redis = new \Redis();
        $this->redis->connect(
            self::$config["host"],
            self::$config["port"],
            self::$config["timeout"],
            self::$config["reserved"],
            self::$config["retry_interval"],
            self::$config["read_timeout"]
        );

        if ($auth = arrData(self::$config, "auth"))
            $this->redis->auth($auth);

    }

    public function __destruct() {
        $this->close();
    }

    static function setConfig($config) {
        self::$config = $config;
    }

    function ping() {
        return $this->redis->ping();
    }

    function close() {
        $this->redis->close();
    }

    /** Remove specified keys.
     * @param mixed ...$keys
     * @return int Number of keys deleted.
     */
    function del(...$keys) {
        return $this->redis->del(...$keys);
    }

    // string methods

    function set($key, $value, $lifeTime = null) {
        return $this->redis->set($key, $value, $lifeTime);
    }

    function get($key) {
        return $this->redis->get($key);
    }

    /**
     * @param string $key
     * @return int
     */
    function incr(string $key, int $value = 1) {
        return $this->redis->incrBy($key, $value);
    }

    /**
     * @param string $key
     * @return int
     */
    public function decr(string $key, int $value = 1)
    {
        return $this->redis->decrBy($key, $value);
    }

    /**
     * @param $key
     * @param $value
     * @return int
     */
    function incrBy($key, $value) {
        return $this->redis->incrBy($key, $value);
    }

    function setget($key, $value) {
        $this->redis->set($key, $value);
        return $value;
    }

    public function contains($key)
    {
        return boolval($this->redis->exists($key));
    }

    public function delete(...$keys)
    {
        return boolval($this->del($keys));
    }

    // Map methods

    /**
     * @param $key
     * @param $hashKey
     * @param $value
     * @return bool|int     - 1 if value didn't exist and was added successfully,
     *                      - 0 if the value was already present and was replaced, FALSE if there was an error.
     */
    function hSet($key, $hashKey, $value) {
        return $this->redis->hSet($key, $hashKey, $value);
    }

    function hGet($key, $hashKey) {
        return $this->redis->hGet($key, $hashKey);
    }

    /**
     * @param $key
     * @param ...$hashKeys
     * @return bool|int the number of deleted keys, 0 if the key doesn't exist, FALSE if the key isn't a hash.
     */
    function hDel($key, ...$hashKeys) {
        return $this->redis->hDel($key, ...$hashKeys);
    }

    /** Returns the whole hash, as an array of strings indexed by strings.
     * @param string $key
     * @return array An array of elements, the contents of the hash.
     */
    function hGetAll($key) {
        return $this->redis->hGetAll($key);
    }

    /** Returns the values in a hash, as an array of strings.
     * @param string $key
     * @return array An array of elements, the values of the hash. This works like PHP's array_values().
     */
    function hVals($key) {
        return $this->redis->hVals($key);
    }

    /** Verify if the specified member exists in a key.
     * @param $key
     * @param $hashKey
     * @return bool
     */
    function hExists($key, $hashKey) {
        return $this->redis->hExists($key, $hashKey);
    }

    /**
     * @param $key
     * @param $hashKey
     * @param $value (integer) value that will be added to the member's value
     * @return int the new value
     */
    function hIncrBy($key, $hashKey, $value) {
        return $this->redis->hIncrBy($key, $hashKey, $value);
    }

    // list methods

    /**
     * @param $key
     * @param mixed ...$values
     * @return false|int The new length of the list in case of success, FALSE in case of Failure.
     */
    function lPush($key, ...$values) {
        return $this->redis->lPush($key, ...$values);
    }

    // set methods

    /** Adds a value to the set value stored at key. If this value is already in the set, FALSE is returned.
     * @param $key
     * @param mixed ...$values
     * @return bool|int
     */
    function sAdd($key, ...$values) {
        return $this->redis->sAdd($key, ...$values);
    }

    /** Checks if value is a member of the set stored at the key key
     * @param $key
     * @param $value
     * @return bool TRUE if value is a member of the set at key key, FALSE otherwise.
     */
    function sIsMember($key, $value) {
        return $this->redis->sIsMember($key, $value);
    }

    /** Returns the contents of a set.
     * @param $key
     * @return array An array of elements, the contents of the set.
     */
    function sMembers($key) {
        return $this->redis->sMembers($key);
    }

    /** Returns a random element from the set value at Key, without removing it.
     * @param $key
     * @param int $count
     * @return array|bool|mixed|string
     * If no count is provided, a random String value from the set will be returned.
     * If a count is provided, an array of values from the set will be returned
     * FALSE if set identified by key is empty or doesn't exist.
     */
    function sRandMember($key, $count = 1) {
        return $this->redis->sRandMember($key, $count);
    }

    /** Returns the cardinality of the set identified by key.
     * @param $key
     * @return int the cardinality of the set identified by key, 0 if the set doesn't exist.
     */
    function sCard($key) {
        return $this->redis->sCard($key);
    }

    /** Removes the specified member from the set value stored at key.
     * @param $key
     * @param mixed ...$members
     * @return int The number of elements removed from the set.
     */
    function sRem($key, ...$members) {
        return $this->redis->sRem($key, ...$members);
    }

}