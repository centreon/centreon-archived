<?php

namespace Core\Infrastructure\Common\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateCommandArchCommand extends CreateArchCommand
{
    public const COMMAND_NAME = 'centreon:create-command-arch';
    public const COMMAND_CREATE  = 'create';
    public const COMMAND_UPDATE  = 'update';
    public const COMMAND_DELETE  = 'delete';
    public const COMMAND_ACTION = [self::COMMAND_CREATE, self::COMMAND_UPDATE, self::COMMAND_DELETE];

    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        $this
            ->setName(self::COMMAND_NAME)
            ->setDescription('Create architecture for a command useCase')
            ->setHelp('This command allows you to create classes for a command useCase')
            ->addArgument('actionName', InputArgument::REQUIRED, 'The name of the useCase')
            ->addArgument('entityName', InputArgument::REQUIRED, 'The name of the entity');
    }

    /**
     * {@inheritDoc}
     */
    public function execute(InputInterface $input, OutputInterface $output): void
    {
        if (!in_array($input->getArgument('actionName'), self::COMMAND_ACTION)) {
            $output->writeln(
                '<error>The actionName should be one of those: ' . implode(', ', self::COMMAND_ACTION) . '</error>'
            );
            return;
        }

        $actionName = ucfirst($input->getArgument('actionName'));
        $entityName = ucfirst($input->getArgument('entityName'));
        $useCaseName = $actionName . $entityName;

        $this->createWriteRepositoryInterfaceFile($entityName);
        $this->createWriteRepositoryFile($entityName);

        if (strtolower($actionName) === self::COMMAND_CREATE || strtolower($actionName) === self::COMMAND_UPDATE) {
            $this->createUseCaseRequestFile($entityName, $useCaseName);
            $this->createUseCaseFile($entityName, $useCaseName, $this->getUseCaseRequestName($useCaseName));
        } else {
            $this->createUseCaseFile($entityName, $useCaseName);
        }
        $this->createControllerFile($entityName, $useCaseName);
    }

    /**
     * Create the RepositoryInterface file.
     *
     * @param string $entityName
     * @return void
     */
    private function createWriteRepositoryInterfaceFile(string $entityName): void
    {
        $repositoryInterfacePath =  __DIR__ . '/../' . $entityName . '/Repository';
        if (!is_dir($repositoryInterfacePath)) {
            mkdir($repositoryInterfacePath, 0777, true);
        }
        $repositoryInterfaceName = $this->getWriteRepositoryInterfaceName($entityName);
        if (!file_exists($repositoryInterfacePath . '/' . $repositoryInterfaceName . '.php')) {
            file_put_contents(
                $repositoryInterfacePath . '/' . $repositoryInterfaceName . '.php',
                $this->generateRepositoryInterfaceContent($entityName, $repositoryInterfaceName)
            );
        }
    }

    /**
     * Create the Repository file.
     *
     * @param string $entityName
     * @return void
     */
    private function createWriteRepositoryFile(string $entityName): void
    {
        $repositoryPath = __DIR__ . '/../../Infrastructure/' . $entityName . '/Repository';
        if (!is_dir($repositoryPath)) {
            mkdir($repositoryPath, 0777, true);
        }
        $repositoryName = 'DbWrite' . $entityName . 'Repository';
        if (!file_exists($repositoryPath . '/' . $repositoryName . '.php')) {
            file_put_contents(
                $repositoryPath . '/' . $repositoryName . '.php',
                $this->generateRepositoryContent(
                    $entityName,
                    $repositoryName,
                    $this->getWriteRepositoryInterfaceName($entityName)
                )
            );
        }
    }

    /**
     * Return the Interface name.
     *
     * @param string $entityName
     * @return string
     */
    private function getWriteRepositoryInterfaceName(string $entityName): string
    {
        return 'Write' . $entityName . 'RepositoryInterface';
    }

    private function createUseCaseRequestFile(string $entityName, string $useCaseName): void
    {
        $useCasePath = __DIR__ . '/../' . $entityName . '/UseCase/' . $useCaseName;
        if (!is_dir($useCasePath)) {
            mkdir($useCasePath, 0777, true);
        }
        $useCaseRequestName = $this->getUseCaseRequestName($useCaseName);
        if (!file_exists($useCasePath . '/' . $useCaseRequestName . '.php')) {
            file_put_contents(
                $useCasePath . '/' . $useCaseRequestName . '.php',
                $this->generateUseCaseRequestContent($entityName, $useCaseName, $useCaseRequestName)
            );
        }
    }

    private function generateUseCaseRequestContent(
        string $entityName,
        string $useCaseName,
        string $useCaseRequestName
    ): string {
        $content = <<<EOF
        <?php
        $this->licenceHeader
        declare(strict_types=1);

        namespace Centreon\Application\\$entityName\UseCase\\$useCaseName;

        class $useCaseRequestName
        {
        }

        EOF;

        return $content;
    }

    private function getUseCaseRequestName(string $useCaseName): string
    {
        return $useCaseName . 'Request';
    }

    private function createUseCaseFile(string $entityName, string $useCaseName, string $useCaseRequest = null): void
    {
        $useCasePath = __DIR__ . '/../' . $entityName . '/UseCase/' . $useCaseName;
        if (!is_dir($useCasePath)) {
            mkdir($useCasePath, 0777, true);
        }
        if (!file_exists($useCasePath . '/' . $useCaseName . '.php')) {
            file_put_contents(
                $useCasePath . '/' . $useCaseName . '.php',
                $this->generateUseCaseContent($entityName, $useCaseName, $useCaseRequest)
            );
        }
    }

    private function generateUseCaseContent(
        string $entityName,
        string $useCaseName,
        string $useCaseRequest = null
    ): string {
        $writeRepositoryInterface = $this->getWriteRepositoryInterfaceName($entityName);
        $content = <<<EOF
        <?php
        $this->licenceHeader
        declare(strict_types=1);

        namespace Centreon\Application\\$entityName\UseCase\\$useCaseName;

        use Centreon\Application\\$entityName\UseCase\\$useCaseName\\$useCaseRequest;
        use Centreon\Application\\$entityName\Repository\\$writeRepositoryInterface;

        class $useCaseName
        {
            /**
             * @var $writeRepositoryInterface
             */
            private \$writeRepository;

            public function __construct($writeRepositoryInterface \$writeRepository)
            {
                \$this->writeRepository = \$writeRepository;
            }
            public function __invoke($useCaseRequest \$request): void
            {
            }
        }

        EOF;

        return $content;
    }

    private function createControllerFile(string $entityName, string $useCaseName): void
    {
        $controllerPath = __DIR__ . '/../../Infrastructure/' . $entityName . '/Api/' . $useCaseName;
        if (!is_dir($controllerPath)) {
            mkdir($controllerPath, 0777, true);
        }
        $controllerName = '' . $useCaseName . 'Controller';
        if (!file_exists($controllerPath . '/' . $controllerName . '.php')) {
            file_put_contents(
                $controllerPath . '/' . $controllerName . '.php',
                $this->generateControllerContent($entityName, $useCaseName)
            );
        }
    }

    private function generateControllerContent(string $entityName, string $useCaseName): string
    {
        $controllerName = $useCaseName . 'Controller';
        $content = <<<EOF
        <?php
        $this->licenceHeader
        declare(strict_types=1);

        namespace Centreon\Infrastructure\\$entityName\Api\\$useCaseName;

        use Centreon\Application\\$entityName\UseCase\\$useCaseName\\$useCaseName;
        use Centreon\Application\Controller\AbstractController;

        class $controllerName extends AbstractController
        {
            public function __invoke($useCaseName \$$useCaseName): void
            {
            }
        }

        EOF;
        return $content;
    }
    
}
