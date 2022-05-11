<?php

namespace Core\Infrastructure\Common\Command\Service;

use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Core\Infrastructure\Common\Command\CreateCoreArchCommand;
use Core\Infrastructure\Common\Command\Model\ModelTemplate\ModelTemplate;
use Core\Infrastructure\Common\Command\Model\RepositoryTemplate\RepositoryInterfaceTemplate;

class CreateCoreArchCommandService
{
    protected RepositoryInterfaceTemplate $repositoryInterfaceTemplate;

    /**
     * @param string $srcPath
     */
    public function __construct(protected string $srcPath)
    {
    }

    /**
     * Ask for a useCase Type
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param mixed $questionHelper
     * @return string
     */
    public function askForUseCaseType(InputInterface $input, OutputInterface $output, $questionHelper): string
    {
        $questionUseCaseType = new ChoiceQuestion(
            'What kind of use case would you like to create ? ',
            CreateCoreArchCommand::COMMAND_ACTION
        );

        $questionUseCaseType->setErrorMessage('Type %s is invalid.');
        $useCaseType = $questionHelper->ask($input, $output, $questionUseCaseType);
        $output->writeln('<info>You have selected: [' . $useCaseType . '] Use Case Type.</info>');
        $output->writeln("");
        return $useCaseType;
    }

    /**
     * ask for a Model Name.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param mixed $questionHelper
     * @return ModelTemplate return the model informations
     */
    public function askForModel(InputInterface $input, OutputInterface $output, $questionHelper): ModelTemplate
    {
        $questionModelName = new Question('For which model is this use case intended ? ');
        $modelName = $questionHelper->ask($input, $output, $questionModelName);
        $output->writeln('<info>You have selected: [' . $modelName . '] Model.</info>');
        $output->writeln("");
        //Search for already existing models.
        $foundModels = $this->searchExistingModel($modelName);
        if (!empty($foundModels)) {
            return new ModelTemplate(
                $foundModels['path'],
                $foundModels['namespace'],
                $modelName,
                true
            );
            // }
        }

        // If the model doesn't exist or if the user want to create a new one.
        $newNamespace = 'Core\\' . $modelName . '\\Domain\\Model';
        $filePath = $this->srcPath . DIRECTORY_SEPARATOR . preg_replace("/\\\\/", DIRECTORY_SEPARATOR, $newNamespace) .
            DIRECTORY_SEPARATOR . $modelName . '.php';

        return new ModelTemplate($filePath, $newNamespace, $modelName);
    }

    /**
     * Look for existing model with the same name.
     *
     * @param string $modelName
     * @return array<string,string>
     */
    private function searchExistingModel(string $modelName): array
    {
        //Search for all model with the same name.
        $modelsInfos = iterator_to_array(
            new \GlobIterator(
                $this->srcPath . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . $modelName
                    . DIRECTORY_SEPARATOR . 'Domain' . DIRECTORY_SEPARATOR . 'Model' . DIRECTORY_SEPARATOR
                    . $modelName . '.php'
            )
        );
        $modelInfo = [];
        if (! empty($modelsInfos)) {
            $foundModel = array_shift($modelsInfos);

            // Set file informations
            $modelInfo['path'] = $foundModel->getRealPath();
            $fileContent = file($foundModel->getRealPath());

            // extract namespace
            foreach ($fileContent as $fileLine) {
                if (strpos($fileLine, 'namespace') !== false) {
                    $parts = explode(' ', $fileLine);
                    $namespace = rtrim(trim($parts[1]), ';\n');
                    $modelInfo['namespace'] = $namespace;
                    break;
                }
            }
        }

        return $modelInfo;
    }

    /**
     * Create the Model file.
     *
     * @param ModelTemplate $model
     */
    public function createModel(ModelTemplate $model): void
    {
        preg_match('/^(.+).' . $model->name . '\.php$/', $model->filePath, $matches);
        $dirLocation = $matches[1];

        //Create dir if not exists,
        if (!is_dir($dirLocation)) {
            mkdir($dirLocation, 0777, true);
        }

        //Create and fill the file.
        file_put_contents($model->filePath, $model->generateModelContent());
    }

    /**
     * Undocumented function
     *
     * @param OutputInterface $output
     * @param string $modelName
     * @param string $repositoryType
     */
    public function createRepositoryInterfaceTemplateIfNotExist(
        OutputInterface $output,
        string $modelName,
        string $repositoryType
    ): void {
        $filePath = $this->srcPath . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . $modelName
            . DIRECTORY_SEPARATOR . 'Application' . DIRECTORY_SEPARATOR . 'Repository' . DIRECTORY_SEPARATOR
            . $repositoryType . $modelName . 'RepositoryInterface.php';
        $namespace = 'Core\\' . $modelName . '\\Application\\Repository';
        if (!file_exists($filePath)) {
            $this->repositoryInterfaceTemplate = new RepositoryInterfaceTemplate(
                $filePath,
                $namespace,
                $repositoryType . $modelName . 'RepositoryInterface',
                false
            );
            preg_match('/^(.+).'. $repositoryType . $modelName . 'RepositoryInterface\.php$/', $filePath, $matches);
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
                $repositoryType . $modelName . 'RepositoryInterface',
                true
            );
            $output->writeln(
                '<info>Using Existing Repository Interface : ' . $this->repositoryInterfaceTemplate->namespace . '\\'
                    . $this->repositoryInterfaceTemplate->name . '</info>'
            );
            $output->writeln('<comment>' . $filePath . '</comment>');
        }
    }
}
