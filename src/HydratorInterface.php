<?php

namespace VarYans\Hydrator;

interface HydratorInterface
{
    public static function hydrate(array $data, $object): object;

    public static function extract(object $object, array $restrictedFields = []): array;
}