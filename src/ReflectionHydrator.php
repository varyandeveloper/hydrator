<?php

namespace VarYans\Hydrator;

class ReflectionHydrator implements HydratorInterface
{
    protected static $reflected;

    /**
     * @param array $data
     * @param $object
     * @return object
     * @throws HydratorException
     * @throws \ReflectionException
     */
    public static function hydrate(array $data, $object): object
    {
        $reflectionClass = self::getReflectionClass($object);
        if (is_string($object)) {
            if (!class_exists($object)) {
                throw new HydratorException('Class ' . $object . ' not found');
            }
            $object = $reflectionClass->newInstanceWithoutConstructor();
        }

        foreach ($data as $key => $value) {
            $method = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));
            if (!$reflectionClass->hasMethod($method)) {
                if (!$reflectionClass->hasProperty($key)) {
                    continue;
                } else {
                    $property = $reflectionClass->getProperty($key);
                    $property->setAccessible(true);
                    $property->setValue($object, $value);
                }
            } else {
                $method = $reflectionClass->getMethod($method);
                $method->setAccessible(true);
                $method->invokeArgs($object, [$value]);
            }
        }

        return $object;
    }

    /**
     * @param object $object
     * @param array $restrictedFields
     * @return array
     * @throws \ReflectionException
     */
    public static function extract(object $object, array $restrictedFields = []): array
    {
        $result = [];
        $reflectionClass = self::getReflectionClass($object);
        $map = [];

        foreach ($restrictedFields as $field) {
            $map[$field] = true;
        }

        foreach ($reflectionClass->getProperties() as $property) {
            $method = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $property->getName())));

            if (!empty($map[$property->getName()]) || !empty($map[$method])) {
                continue;
            }

            if ($reflectionClass->hasMethod($method)) {
                $method = $reflectionClass->getMethod($method);
                $method->setAccessible(true);
                $result[$property->getName()] = $method->invoke($object);
            } else {
                $property->setAccessible(true);
                $result[$property->getName()] = $property->getValue($object);
            }
        }

        return $result;
    }

    /**
     * @param $object
     * @return \ReflectionClass
     * @throws \ReflectionException
     */
    protected static function getReflectionClass($object): \ReflectionClass
    {
        if (is_object($object)) {
            $object = get_class($object);
        }

        if (empty(self::$reflected[$object])) {
            self::$reflected[$object] = new \ReflectionClass($object);
        }

        return self::$reflected[$object];
    }
}