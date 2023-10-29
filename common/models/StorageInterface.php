<?php

namespace common\models;

interface StorageInterface
{
    /**
     * @param string $id
     * @return bool
     */
    public function exists(string $id): bool;

    /**
     * @param string $id
     * @return mixed
     */
    public function get(string $id);

    /**
     * @param string $id
     * @param string|mixed $data
     * @return bool
     */
    public function put(string $id, $data): bool;
}
