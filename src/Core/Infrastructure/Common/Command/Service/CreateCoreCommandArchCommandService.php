<?php

namespace Core\Infrastructure\Common\Command\Service;

use Symfony\Component\Console\Output\OutputInterface;
use Core\Infrastructure\Common\Command\Model\DtoTemplate\RequestDtoTemplate;
use Core\Infrastructure\Common\Command\Model\PresenterTemplate\QueryPresenterTemplate;
use Core\Infrastructure\Common\Command\Model\RepositoryTemplate\WriteRepositoryTemplate;
use Core\Infrastructure\Common\Command\Model\PresenterTemplate\QueryPresenterInterfaceTemplate;
use Core\Infrastructure\Common\Command\Model\RepositoryTemplate\WriteRepositoryInterfaceTemplate;
use Core\Infrastructure\Common\Command\Model\UseCaseTemplate\QueryUseCaseTemplate;

class CreateCoreCommandArchCommandService
{
    private WriteRepositoryInterfaceTemplate $writeRepositoryInterfaceTemplate;
    private WriteRepositoryTemplate $writeRepositoryTemplate;
    private RequestDtoTemplate $requestDtoTemplate;
    private QueryPresenterInterfaceTemplate $queryPresenterInterfaceTemplate;
    private QueryUseCaseTemplate $queryUseCaseTemplate;

    public function __construct(private string $srcPath)
    {
    }

