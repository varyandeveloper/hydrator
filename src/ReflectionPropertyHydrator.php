<?php

namespace VarYans\Hydrator;

class ReflectionPropertyHydrator extends ReflectionHydrator implements HydratorInterface
{
    /**
     * @param array $data
     * @param string|object $object
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
            if (!$reflectionClass->hasProperty($key)) {
                continue;
            }

            $property = $reflectionClass->getProperty($key);
            $property->setAccessible(true);
            $property->setValue($object, $value);
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
            if (!empty($map[$property->getName()])) {
                continue;
            }
            $property->setAccessible(true);
            $result[$property->getName()] = $property->getValue($object);
        }

        return $result;
    }
}