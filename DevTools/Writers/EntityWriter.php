<?php declare(strict_types=1);

namespace DevTools\Writers;

use DevTools\MetaDataHelper;
use MetaDataTool\ValueObjects\Endpoint;
use MetaDataTool\ValueObjects\EndpointCollection;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Environment;

class EntityWriter
{
    /** @var string */
    private $basePath;
    /** @var Filesystem */
    private $filesystem;
    /** @var Environment */
    private $templateEngine;

    public function __construct(
        string $basePath,
        Filesystem $filesystem,
        Environment $templateEngine
    ) {
        $this->basePath = $basePath;
        $this->filesystem = $filesystem;
        $this->templateEngine = $templateEngine;
    }

    public function write(EndpointCollection $endpointCollection): void
    {
        /** @var Endpoint $endpoint */
        foreach ($endpointCollection as $endpoint) {
            $path = MetaDataHelper::convertUriToFilePath($endpoint->getUri());

            $filename = $this->basePath . $path . '.php';
            $this->filesystem->mkdir(dirname($filename));

//
//            $uri = str_replace(['/api/v1/{division}', '/api/v1/current', '/'], ['', '', '\\'], $endpoint->getUri());
//            var_dump($uri);
//            $pos = strripos($uri, '\\');
//            return;

            $content = $this->buildContent($endpoint);


            $this->filesystem->dumpFile($filename, $content);
        }
    }

    private function buildContent(Endpoint $endpoint): string
    {
        return $this->templateEngine->render(
            'entity.php.template',
            [
                'endpoint' => $endpoint
            ]
        );
    }
}
