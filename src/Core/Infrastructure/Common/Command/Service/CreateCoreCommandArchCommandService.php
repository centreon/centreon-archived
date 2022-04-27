<?php

namespace Core\Infrastructure\Common\Command\Service;

use Symfony\Component\Console\Output\OutputInterface;
use Core\Infrastructure\Common\Command\Model\DtoTemplate\RequestDtoTemplate;
use Core\Infrastructure\Common\Command\Model\UseCaseTemplate\CommandUseCaseTemplate;
use Core\Infrastructure\Common\Command\Model\PresenterTemplate\PresenterTemplate;
use Core\Infrastructure\Common\Command\Model\RepositoryTemplate\RepositoryTemplate;
use Core\Infrastructure\Common\Command\Model\ControllerTemplate\CommandControllerTemplate;
use Core\Infrastructure\Common\Command\Model\PresenterTemplate\PresenterInterfaceTemplate;

class CreateCoreCommandArchCommandService extends CreateCoreArchCommandService
{
    /**
     * @var RepositoryTemplate
     */
    private RepositoryTemplate $writeRepositoryTemplate;

    /**
     * @var RequestDtoTemplate
     */
    private RequestDtoTemplate $requestDtoTemplate;

    /**
     * @var PresenterInterfaceTemplate
     */
    private PresenterInterfaceTemplate $commandPresenterInterfaceTemplate;

    /**
     * @var CommandUseCaseTemplate
     */
    private CommandUseCaseTemplate $commandUseCaseTemplate;

    /**
     * @var PresenterTemplate
     */
    private PresenterTemplate $commandPresenterTemplate;

    /**
     * @var CommandControllerTemplate
     */
    private CommandControllerTemplate $commandControllerTemplate;

    /**
     * @param string $srcPath
     */
    public function __construct(protected string $srcPath)
    {
    }

    /**
     * Create the Write Repository file if it doesn't exist.
     *
     * @param OutputInterface $output
     * @param string $modelName
     */
    public function createWriteRepositoryTemplateIfNotExist(
        OutputInterface $output,
        string $modelName
    ): void {
        $filePath = $this->srcPath . '/Core/' . $modelName . '/Infrastructure/Repository/' . 'DbWrite'
            . $modelName . 'Repository.php';
        $namespace = 'Core\\' . $modelName . '\\Infrastructure\\Repository';
        if (!file_exists($filePath)) {
            $this->writeRepositoryTemplate = new RepositoryTemplate(
                $filePath,
                $namespace,
                'DbWrite' . $modelName . 'Repository',
                $this->repositoryInterfaceTemplate,
                false
            );
            preg_match('/^(.+).DbWrite' . $modelName . 'Repository\.php$/', $filePath, $matches);
            $dirLocation = $matches[1];
            //Create dir if not exists,
            if (!is_dir($dirLocation)) {
                mkdir($dirLocation, 0777, true);
            }

            file_put_contents(
                $this->writeRepositoryTemplate->filePath,
                $this->writeRepositoryTemplate->generateModelContent()
            );
            $output->writeln(
                'Creating Repository : ' . $this->writeRepositoryTemplate->namespace . '\\'
                    . $this->writeRepositoryTemplate->name
            );
        } else {
            $this->writeRepositoryTemplate = new RepositoryTemplate(
                $filePath,
                $namespace,
                'DbWrite' . $modelName . 'Repository',
                $this->repositoryInterfaceTemplate,
                true
            );
            $output->writeln(
                'Using Existing Repository : ' . $this->writeRepositoryTemplate->namespace . '\\'
                    . $this->repositoryInterfaceTemplate->name
            );
        }
    }

