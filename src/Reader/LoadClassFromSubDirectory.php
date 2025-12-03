<?php

declare(strict_types=1);

namespace JardisCore\ClassVersion\Reader;

use InvalidArgumentException;
use JardisPsr\ClassVersion\ClassVersionInterface;

/**
 * Returns the classVersion of a given class from the subdirectory of the given class
 */
class LoadClassFromSubDirectory implements ClassVersionInterface
{
    /**
     * @template T
     * @param class-string<T> $className
     * @param ?string $version
     * @return mixed|T
     * @throws InvalidArgumentException
     */
    public function __invoke(string $className, ?string $version = null): mixed
    {
        $class = $className;
        $version = trim($version ?? '', " \t\n\r\0\x0B");

        if (!empty($version)) {
            $pos = str_contains($className, '\\') ? strrpos($className, '\\') + 1 : 0;
            $class = substr($className, 0, $pos) . $version . '\\' . substr($className, $pos);
        }

        if ($version !== '' && class_exists($class)) {
            return $class;
        }

        if (class_exists($className)) {
            return $className;
        }

        throw new InvalidArgumentException(sprintf(
            'Given class "%s" not found (also tried versioned "%s")',
            $className,
            $class
        ));
    }
}
