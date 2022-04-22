<?php

namespace Core\Infrastructure\Common\Command\Service;

use Core\Infrastructure\Common\Command\Model\DtoTemplate\ResponseDtoTemplate;
use Symfony\Component\Console\Output\OutputInterface;
use Core\Infrastructure\Common\Command\Model\RepositoryTemplate\WriteRepositoryTemplate;
use Core\Infrastructure\Common\Command\Model\RepositoryTemplate\WriteRepositoryInterfaceTemplate;

class CreateCoreQueryArchCommandService
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
        $filePath = $this->srcPath . '/Core/Application/' . $modelName . '/Repository/' . 'Read' .
            $modelName . 'RepositoryInterface.php';
        $namespace = 'Core\\Application\\' . $modelName . '\\Repository';
        if (!file_exists($filePath)) {
            $this->writeRepositoryInterfaceTemplate = new WriteRepositoryInterfaceTemplate(
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
                $this->writeRepositoryInterfaceTemplate->filePath,
                $this->writeRepositoryInterfaceTemplate->generateModelContent()
            );
            $output->writeln(
                'Creating Repository Interface : ' . $this->writeRepositoryInterfaceTemplate->namespace . '\\'
                    . $this->writeRepositoryInterfaceTemplate->name
            );
        } else {
            $this->writeRepositoryInterfaceTemplate = new WriteRepositoryInterfaceTemplate(
                $filePath,
                $namespace,
                'Read' . $modelName . 'RepositoryInterface',
                true
            );
            $output->writeln(
                'Using Existing Repository Interface : ' . $this->writeRepositoryInterfaceTemplate->namespace . '\\'
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
    public function createReadRepositoryTemplateIfNotExist(
        OutputInterface $output,
        string $modelName
    ): void {
        $filePath = $this->srcPath . '/Core/Infrastructure/' . $modelName . '/Repository/' . 'DbRead'
            . $modelName . 'Repository.php';
        $namespace = 'Core\\Infrastructure\\' . $modelName . '\\Repository';
        if (!file_exists($filePath)) {
            $this->writeRepositoryTemplate = new WriteRepositoryTemplate(
                $filePath,
                $namespace,
                'DbRead' . $modelName . 'Repository',
                $this->writeRepositoryInterfaceTemplate,
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
                'Creating Repository : ' . $this->writeRepositoryTemplate->namespace . '\\'
                    . $this->writeRepositoryTemplate->name
            );
        } else {
            $this->writeRepositoryTemplate = new WriteRepositoryTemplate(
                $filePath,
                $namespace,
                'DbRead' . $modelName . 'Repository',
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
    public function createResponseDtoTemplateIfNotExist(
        OutputInterface $output,
        string $modelName,
        string $useCaseType
    ): void {
        $useCaseName = $useCaseType . $modelName;
        $className = $useCaseName . 'Response';
        $filePath = $this->srcPath . '/Core/Application/' . $modelName . '/UseCase/' . $useCaseName . '\\'
            . $className . '.php';
        $namespace = 'Core\\Application\\' . $modelName . '\\UseCase\\' . $useCaseName;
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
                'Creating response : ' . $this->responseDtoTemplate->namespace . '\\'
                    . $this->responseDtoTemplate->name
            );
        } else {
            $this->responseDtoTemplate = new ResponseDtoTemplate(
                $filePath,
                $namespace,
                $className,
                true
            );
            $output->writeln(
                'Using Existing response : ' . $this->responseDtoTemplate->namespace . '\\'
                    . $this->responseDtoTemplate->name
            );
        }
    }
}