    /**
     * Create the Request Dto file if it doesn't exist.
     *
     * @param OutputInterface $output
     * @param string $modelName
     */
    public function createRequestDtoTemplateIfNotExist(
        OutputInterface $output,
        string $modelName,
        string $useCaseType
    ): void {
        $useCaseName = $useCaseType . $modelName;
        $className = $useCaseName . 'Request';
        $filePath = $this->srcPath . '/Core/' . $modelName . '/Application/UseCase/' . $useCaseName . '\\'
            . $className . '.php';
        $namespace = 'Core\\' . $modelName . '\\Application\\UseCase\\' . $useCaseName;
        if (!file_exists($filePath)) {
            $this->requestDtoTemplate = new RequestDtoTemplate(
                $filePath,
                $namespace,
                $className,
                false
            );
            preg_match('/^(.+).' . $className . '\.php$/', $filePath, $matches);
            $dirLocation = $matches[1];
            //Create dir if not exists,
            if (!is_dir($dirLocation)) {
                mkdir($dirLocation, 0777, true);
            }

            file_put_contents(
                $this->requestDtoTemplate->filePath,
                $this->requestDtoTemplate->generateModelContent()
            );
            $output->writeln(
                'Creating Request : ' . $this->requestDtoTemplate->namespace . '\\'
                    . $this->requestDtoTemplate->name
            );
        } else {
            $this->requestDtoTemplate = new RequestDtoTemplate(
                $filePath,
                $namespace,
                $className,
                true
            );
            $output->writeln(
                'Using Existing Request : ' . $this->requestDtoTemplate->namespace . '\\'
                    . $this->requestDtoTemplate->name
            );
        }
    }

    /**
     * Create the Presenter Interface file if it doesn't exist
     *
     * @param OutputInterface $output
     * @param string $modelName
     * @param string $useCaseType
     */
    public function createPresenterInterfaceIfNotExist(
        OutputInterface $output,
        string $modelName,
        string $useCaseType
    ): void {
        $useCaseName = $useCaseType . $modelName;
        $className = $useCaseName . 'PresenterInterface';
        $filePath = $this->srcPath . '/Core/' . $modelName . '/Application/UseCase/' . $useCaseName . '\\'
            . $className . '.php';
        $namespace = 'Core\\' . $modelName . '\\Application\\UseCase\\' . $useCaseName;
        if (!file_exists($filePath)) {
            $this->commandPresenterInterfaceTemplate = new PresenterInterfaceTemplate(
                $filePath,
                $namespace,
                $className,
                false
            );
            preg_match('/^(.+).' . $className . '\.php$/', $filePath, $matches);
            $dirLocation = $matches[1];
            //Create dir if not exists,
            if (!is_dir($dirLocation)) {
                mkdir($dirLocation, 0777, true);
            }
            file_put_contents(
                $this->commandPresenterInterfaceTemplate->filePath,
                $this->commandPresenterInterfaceTemplate->generateModelContent()
            );
            $output->writeln(
                'Creating Presenter Interface : ' . $this->commandPresenterInterfaceTemplate->namespace . '\\'
                    . $this->commandPresenterInterfaceTemplate->name
            );
        } else {
            $this->commandPresenterInterfaceTemplate = new PresenterInterfaceTemplate(
                $filePath,
                $namespace,
                $className,
                true
            );
            $output->writeln(
                'Using Existing Presenter Interface : ' . $this->commandPresenterInterfaceTemplate->namespace . '\\'
                    . $this->commandPresenterInterfaceTemplate->name
            );
        }
    }

    /**
     * Create the Presenter Interface file if it doesn't exist
     *
     * @param OutputInterface $output
     * @param string $modelName
     * @param string $useCaseType
     */
    public function createPresenterIfNotExist(
        OutputInterface $output,
        string $modelName,
        string $useCaseType
    ): void {
        $useCaseName = $useCaseType . $modelName;
        $className = $useCaseName . 'Presenter';
        $filePath = $this->srcPath . '/Core/' . $modelName . '/Infrastructure/Api/' . $useCaseName . '\\'
            . $className . '.php';
        $namespace = 'Core\\' . $modelName . '\\Application\\UseCase\\' . $useCaseName;
        if (!file_exists($filePath)) {
            $this->commandPresenterTemplate = new PresenterTemplate(
                $filePath,
                $namespace,
                $className,
                $this->commandPresenterInterfaceTemplate,
                false
            );
            preg_match('/^(.+).' . $className . '\.php$/', $filePath, $matches);
            $dirLocation = $matches[1];
            //Create dir if not exists,
            if (!is_dir($dirLocation)) {
                mkdir($dirLocation, 0777, true);
            }
            file_put_contents(
                $this->commandPresenterTemplate->filePath,
                $this->commandPresenterTemplate->generateModelContent()
            );
            $output->writeln(
                'Creating Presenter : ' . $this->commandPresenterTemplate->namespace . '\\'
                    . $this->commandPresenterTemplate->name
            );
        } else {
            $this->commandPresenterTemplate = new PresenterTemplate(
                $filePath,
                $namespace,
                $className,
                $this->commandPresenterInterfaceTemplate,
                true
            );
            $output->writeln(
                'Using Existing Presenter : ' . $this->commandPresenterTemplate->namespace . '\\'
                    . $this->commandPresenterTemplate->name
            );
        }
    }

