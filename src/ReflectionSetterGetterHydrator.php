<?php

namespace VarYans\Hydrator;

class ReflectionSetterGetterHydrator extends ReflectionHydrator implements HydratorInterface
{
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
                continue;
            }
            $method = $reflectionClass->getMethod($method);
            $method->setAccessible(true);
            $method->invokeArgs($object, [$value]);
        }

        return $object;
    }

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

            if (!empty($map[$method]) || !empty($map[$property->getName()]) || !$reflectionClass->hasMethod($method)) {
                continue;
            }

            $method = $reflectionClass->getMethod($method);
            $method->setAccessible(true);
            $result[$property->getName()] = $method->invoke($object);
        }

        return $result;
    }
}