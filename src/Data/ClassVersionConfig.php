<?php

declare(strict_types=1);

namespace JardisCore\ClassVersion\Data;

use InvalidArgumentException;
use JardisPsr\ClassVersion\ClassVersionConfigInterface;

/**
 * ClassVersionConfig is responsible for managing version configurations
 * using an associative array, where keys are version group identifiers and
 * values are arrays of version labels.
 */
class ClassVersionConfig implements ClassVersionConfigInterface
{
    /** @var array<string, array<string|mixed>> */
    private array $version;

    /**
     * @param array<string, array<string|mixed>> $version
     * @throws InvalidArgumentException
     */
    public function __construct(array $version = [])
    {
        $this->version = $this->validate($version);
    }

    public function version(?string $version = null): ?string
    {
        $version = trim($version ?? '', " \t\n\r\0\x0B");

        foreach ($this->version as $versionConfigKey => $versions) {
            if (in_array($version, $versions, true)) {
                return $versionConfigKey;
            }
        }

        return $version;
    }

    /**
     * @param array<string, array<string|mixed>> $versions
     * @return array<string, array<string|mixed>>
     * @throws InvalidArgumentException
     */
    protected function validate(array $versions): array
    {
        if (!empty($versions)) {
            foreach ($versions as $key => $versionList) {
                /** @phpstan-ignore-next-line */
                if (!is_string($key) || !is_array($versionList)) {
                    throw new InvalidArgumentException(
                        'Parameter must be an assoc array (key as string and value as array)'
                    );
                }

                foreach ($versionList as $i => $label) {
                    if (!is_string($label)) {
                        throw new InvalidArgumentException(
                            sprintf('Version labels must be strings (key "%s", index %d)', $key, $i)
                        );
                    }
                    $trimmed = trim($label);
                    if ($trimmed === '') {
                        throw new InvalidArgumentException(
                            sprintf('Version labels must be non-empty (key "%s", index %d)', $key, $i)
                        );
                    }
                    $versionList[$i] = $trimmed;
                }

                $versions[$key] = array_values(array_unique($versionList));
            }
        }

        return $versions;
    }
}
