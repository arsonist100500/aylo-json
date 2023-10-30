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
    public function read(string $id);

    /**
     * @param string $id
     * @return mixed
     */
    public function readStream(string $id);

    /**
     * @param string $id
     * @param string|mixed $data
     * @return bool
     */
    public function write(string $id, $data): bool;

    /**
     * @param string $id
     * @return int
     */
    public function getSize(string $id): int;
}