    /**
     * Create the UseCase file if it doesn't exist
     *
     * @param OutputInterface $output
     * @param string $modelName
     * @param string $useCaseType
     */
    public function createUseCaseIfNotExist(
        OutputInterface $output,
        string $modelName,
        string $useCaseType
    ): void {
        $useCaseName = $useCaseType . $modelName;
        $filePath = $this->srcPath . '/Core/'  . $modelName . '/Application/UseCase/' . $useCaseName . '\\'
            . $useCaseName . '.php';
        $namespace = 'Core\\' . $modelName . '\\Application\\UseCase\\' . $useCaseName;
        if (!file_exists($filePath)) {
            $this->commandUseCaseTemplate = new CommandUseCaseTemplate(
                $filePath,
                $namespace,
                $useCaseName,
                $this->commandPresenterInterfaceTemplate,
                $this->requestDtoTemplate,
                $this->repositoryInterfaceTemplate,
                false
            );
            preg_match('/^(.+).' . $useCaseName . '\.php$/', $filePath, $matches);
            $dirLocation = $matches[1];
            //Create dir if not exists,
            if (!is_dir($dirLocation)) {
                mkdir($dirLocation, 0777, true);
            }
            file_put_contents(
                $this->commandUseCaseTemplate->filePath,
                $this->commandUseCaseTemplate->generateModelContent()
            );
            $output->writeln(
                'Creating Use Case : ' . $this->commandUseCaseTemplate->namespace . '\\'
                    . $this->commandUseCaseTemplate->name
            );
        } else {
            $this->commandUseCaseTemplate = new CommandUseCaseTemplate(
                $filePath,
                $namespace,
                $useCaseName,
                $this->commandPresenterInterfaceTemplate,
                $this->requestDtoTemplate,
                $this->repositoryInterfaceTemplate,
                true
            );
            $output->writeln(
                'Using Existing Use Case : ' . $this->commandUseCaseTemplate->namespace . '\\'
                    . $this->commandUseCaseTemplate->name
            );
        }
    }

    /**
     * Create the Controller file if it doesn't exist
     *
     * @param OutputInterface $output
     * @param string $modelName
     * @param string $useCaseType
     */
    public function createControllerIfNotExist(
        OutputInterface $output,
        string $modelName,
        string $useCaseType
    ): void {
        $useCaseName = $useCaseType . $modelName;
        $className = $useCaseName . 'Controller';
        $filePath = $this->srcPath . '/Core/' . $modelName . '/Infrastructure/Api/' . $useCaseName . '\\'
            . $className . '.php';
        $namespace = 'Core\\' . $modelName . '\\Infrastructure\\Api\\' . $useCaseName;
        if (!file_exists($filePath)) {
            $this->commandControllerTemplate = new CommandControllerTemplate(
                $filePath,
                $namespace,
                $className,
                $this->commandUseCaseTemplate,
                $this->commandPresenterInterfaceTemplate,
                $this->requestDtoTemplate,
                false
            );
            preg_match('/^(.+).' . $className . '\.php$/', $filePath, $matches);
            $dirLocation = $matches[1];
            //Create dir if not exists,
            if (!is_dir($dirLocation)) {
                mkdir($dirLocation, 0777, true);
            }
            file_put_contents(
                $this->commandControllerTemplate->filePath,
                $this->commandControllerTemplate->generateModelContent()
            );
            $output->writeln(
                'Creating Controller : ' . $this->commandControllerTemplate->namespace . '\\'
                    . $this->commandControllerTemplate->name
            );
        } else {
            $this->commandControllerTemplate = new CommandControllerTemplate(
                $filePath,
                $namespace,
                $className,
                $this->commandUseCaseTemplate,
                $this->commandPresenterInterfaceTemplate,
                $this->requestDtoTemplate,
                true
            );
            $output->writeln(
                'Using Existing Controller : ' . $this->commandControllerTemplate->namespace . '\\'
                    . $this->commandControllerTemplate->name
            );
        }
    }

    /**
     * Create the Unit tests files if they don't exist
     *
     * @param OutputInterface $output
     * @param string $modelName
     * @param string $repositoryType
     */
    public function createUnitTestsIfNotExists(
        OutputInterface $output,
        string $modelName,
        string $repositoryType
    ): void {

    }
}
