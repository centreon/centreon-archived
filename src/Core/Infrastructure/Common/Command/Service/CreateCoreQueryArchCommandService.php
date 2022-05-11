<?php

namespace Core\Infrastructure\Common\Command\Service;

use Symfony\Component\Console\Output\OutputInterface;
use Core\Infrastructure\Common\Command\Model\ModelTemplate\ModelTemplate;
use Core\Infrastructure\Common\Command\Model\DtoTemplate\ResponseDtoTemplate;
use Core\Infrastructure\Common\Command\Model\FactoryTemplate\FactoryTemplate;
use Core\Infrastructure\Common\Command\Model\PresenterTemplate\PresenterTemplate;
use Core\Infrastructure\Common\Command\Model\UseCaseTemplate\QueryUseCaseTemplate;
use Core\Infrastructure\Common\Command\Model\RepositoryTemplate\RepositoryTemplate;
use Core\Infrastructure\Common\Command\Model\ControllerTemplate\QueryControllerTemplate;
use Core\Infrastructure\Common\Command\Model\PresenterTemplate\PresenterInterfaceTemplate;
use Core\Infrastructure\Common\Command\Model\RepositoryTemplate\RepositoryInterfaceTemplate;

class CreateCoreQueryArchCommandService extends CreateCoreArchCommandService
{
    public function __construct(protected string $srcPath)
    {
    }

    /**
     * Create the Write Repository Interface file if it doesn't exist.
     *
     * @param OutputInterface $output
     * @param string $modelName
     */
    public function createReadRepositoryInterfaceTemplateIfNotExist(
        OutputInterface $output,
        string $modelName
    ): void {
        $filePath = $this->srcPath . '/Core/' . $modelName . '/Application/Repository/' . 'Read' .
            $modelName . 'RepositoryInterface.php';
        $namespace = 'Core\\' . $modelName . '\\Application\\Repository';
        if (!file_exists($filePath)) {
            $this->repositoryInterfaceTemplate = new RepositoryInterfaceTemplate(
                $filePath,
                $namespace,
                'Read' . $modelName . 'RepositoryInterface',
                false
            );
            preg_match('/^(.+).Read' . $modelName . 'RepositoryInterface\.php$/', $filePath, $matches);
            $dirLocation = $matches[1];
            //Create dir if not exists,
            if (!is_dir($dirLocation)) {
                mkdir($dirLocation, 0777, true);
            }

            file_put_contents(
                $this->repositoryInterfaceTemplate->filePath,
                $this->repositoryInterfaceTemplate->generateModelContent()
            );
            $output->writeln(
                '<info>Creating Repository Interface : ' . $this->repositoryInterfaceTemplate->namespace . '\\'
                    . $this->repositoryInterfaceTemplate->name . '</info>'
            );
            $output->writeln('<comment>' . $filePath . '</comment>');
        } else {
            $this->repositoryInterfaceTemplate = new RepositoryInterfaceTemplate(
                $filePath,
                $namespace,
                'Read' . $modelName . 'RepositoryInterface',
                true
            );
            $output->writeln(
                '<info>Using Existing Repository Interface : ' . $this->repositoryInterfaceTemplate->namespace . '\\'
                    . $this->repositoryInterfaceTemplate->name . '</info>'
            );
            $output->writeln('<comment>' . $filePath . '</comment>');
        }
    }

