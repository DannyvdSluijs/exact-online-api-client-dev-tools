<?php declare(strict_types=1);

namespace DevTools\Command;

use DevTools\TwigExtension;
use DevTools\Writers\EntityWriter;
use MetaDataTool\JsonFileWriter;
use MetaDataTool\DocumentationCrawler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class EntityBuilderCommand extends Command
{
    protected static $defaultName = 'run';

    protected function configure(): void
    {
        $this
            ->setDescription('') /* @todo */
            ->setHelp(<<<'HELP'
                /* @todo */
HELP
            ); /* @todo */
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /* Meta data */
        $endpoints = (new DocumentationCrawler())->run();

        $twig = new Environment(new FilesystemLoader('./template/'));
        $twig->addExtension(new TwigExtension());
        $writer = new EntityWriter(
            './tmp',
            new Filesystem(),
            $twig
        );

        $writer->write($endpoints);

        return 0;
    }
}
