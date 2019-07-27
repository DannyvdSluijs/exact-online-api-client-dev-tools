<?php declare(strict_types=1);

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
            new TwigFilter('namespace', function (Endpoint $endpoint) : string {
                return $this->namespace($endpoint);
            }),
            new TwigFilter('className', function (Endpoint $endpoint) : string {
                return $this->className($endpoint);
            }),
            new TwigFilter('derivePropertyType', function (Property $property) : string {
                return $this->derivePropertyType($property);
            }),
        ];
    }

    public function namespace(Endpoint $endpoint): string
    {
        $uri = str_replace(['/api/v1/{division}', '/api/v1/current', '/'], ['', '', '\\'], $endpoint->getUri());
        $pos = strripos($uri, '\\');
        return 'ExactOnline\ApiClient\Entity' . ucwords(substr($uri, 0, $pos), '\\');
    }

    public function className(Endpoint $endpoint): string
    {
        $elements = explode('/', $endpoint->getUri());

        return ucfirst(array_pop($elements));
    }

    public function derivePropertyType(Property $property): string
    {
        switch ($property->getType()) {
            case 'Edm.Guid':
            case 'Edm.String':
            case 'Edm.DateTime':
                return 'string';
            case 'Edm.Int32':
            case 'Edm.Int16':
                return 'int';
            default:
                return $property->getType();
        }
    }

}
