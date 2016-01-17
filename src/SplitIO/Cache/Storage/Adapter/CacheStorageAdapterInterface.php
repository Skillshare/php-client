<?php
namespace SplitIO\Cache\Storage\Adapter;

interface CacheStorageAdapterInterface
{

    public function __construct(array $options);

    /**
     * @param string $key
     * @return \Psr\Cache\CacheItemInterface
     */
    public function getItem($key);

    /**
     * @param string $key
     * @param mixed $value
     * @param int|null $expiration
     * @return bool
     */
    public function addItem($key, $value, $expiration = null);

    /**
     * @return bool
     */
    public function clear();

    /**
    * @param $key
    * @return bool
    */
    public function deleteItem($key);

    /**
     * @param array $keys
     * @return bool
     */
    public function deleteItems(array $keys);

    /**
     * @param $key
     * @param $value
     * @param int|null $expiration
     * @return bool
     */
    public function save($key, $value, $expiration = null);
}