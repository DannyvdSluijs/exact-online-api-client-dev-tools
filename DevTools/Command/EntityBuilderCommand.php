<?php declare(strict_types=1);

namespace DevTools\Command;

use DevTools\TwigExtension;
use DevTools\Writers\EntityWriter;
use MetaDataTool\Command\MetaDataBuilderCommand;
use MetaDataTool\ValueObjects\Endpoint;
use MetaDataTool\ValueObjects\EndpointCollection;
use MetaDataTool\ValueObjects\HttpMethodMask;
use MetaDataTool\ValueObjects\Property;
use MetaDataTool\ValueObjects\PropertyCollection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class EntityBuilderCommand extends Command
{
    protected static $defaultName = 'build-entities';

    protected function configure(): void
    {
        $this
            ->setDescription('Build all the entities based on the Exact Online API documentation')
            ->setHelp(<<<'HELP'
                This command scans the Exact Online API documentation and builds the entities based on the 
                meta data discovered.
HELP
            )
            ->setDefinition([
                new InputOption('destination', 'd', InputOption::VALUE_REQUIRED, 'The destination directory', getcwd()),
                new InputOption('refresh-meta-data', 'm', InputOption::VALUE_NONE, 'Refresh the meta data'),
            ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (! is_readable('./meta-data.json') || $input->getOption('refresh-meta-data')) {
            $this->refreshMetaData();
        }

        /* Load meta data from file */
        $endpoints = $this->loadEndpoints();

        $twig = new Environment(new FilesystemLoader('./template/'));
        $twig->addExtension(new TwigExtension());
        $writer = new EntityWriter(
            $this->getFullDestinationPath($input->getOption('destination')),
            new Filesystem(),
            $twig
        );

        $writer->write($endpoints);

        return 0;
    }

    private function getFullDestinationPath(string $destination): string
    {
        if (strpos($destination, DIRECTORY_SEPARATOR) === 0) {
            return $destination;
        }

        return getcwd() . DIRECTORY_SEPARATOR . $destination;
    }

    private function refreshMetaData(): void
    {
        $command = new MetaDataBuilderCommand();
        $command->run(new ArrayInput(['--destination' => '.']), new NullOutput());
    }

    private function loadEndpoints(): EndpointCollection
    {
        $object = json_decode(file_get_contents('meta-data.json'));
        $endpointCollection = new EndpointCollection();

        foreach ($object as $endpoint) {
            $properties = [];
            foreach ($endpoint->properties as $property) {
                $properties[] = new Property(
                    $property->name,
                    $property->type,
                    $property->description,
                    $property->primaryKey,
                    HttpMethodMask::none() /* @todo */
                );
            }
            $endpointCollection->add(new Endpoint(
                $endpoint->endpoint,
                $endpoint->documentation,
                $endpoint->scope,
                $endpoint->uri,
                HttpMethodMask::none(), /* @todo */
                $endpoint->example,
                new PropertyCollection(...$properties)
            ));
        }

        return $endpointCollection;
    }

}
