<?php
/*
 * CENTREON
 *
 * Source Copyright 2005-2019 Centreon
 *
 * Unauthorized reproduction, copy and distribution
 * are not allowed.
 *
 * For more information : contact@centreon.com
 *
 */
declare(strict_types=1);

namespace CentreonAutoDiscovery\Domain\Mapper\Mapper;

use Centreon\Domain\HostConfiguration\Host;

class AbstractMapper
{
    protected const PREFIX_DISCOVERY_RESULT = 'discovery.results.';
    protected const PREFIX_DISCOVERY_PARAMETER = 'discovery.parameters.';
    protected const PREFIX_DISCOVERY_CREDENTIAL = 'discovery.credentials.';

    protected $validationErrors = [];

    protected $extendedAttributes = [
        'host.notes' => 'extendedHost',
        'host.notes_url' => 'extendedHost',
        'host.notes_actions_url'=> 'extendedHost'
    ];

    protected $availableHostAttributes = [
        'host.name',
        'host.alias',
        'host.ip_address',
        'host.comment',
        'host.geo_coords',
        'host.is_activate',
        'host.notes',
        'host.notes_url',
        'host.notes_actions_url'
    ];

    /**
     * @inheritDoc
     */
    public function addValidationError(string $attribute, string $message): void
    {
        $this->validationErrors[$attribute] = $message;
    }

    /**
     * Update the host according to the attribute name.
     *
     * ex: host.name => host->setName(...) <br/>
     * ex: host.notes => host->getExtendedHost()->setNotes(...)
     *
     * @param Host &$host (passing by Reference)
     * @param string $attribute
     * @param mixed $value
     * @throws MapperException
     */
    public function updateHost(Host &$host, string $attribute, $value): void
    {
        $objectToCall = $host;
        $decomposedAttribute = explode('.', $attribute);
        $entityName = array_shift($decomposedAttribute);
        if( $entityName !== 'host') {
            throw new MapperException('Unauthorized entity (' . $entityName . ')');
        }

        $hostAttribute = array_shift($decomposedAttribute);

        $isExtendedAttribute = array_key_exists($attribute, $this->extendedAttributes);

        try {
            $methodName = $this->convertSnakeCaseToCamelCase($hostAttribute);
            // Check Boolean methods and change isOk to setOk
            if (strlen($methodName) > 2 && substr($methodName, 0, 2) !== 'is') {
                $methodName = 'set' . ucfirst($methodName);
            }

            $reflexion = new \ReflectionClass(Host::class);

            if ($isExtendedAttribute) {
                $extendedHostMethod = $this->extendedAttributes[$attribute];
                // If the sub entity of the host is null, we create it.
                if (call_user_func_array(array($host, 'get' . ucfirst($extendedHostMethod)), []) === null) {
                    $type = (string) $reflexion->getMethod('get' . $extendedHostMethod)->getReturnType();
                    if ($type !== null) {
                        call_user_func_array(array($host, 'set' . ucfirst($extendedHostMethod)), [new $type]);
                    } else {
                        throw new MapperException('Impossible to fill in the property ' . $attribute . ' of host');
                    }
                }
                $objectToCall = call_user_func_array(array($host, 'get' . ucfirst($extendedHostMethod)), []);
                $reflexion = new \ReflectionClass($type);
            }

            if ($reflexion->hasMethod($methodName)) {
                $objectToCall->{$methodName}($value);
            }
        } catch (\Exception $ex) {
            throw new MapperException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    public function convertSnakeCaseToCamelCase(string $snakeCaseAttribute): string
    {
        $camelCaseAttribute = '';
        $nbrLetters = strlen($snakeCaseAttribute);
        for ($index = 0 ; $index < $nbrLetters ; $index++) {
            if ($snakeCaseAttribute[$index] === '_') {
                if ($index < ($nbrLetters + 1) && $snakeCaseAttribute[$index + 1] !== '_') {
                    $camelCaseAttribute .= '_' . strtolower($snakeCaseAttribute[++$index]);
                }
            } else {
                $camelCaseAttribute .= $snakeCaseAttribute[$index];
            }
        }
        return $snakeCaseAttribute;
    }

    /**
     * Retrieve the value based on the key name from host discovery.
     *
     * @param mixed $name Key name to search
     * @param array $discoveredHost Result of the host discovery
     * @return mixed|null Value to extract
     * @throws MapperException
     */
    protected function findValue(string $name, array $discoveredHost)
    {
        // Find a value from the host discovery result
        if (strpos($name, self::PREFIX_DISCOVERY_RESULT) === 0) {
            return $this->findValueFromDiscoveryResult($name, $discoveredHost);
        } elseif (strpos($name, self::PREFIX_DISCOVERY_PARAMETER) === 0) {
            return $this->findValueFromParameters($name, []);
        } elseif (strpos($name, self::PREFIX_DISCOVERY_CREDENTIAL) === 0) {
            return $this->findValueFromCredentials($name, []);
        }
        return null;
    }

    /**
     * @param string $name
     * @param array $discoveredHost
     * @return array|mixed
     * @throws MapperException
     */
    protected function findValueFromDiscoveryResult(string $name, array $discoveredHost)
    {
        $nameWithoutPrefix = substr($name, strlen(self::PREFIX_DISCOVERY_RESULT));
        // We check if the value is contained in the "tags" value
        if (strpos($nameWithoutPrefix, 'tags.') === 0) {
            $nameWithoutPrefix = substr($nameWithoutPrefix, strlen('tags.'));
            $tags = $discoveredHost['tags'];
            $values = [];
            foreach ($tags as $tag) {
                if (!isset($tag[$nameWithoutPrefix])) {
                    throw new MapperException('Attribute \'' . substr($name, strlen(self::PREFIX_DISCOVERY_RESULT)) . '\' in tags does not exist');
                }
                $values[] = $tag[$nameWithoutPrefix];
            }
            return $values;
        } else {
            if (!isset($discoveredHost[$nameWithoutPrefix])) {
                throw new MapperException('Attribute \'' . substr($name, strlen(self::PREFIX_DISCOVERY_RESULT)) . '\' does not exist');
            }
            return $discoveredHost[$nameWithoutPrefix];
        }
    }

    /**
     * @param string $name
     * @param array $parameters
     * @return null
     */
    protected function findValueFromParameters(string $name, array $parameters)
    {
        return null;
    }

    /**
     * @param string $name
     * @param array $parameters
     * @return null
     */
    protected function findValueFromCredentials(string $name, array $parameters)
    {
        return null;
    }

    /**
     * @param string[] $availableHostAttributes List of available host attributes (['host.name', 'host.alias', ...])
     */
    protected function setAvailableHostAttributes(array $availableHostAttributes): void
    {
        $this->availableHostAttributes = $availableHostAttributes;
    }

    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }
}