    /**
     * Create the Write Repository file if it doesn't exist.
     *
     * @param OutputInterface $output
     * @param string $modelName
     */
    public function createReadRepositoryTemplateIfNotExist(
        OutputInterface $output,
        string $modelName
    ): void {
        $filePath = $this->srcPath . '/Core/' . $modelName . '/Infrastructure/Repository/' . 'DbRead'
            . $modelName . 'Repository.php';
        $namespace = 'Core\\' . $modelName . '\\Infrastructure\\Repository';
        if (!file_exists($filePath)) {
            $this->writeRepositoryTemplate = new RepositoryTemplate(
                $filePath,
                $namespace,
                'DbRead' . $modelName . 'Repository',
                $this->repositoryInterfaceTemplate,
                false
            );
            preg_match('/^(.+).DbRead' . $modelName . 'Repository\.php$/', $filePath, $matches);
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
                '<info>Creating Repository : ' . $this->writeRepositoryTemplate->namespace . '\\'
                    . $this->writeRepositoryTemplate->name . '</info>'
            );
            $output->writeln('<comment>' . $filePath . '</comment>');
        } else {
            $this->writeRepositoryTemplate = new RepositoryTemplate(
                $filePath,
                $namespace,
                'DbRead' . $modelName . 'Repository',
                $this->repositoryInterfaceTemplate,
                true
            );
            $output->writeln(
                '<info>Using Existing Repository : ' . $this->writeRepositoryTemplate->namespace . '\\'
                    . $this->writeRepositoryTemplate->name . '</info>'
            );
            $output->writeln('<comment>' . $filePath . '</comment>');
        }
    }

    /**
     * Create the Request Dto file if it doesn't exist.
     *
     * @param OutputInterface $output
     * @param string $modelName
     * @return void
     */
    public function createResponseDtoTemplateIfNotExist(
        OutputInterface $output,
        string $modelName,
        string $useCaseType
    ): void {
        $useCaseName = $useCaseType . $modelName;
        $className = $useCaseName . 'Response';
        $filePath = $this->srcPath . '/Core/' . $modelName . '/Application/UseCase/' . $useCaseName . '\\'
            . $className . '.php';
        $namespace = 'Core\\' . $modelName . '\\Application\\UseCase\\' . $useCaseName;
        if (!file_exists($filePath)) {
            $this->responseDtoTemplate = new ResponseDtoTemplate(
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
                $this->responseDtoTemplate->filePath,
                $this->responseDtoTemplate->generateModelContent()
            );
            $output->writeln(
                '<info>Creating Response : ' . $this->responseDtoTemplate->namespace . '\\'
                    . $this->responseDtoTemplate->name . '</info>'
            );
            $output->writeln('<comment>' . $filePath . '</comment>');
        } else {
            $this->responseDtoTemplate = new ResponseDtoTemplate(
                $filePath,
                $namespace,
                $className,
                true
            );
            $output->writeln(
                '<info>Using Existing Response : ' . $this->responseDtoTemplate->namespace . '\\'
                    . $this->responseDtoTemplate->name . '</info>'
            );
            $output->writeln('<comment>' . $filePath . '</comment>');
        }
    }

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
                '<info>Creating Presenter Interface : ' . $this->commandPresenterInterfaceTemplate->namespace . '\\'
                    . $this->commandPresenterInterfaceTemplate->name . '</info>'
            );
            $output->writeln('<comment>' . $filePath . '</comment>');
        } else {
            $this->commandPresenterInterfaceTemplate = new PresenterInterfaceTemplate(
                $filePath,
                $namespace,
                $className,
                true
            );
            $output->writeln(
                '<info>Using Existing Presenter Interface : ' . $this->commandPresenterInterfaceTemplate->namespace . '\\'
                    . $this->commandPresenterInterfaceTemplate->name . '</info>'
            );
            $output->writeln('<comment>' . $filePath . '</comment>');
        }
    }

    public function createPresenterIfNotExist(
        OutputInterface $output,
        string $modelName,
        string $useCaseType
    ): void {
        $useCaseName = $useCaseType . $modelName;
        $className = $useCaseName . 'Presenter';
        $filePath = $this->srcPath . '/Core/' . $modelName . '/Infrastructure/Api/' . $useCaseName . '\\'
            . $className . '.php';
        $namespace = 'Core\\' . $modelName . '\\Infrastructure\\Api\\' . $useCaseName;
        if (!file_exists($filePath)) {
            $this->CommandPresenterTemplate = new PresenterTemplate(
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
                $this->CommandPresenterTemplate->filePath,
                $this->CommandPresenterTemplate->generateModelContent()
            );
            $output->writeln(
                '<info>Creating Presenter : ' . $this->CommandPresenterTemplate->namespace . '\\'
                    . $this->CommandPresenterTemplate->name . '</info>'
            );
            $output->writeln('<comment>' . $filePath . '</comment>');
        } else {
            $this->CommandPresenterTemplate = new PresenterTemplate(
                $filePath,
                $namespace,
                $className,
                $this->commandPresenterInterfaceTemplate,
                true
            );
            $output->writeln(
                '<info>Using Existing Presenter : ' . $this->CommandPresenterTemplate->namespace . '\\'
                    . $this->CommandPresenterTemplate->name . '</info>'
            );
            $output->writeln('<comment>' . $filePath . '</comment>');
        }
    }

    public function createUseCaseIfNotExist(
        OutputInterface $output,
        ModelTemplate $modelTemplate,
        string $useCaseType
    ): void {
        $useCaseName = $useCaseType . $modelTemplate->name;
        $filePath = $this->srcPath . '/Core/'  . $modelTemplate->name . '/Application/UseCase/' . $useCaseName . '\\'
            . $useCaseName . '.php';
        $namespace = 'Core\\' . $modelTemplate->name . '\\Application\\UseCase\\' . $useCaseName;
        if (!file_exists($filePath)) {
            $this->queryUseCaseTemplate = new QueryUseCaseTemplate(
                $filePath,
                $namespace,
                $useCaseName,
                $this->commandPresenterInterfaceTemplate,
                $this->responseDtoTemplate,
                $this->repositoryInterfaceTemplate,
                false,
                $modelTemplate
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
                '<info>Creating Use Case : ' . $this->queryUseCaseTemplate->namespace . '\\'
                    . $this->queryUseCaseTemplate->name . '</info>'
            );
            $output->writeln('<comment>' . $filePath . '</comment>');
        } else {
            $this->queryUseCaseTemplate = new QueryUseCaseTemplate(
                $filePath,
                $namespace,
                $useCaseName,
                $this->commandPresenterInterfaceTemplate,
                $this->responseDtoTemplate,
                $this->repositoryInterfaceTemplate,
                true,
                $modelTemplate
            );
            $output->writeln(
                '<info>Using Existing Use Case : ' . $this->queryUseCaseTemplate->namespace . '\\'
                    . $this->queryUseCaseTemplate->name . '</info>'
            );
            $output->writeln('<comment>' . $filePath . '</comment>');
        }
    }

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
            $this->queryControllerTemplate = new QueryControllerTemplate(
                $filePath,
                $namespace,
                $className,
                $this->queryUseCaseTemplate,
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
                $this->queryControllerTemplate->filePath,
                $this->queryControllerTemplate->generateModelContent()
            );
            $output->writeln(
                '<info>Creating Controller : ' . $this->queryControllerTemplate->namespace . '\\'
                    . $this->queryControllerTemplate->name . '</info>'
            );
            $output->writeln('<comment>' . $filePath . '</comment>');
        } else {
            $this->queryControllerTemplate = new QueryControllerTemplate(
                $filePath,
                $namespace,
                $className,
                $this->queryUseCaseTemplate,
                $this->commandPresenterInterfaceTemplate,
                true
            );
            $output->writeln(
                '<info>Using Existing Controller : ' . $this->queryControllerTemplate->namespace . '\\'
                    . $this->queryControllerTemplate->name . '</info>'
            );
            $output->writeln('<comment>' . $filePath . '</comment>');
        }
    }

    /**
     * Create the Factory file if it doesn't exist
     *
     * @param OutputInterface $output
     * @param ModelTemplate $modelTemplate
     */
    public function createFactoryIfNotExist(
        OutputInterface $output,
        ModelTemplate $modelTemplate,
    ): void {
        $className = 'Db' . $modelTemplate->name . 'Factory';
        $filePath = $this->srcPath . '/Core/' . $modelTemplate->name . '/Infrastructure/Repository/'
            . $className. '.php';
        $namespace = 'Core\\' . $modelTemplate->name . '\\Infrastructure\\Repository';
        if (!file_exists($filePath)) {
            $this->factoryTemplate = new FactoryTemplate(
                $filePath,
                $namespace,
                $className,
                $modelTemplate
            );
            preg_match('/^(.+).' . $className . '\.php$/', $filePath, $matches);
            $dirLocation = $matches[1];
            //Create dir if not exists,
            if (!is_dir($dirLocation)) {
                mkdir($dirLocation, 0777, true);
            }
            file_put_contents(
                $this->factoryTemplate->filePath,
                $this->factoryTemplate->generateModelContent()
            );
            $output->writeln(
                '<info>Creating Factory : ' . $this->factoryTemplate->namespace . '\\'
                    . $this->factoryTemplate->name . '</info>'
            );
            $output->writeln('<comment>' . $filePath . '</comment>');
        } else {
            $this->factoryTemplate = new FactoryTemplate(
                $filePath,
                $namespace,
                $className,
                $modelTemplate,
                true
            );
            $output->writeln(
                '<info>Using Existing Factory : ' . $this->factoryTemplate->namespace . '\\'
                    . $this->factoryTemplate->name . '</info>'
            );
            $output->writeln('<comment>' . $filePath . '</comment>');
        }
    }
}
