<?php declare(strict_types=1);

namespace DevTools;

use MetaDataTool\ValueObjects\Endpoint;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TwigExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('namespace', [$this, 'namespace']),
            new TwigFilter('className', [$this, 'className']),
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
}