    /**
     * Create the Write Repository Interface file if it doesn't exist.
     *
     * @param OutputInterface $output
     * @param string $modelName
     */
    public function createWriteRepositoryInterfaceTemplateIfNotExist(
        OutputInterface $output,
        string $modelName
    ): void {
        $filePath = $this->srcPath . '/Core/Application/' . $modelName . '/Repository/' . 'Write' .
            $modelName . 'RepositoryInterface.php';
        $namespace = 'Core\\Application\\' . $modelName . '\\Repository';
        if (!file_exists($filePath)) {
            $this->writeRepositoryInterfaceTemplate = new WriteRepositoryInterfaceTemplate(
                $filePath,
                $namespace,
                'Write' . $modelName . 'RepositoryInterface',
                false
            );
            preg_match('/^(.+).Write' . $modelName . 'RepositoryInterface\.php$/', $filePath, $matches);
            $dirLocation = $matches[1];
            //Create dir if not exists,
            if (!is_dir($dirLocation)) {
                mkdir($dirLocation, 0777, true);
            }

            file_put_contents(
                $this->writeRepositoryInterfaceTemplate->filePath,
                $this->writeRepositoryInterfaceTemplate->generateModelContent()
            );
            $output->writeln(
                'Creating Interface : ' . $this->writeRepositoryInterfaceTemplate->namespace . '\\'
                    . $this->writeRepositoryInterfaceTemplate->name
            );
        } else {
            $this->writeRepositoryInterfaceTemplate = new WriteRepositoryInterfaceTemplate(
                $filePath,
                $namespace,
                'Write' . $modelName . 'RepositoryInterface',
                true
            );
            $output->writeln(
                'Using Existing Interface : ' . $this->writeRepositoryInterfaceTemplate->namespace . '\\'
                    . $this->writeRepositoryInterfaceTemplate->name
            );
        }
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
        $filePath = $this->srcPath . '/Core/Infrastructure/' . $modelName . '/Repository/' . 'DbWrite'
            . $modelName . 'Repository.php';
        $namespace = 'Core\\Infrastructure\\' . $modelName . '\\Repository';
        if (!file_exists($filePath)) {
            $this->writeRepositoryTemplate = new WriteRepositoryTemplate(
                $filePath,
                $namespace,
                'DbWrite' . $modelName . 'Repository',
                $this->writeRepositoryInterfaceTemplate,
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
            $this->writeRepositoryTemplate = new WriteRepositoryTemplate(
                $filePath,
                $namespace,
                'DbWrite' . $modelName . 'Repository',
                $this->writeRepositoryInterfaceTemplate,
                true
            );
            $output->writeln(
                'Using Existing Repository : ' . $this->writeRepositoryTemplate->namespace . '\\'
                    . $this->writeRepositoryTemplate->name
            );
        }
    }

    /**
     * Create the Request Dto file if it doesn't exist.
     *
     * @param OutputInterface $output
     * @param string $modelName
     * @return void
     */
    public function createRequestDtoTemplateIfNotExist(
        OutputInterface $output,
        string $modelName,
        string $useCaseType
    ): void {
        $useCaseName = $useCaseType . $modelName;
        $className = $useCaseName . 'Request';
        $filePath = $this->srcPath . '/Core/Application/' . $modelName . '/UseCase/' . $useCaseName . '\\'
            . $className . '.php';
        $namespace = 'Core\\Application\\' . $modelName . '\\UseCase\\' . $useCaseName;
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

    public function createPresenterInterfaceIfNotExist(
        OutputInterface $output,
        string $modelName,
        string $useCaseType
    ): void {
        $useCaseName = $useCaseType . $modelName;
        $className = $useCaseName . 'PresenterInterface';
        $filePath = $this->srcPath . '/Core/Application/' . $modelName . '/UseCase/' . $useCaseName . '\\'
            . $className . '.php';
        $namespace = 'Core\\Application\\' . $modelName . '\\UseCase\\' . $useCaseName;
        if (!file_exists($filePath)) {
            $this->queryPresenterInterfaceTemplate = new QueryPresenterInterfaceTemplate(
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
                $this->queryPresenterInterfaceTemplate->filePath,
                $this->queryPresenterInterfaceTemplate->generateModelContent()
            );
            $output->writeln(
                'Creating Presenter Interface : ' . $this->queryPresenterInterfaceTemplate->namespace . '\\'
                    . $this->queryPresenterInterfaceTemplate->name
            );
        } else {
            $this->queryPresenterInterfaceTemplate = new QueryPresenterInterfaceTemplate(
                $filePath,
                $namespace,
                $className,
                true
            );
            $output->writeln(
                'Using Existing Presenter Interface : ' . $this->queryPresenterInterfaceTemplate->namespace . '\\'
                    . $this->queryPresenterInterfaceTemplate->name
            );
        }
    }

    public function createPresenterIfNotExist(
        OutputInterface $output,
        string $modelName,
        string $useCaseType
    ): void {
        $useCaseName = $useCaseType . $modelName;
        $className = $useCaseName . 'Presenter';
        $filePath = $this->srcPath . '/Core/Infrastructure/' . $modelName . '/Api/' . $useCaseName . '\\'
            . $className . '.php';
        $namespace = 'Core\\Application\\' . $modelName . '\\UseCase\\' . $useCaseName;
        if (!file_exists($filePath)) {
            $this->queryPresenterTemplate = new QueryPresenterTemplate(
                $filePath,
                $namespace,
                $className,
                $this->queryPresenterInterfaceTemplate,
                false
            );
            preg_match('/^(.+).' . $className . '\.php$/', $filePath, $matches);
            $dirLocation = $matches[1];
            //Create dir if not exists,
            if (!is_dir($dirLocation)) {
                mkdir($dirLocation, 0777, true);
            }
            file_put_contents(
                $this->queryPresenterTemplate->filePath,
                $this->queryPresenterTemplate->generateModelContent()
            );
            $output->writeln(
                'Creating Presenter : ' . $this->queryPresenterTemplate->namespace . '\\'
                    . $this->queryPresenterTemplate->name
            );
        } else {
            $this->queryPresenterTemplate = new QueryPresenterTemplate(
                $filePath,
                $namespace,
                $className,
                $this->queryPresenterInterfaceTemplate,
                true
            );
            $output->writeln(
                'Using Existing Presenter : ' . $this->queryPresenterTemplate->namespace . '\\'
                    . $this->queryPresenterTemplate->name
            );
        }
    }

    public function createUseCaseIfNotExist(
        OutputInterface $output,
        string $modelName,
        string $useCaseType
    ): void {
        $useCaseName = $useCaseType . $modelName;
        $filePath = $this->srcPath . '/Core/Application/' . $modelName . '/UseCase/' . $useCaseName . '\\'
            . $useCaseName . '.php';
        $namespace = 'Core\\Application\\' . $modelName . '\\UseCase\\' . $useCaseName;
        if (!file_exists($filePath)) {
            $this->queryUseCaseTemplate = new QueryUseCaseTemplate(
                $filePath,
                $namespace,
                $useCaseName,
                $this->queryPresenterInterfaceTemplate,
                $this->requestDtoTemplate,
                $this->writeRepositoryInterfaceTemplate,
                false
            );
            preg_match('/^(.+).' . $useCaseName . '\.php$/', $filePath, $matches);
            $dirLocation = $matches[1];
            //Create dir if not exists,
            if (!is_dir($dirLocation)) {
                mkdir($dirLocation, 0777, true);
            }
            file_put_contents(
                $this->queryUseCaseTemplate->filePath,
                $this->queryUseCaseTemplate->generateModelContent()
            );
            $output->writeln(
                'Creating Use Case : ' . $this->queryUseCaseTemplate->namespace . '\\'
                    . $this->queryUseCaseTemplate->name
            );
        } else {
            $this->queryUseCaseTemplate = new QueryUseCaseTemplate(
                $filePath,
                $namespace,
                $useCaseName,
                $this->queryPresenterInterfaceTemplate,
                $this->requestDtoTemplate,
                $this->writeRepositoryInterfaceTemplate,
                true
            );
            $output->writeln(
                'Using Existing Use Case : ' . $this->queryUseCaseTemplate->namespace . '\\'
                    . $this->queryUseCaseTemplate->name
            );
        }
    }

    public function createControllerIfNotExist(
        OutputInterface $output,
        string $modelName,
        string $useCaseType
    ): void {
        $useCaseName = $useCaseType . $modelName;
        $className = $useCaseName . 'Controller';
        $filePath = $this->srcPath . '/Core/Infrastructure/' . $modelName . '/Api/' . $useCaseName . '\\'
            . $className . '.php';
        $namespace = 'Core\\Infrastructure\\' . $modelName . '\\Api\\' . $useCaseName;
        if (!file_exists($filePath)) {
            $this->queryControllerTemplate = new QueryControllerTemplate(
                $filePath,
                $namespace,
                $useCaseName,
                $this->queryPresenterInterfaceTemplate,
                $this->requestDtoTemplate,
                $this->writeRepositoryInterfaceTemplate,
                false
            );
            preg_match('/^(.+).' . $useCaseName . '\.php$/', $filePath, $matches);
            $dirLocation = $matches[1];
            //Create dir if not exists,
            if (!is_dir($dirLocation)) {
                mkdir($dirLocation, 0777, true);
            }
            file_put_contents(
                $this->queryControllerTemplate->filePath,
                $this->queryControllerTemplate->generateModelContent()
            );
            $output->writeln(
                'Creating Use Case : ' . $this->queryControllerTemplate->namespace . '\\'
                    . $this->queryControllerTemplate->name
            );
        } else {
            $this->queryControllerTemplate = new QueryControllerTemplate(
                $filePath,
                $namespace,
                $useCaseName,
                $this->queryPresenterInterfaceTemplate,
                $this->requestDtoTemplate,
                $this->writeRepositoryInterfaceTemplate,
                true
            );
            $output->writeln(
                'Using Existing Use Case : ' . $this->queryControllerTemplate->namespace . '\\'
                    . $this->queryControllerTemplate->name
            );
        }
    }
}
