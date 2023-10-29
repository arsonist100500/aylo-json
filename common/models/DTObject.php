<?php

namespace common\models;

use JsonSerializable;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use Yii;
use yii\base\Arrayable;
use yii\base\ArrayableTrait;
use yii\base\BaseObject;

class DTObject extends BaseObject implements Arrayable, JsonSerializable
{
    use ArrayableTrait;

    /**
     * @return array
     */
    public function fields()
    {
        $fields = [];
        $class = new ReflectionClass($this);
        foreach ($class->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            if ($property->isStatic() === false) {
                $fields[] = $property->getName();
            }
        }
        foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->isStatic()) {
                continue;
            }
            $name = $method->getName();
            if (str_starts_with($name, 'get')) {
                $fields[] = strtolower($name[3]) . substr($name, 4);
            }
        }
        return $fields;
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public function __get($name)
    {
        $getter = 'get' . $name;
        if (method_exists($this, $getter)) {
            return $this->$getter();
        }
        return null;
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        $setter = 'set' . $name;
        if (method_exists($this, $setter)) {
            $this->$setter($value);
        } else {
            $value_type = gettype($value);
            Yii::warning("DTO missing field: {$name}\nValueType: {$value_type}", static::class);
        }
    }

    /**
     * @return string
     * @throws \JsonException
     */
    public function __toString(): string
    {
        return json_encode($this, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * @param array|\yii\base\Arrayable $data
     */
    public function load($data): void
    {
        if ($data instanceof Arrayable) {
            $data = $data->toArray();
        }
        if (is_array($data)) {
            Yii::configure($this, $data);
        }
    }
}
