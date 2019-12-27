<?php

declare(strict_types=1);

namespace DevTools;

use MetaDataTool\ValueObjects\Endpoint;
use MetaDataTool\ValueObjects\Property;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TwigExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('namespace', function (Endpoint $endpoint): string {
                return $this->namespace($endpoint);
            }),
            new TwigFilter('className', function (Endpoint $endpoint): string {
                return $this->className($endpoint);
            }),
            new TwigFilter('derivePropertyType', function (Property $property): string {
                return $this->derivePropertyType($property);
            }),
            new TwigFilter('derivePropertyName', function (Property $property): string {
                return $this->derivePropertyName($property);
            }),
            new TwigFilter('deriveEndpointUri', function (Endpoint $endpoint): string {
                return $this->deriveEndpointUri($endpoint);
            }),
        ];
    }

    public function namespace(Endpoint $endpoint): string
    {
        $uri = str_replace(['/api/v1/{division}', '/api/v1/current', '/'], ['', '', '\\'], $endpoint->getUri());
        $pos = strrpos($uri, '\\');

        if ($pos === false) {
            throw new \RuntimeException('Failed to find the position of the namespace seperator');
        }

        return 'ExactOnline\ApiClient\Entity' . ucwords(substr($uri, 0, $pos), '\\');
    }

    public function className(Endpoint $endpoint): string
    {
        $elements = explode('/', $endpoint->getUri());
        $taintedClassName = array_pop($elements);

        if ($taintedClassName === null) {
            throw new \RuntimeException('Unable to derive class name from endpoint uri');
        }

        return ucfirst($taintedClassName);
    }

    public function derivePropertyType(Property $property): string
    {
        if ($this->propertyIsApiEntity($property)) {
            return $this->mapPropertyTypeToApiEntityClassName($property);
        }

        switch ($property->getType()) {
            case 'Edm.Guid':
            case 'Edm.String':
            case 'Edm.DateTime':
            case 'Edm.Binary':
                return 'string';
            case 'Edm.Int64':
            case 'Edm.Int32':
            case 'Edm.Int16':
            case 'Edm.Byte':
                return 'int';
            case 'Edm.Double':
                return 'float';
            case 'Edm.Boolean':
                return 'bool';
            default:
                return $property->getType();
        }
    }

    public function derivePropertyName(Property $property): string
    {
        if ($property->getName() === 'ID') {
            return 'id';
        }

        return lcfirst($property->getName());
    }

    public function deriveEndpointUri(Endpoint $endpoint): string
    {
        return ltrim($endpoint->getUri(), '/api/v1/{division}');
    }

    private function propertyIsApiEntity(Property $property): bool
    {
        return strpos($property->getType(), 'Exact.Web.Api.Models.') === 0;
    }

    private function mapPropertyTypeToApiEntityClassName(Property $property): string
    {
        $propertyType = ltrim($property->getType(), 'Exact.Web.Api.Models.');
        $propertyType = str_replace('.', '\\', $propertyType);
        return '\ExactOnline\ApiClient\Entity\\' . $propertyType;
    }
}
