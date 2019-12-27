<?php

declare(strict_types=1);

namespace DevTools\Writers;

use DevTools\Exceptions\SerializationException;
use DevTools\MetaDataHelper;
use MetaDataTool\ValueObjects\Endpoint;
use MetaDataTool\ValueObjects\EndpointCollection;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Environment;

class MetaDataWriter
{
    /** @var string */
    private $basePath;
    /** @var Filesystem */
    private $filesystem;

    public function __construct(
        string $basePath,
        Filesystem $filesystem
    ) {
        $this->basePath = $basePath;
        $this->filesystem = $filesystem;
    }

    public function write(EndpointCollection $endpointCollection): void
    {
        $metaData = [];

        /** @var Endpoint $endpoint */
        foreach ($endpointCollection as $endpoint) {
            $metaData[$endpoint->getEndpoint()] = $endpoint;
        }

        $content = json_encode($metaData, JSON_PRETTY_PRINT);
        $filename = $this->basePath . '/meta-data.json';

        if ($content === false) {
            throw SerializationException::jsonEncodingFailed();
        }

        $this->filesystem->dumpFile($filename, $content);
    }
}
