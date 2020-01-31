<?php

declare(strict_types=1);

namespace DevTools\Command;

use DevTools\ValueObjects\OAuthClient;
use DevTools\ValueObjects\OAuthTokenSet;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class AuthorisationSetupCommand extends Command
{
    protected static $defaultName = 'authorisation-setup';

    private static $baseUrl = 'https://start.exactonline.nl';
    private static $authUrl = '/api/oauth2/auth';
    private static $tokenUrl = '/api/oauth2/token';

    private static $redirectUri = 'http://localhost:8080/oauth-endpoint.php';

    /** @var InputInterface */
    private $input;
    /** @var OutputInterface */
    private $output;
    /** @var QuestionHelper */
    private $questionHelper;
    /** @var Client */
    private $client;
    /** @var int */
    private $pid;

    protected function configure(): void
    {
        $this
            ->setDescription('Setup the authorisation for your ExactOnline API Client')
            ->setHelp(<<<'HELP'
                This command helps to setup the OAuth 2.0 authorisation used with the ExactOnline API. The command will
                ask you for the client id and the cleitn secret which can be found at https://apps.exactonline.com
HELP
            )
            ->setDefinition([
                new InputOption(
                    'output',
                    'o',
                    InputOption::VALUE_REQUIRED,
                    'The output directory for tokens',
                    getcwd()
                ),
            ]);
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->input = $input;
        $this->output = $output;
        $this->questionHelper = $this->getHelper('question');
        $this->client = new Client(['http_errors' => true, 'handler' => HandlerStack::create(), 'expect' => false]);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->checkRedirectUriValue();
        $client = $this->askClientDetails();
        $authorisationCode = $this->getAuthorisationCode($client);
        $tokenSet = $this->fetchTokens($client, $authorisationCode);
        $this->writeToFile($client, $tokenSet);

        return 0;
    }

    private function checkRedirectUriValue(): void
    {
        $question = new ConfirmationQuestion("Is the Redirect uri of your client set to: '{$this::$redirectUri}' ? ");
        $answer = $this->questionHelper->ask($this->input, $this->output, $question);
        if ($answer === false) {
            $this->output->writeln("Change the Redirect uri to '{$this::$redirectUri}' and rerun the script.");
            exit(1);
        }
    }

    private function askClientDetails(): OAuthClient
    {
        $clientId = $this->questionHelper->ask(
            $this->input,
            $this->output,
            new Question('What is the Client ID (Can be found in the Exact App Center https://apps.exactonline.com)? ')
        );

        $clientSecret = $this->questionHelper->ask(
            $this->input,
            $this->output,
            new Question('What is the Client secret (Can also be found in the Exact App Center)? ')
        );

        return new OAuthClient($clientId, $clientSecret);
    }

    private function getAuthorisationCode(OAuthClient $client): string
    {
        $this->startHttpListening();

        $url = self::$baseUrl . self::$authUrl . '?' . http_build_query([
            'client_id' => $client->getId(),
            'redirect_uri' => self::$redirectUri,
            'response_type' => 'code'
        ]);
        $this->output->writeln("Open the following url in your browser: $url");

        // Wait for file ((max 60 sec)
        $i = 0;
        $code = null;
        $filename = __DIR__ . '/../../.oauth.code';
        if (file_exists($filename)) {
            unlink($filename);
        }

        while ($i < 12) {
            if (! file_exists($filename)) {
                sleep(5);
                $i++;
            } else {
                $code = file_get_contents($filename);
                if ($code === false) {
                    $this->output->writeln('Unable to read code from file, halting!');
                    $this->stopHttpListening();
                    exit(1);
                }

                $this->output->writeln('Retrieved authorisation code.');
                break;
            }
        }

        $this->stopHttpListening();
        unlink($filename);

        if ($code === null) {
            throw new \RuntimeException('Unable to retrieve the authorisation code');
        }

        return $code;
    }

    private function startHttpListening(): void
    {
        $command = 'php -S localhost:8080 -t scripts/';
        $this->pid = (int) shell_exec(sprintf('%s > /dev/null 2>&1 & echo $!', $command));
    }

    private function stopHttpListening(): void
    {
        shell_exec(sprintf('kill %d 2>&1', $this->pid));
    }

    private function fetchTokens(OAuthClient $client, string $authorisationCode): OAuthTokenSet
    {
        $this->output->writeln('Requesting access and refresh tokens.');

        $url = self::$baseUrl . self::$tokenUrl;
        $body = [
            'form_params' => [
                'redirect_uri' => self::$redirectUri,
                'grant_type' => 'authorization_code',
                'client_id' => $client->getId(),
                'client_secret' => $client->getSecret(),
                'code' => $authorisationCode
            ]
        ];

        $response = $this->client->post($url, $body);
        $body = json_decode($response->getBody()->getContents(), true);

        return new OAuthTokenSet(
            $body['access_token'],
            new \DateTimeImmutable("now + {$body['expires_in']} sec"),
            $body['refresh_token']
        );
    }

    private function getFullDestinationPath(string $destination): string
    {
        if (strpos($destination, DIRECTORY_SEPARATOR) === 0) {
            return $destination;
        }

        return getcwd() . DIRECTORY_SEPARATOR . $destination;
    }

    private function writeToFile(OAuthClient $client, OAuthTokenSet $tokenSet): void
    {
        $output = $this->input->getOption('output');

        if (! is_string($output)) {
            throw new \InvalidArgumentException('Output parameter should be a string');
        }

        $path = $this->getFullDestinationPath($output);
        $fileName = $path . DIRECTORY_SEPARATOR . 'oauth.json';

        $data = array_merge($client->jsonSerialize(), $tokenSet->jsonSerialize());
        file_put_contents($fileName, json_encode($data, JSON_PRETTY_PRINT));

        $this->output->writeln('Access and refresh tokens are available in: ' . $fileName);
    }
}
