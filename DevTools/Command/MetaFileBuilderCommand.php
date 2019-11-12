<?php declare(strict_types=1);

namespace DevTools\Command;

use DevTools\Writers\MetaDataWriter;
use MetaDataTool\DocumentationCrawler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class MetaFileBuilderCommand extends Command
{
    protected static $defaultName = 'build-meta-file';

    protected function configure(): void
    {
        $this
            ->setDescription('Build the meta file based on the Exact Online API documentation')
            ->setHelp(<<<'HELP'
                This command scans the Exact Online API documentation and stores the meta data discovered 
                from the documentation.
HELP
            )
            ->setDefinition([
                new InputOption('destination', 'd', InputOption::VALUE_REQUIRED, 'The destination directory', getcwd()),
            ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /* @todo add caching support */
        $endpoints = (new DocumentationCrawler())->run();

        $writer = new MetaDataWriter(
            $this->getFullDestinationPath($input->getOption('destination')),
            new Filesystem()
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
