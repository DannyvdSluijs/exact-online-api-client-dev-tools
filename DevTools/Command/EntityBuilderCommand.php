<?php declare(strict_types=1);

namespace DevTools\Command;

use DevTools\TwigExtension;
use DevTools\Writers\EntityWriter;
use MetaDataTool\DocumentationCrawler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
            ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /* Meta data */
        $endpoints = (new DocumentationCrawler())->run();

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

}
