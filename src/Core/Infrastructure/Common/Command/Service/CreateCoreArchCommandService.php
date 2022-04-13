<?php

namespace Core\Infrastructure\Common\Command\Service;

use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Core\Infrastructure\Common\Command\CreateCoreArchCommand;
use Core\Infrastructure\Common\Command\Model\ModelTemplate\ModelTemplate;
use Core\Infrastructure\Common\Command\Model\RepositoryTemplate\RepositoryInterfaceTemplate;

class CreateCoreArchCommandService
{
    protected RepositoryInterfaceTemplate $repositoryInterfaceTemplate;


    /**
     * @param string $srcPath
     */
    public function __construct(private string $srcPath)
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
        $output->writeln('You have selected: [' . $useCaseType . '] Use Case Type.');
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
        $output->writeln('You have selected: [' . $modelName . '] Model.');

        //Search for already existing models.
        $foundModels = $this->searchExistingModel($modelName);
        if (!empty($foundModels)) {
            $output->writeln('');
            $output->writeln('Some Models for [' . $modelName . '] has been found:');

            // Extract namespace from files
            $existingNamespace = [];
            foreach ($foundModels as $foundModel) {
                $output->writeln('- ' . $foundModel['namespace'] . '\\' . $modelName);
                $existingNamespace[] = $foundModel['namespace'] . '\\' . $modelName;
            }
            $output->writeln('');

            $questionUseExistingModel = new ConfirmationQuestion('would you like to use one of those models ? ', false);
            $useExistingModel = $questionHelper->ask($input, $output, $questionUseExistingModel, false);

            // Use an existing model.
            if ($useExistingModel === true) {
                $questionWhichExistingModelUsed = new ChoiceQuestion(
                    'Which models would you like to use ?',
                    $existingNamespace
                );
                $modelNamespace = $questionHelper->ask($input, $output, $questionWhichExistingModelUsed);
                $namespaceValue = preg_replace('/\\\\' . $modelName . '$/', '', $modelNamespace);
                foreach ($foundModels as $key => $foundModel) {
                    if ($foundModel['namespace'] === $namespaceValue) {
                        $namespaceIndex = $key;
                        break;
                    }
                }

                return new ModelTemplate(
                    $foundModels[$namespaceIndex]['path'],
                    $foundModels[$namespaceIndex]['namespace'],
                    $modelName,
                    true
                );
            }
        }

        // If the model doesn't exist or if the user want to create a new one.
        $questionNewNamespace = new Question('In which namespace would you like to create your Model ? ');
        $newNamespace = $questionHelper->ask($input, $output, $questionNewNamespace);
        $filePath = $this->srcPath . DIRECTORY_SEPARATOR . preg_replace("/\\\\/", DIRECTORY_SEPARATOR, $newNamespace) .
            DIRECTORY_SEPARATOR . $modelName . '.php';

        return new ModelTemplate($filePath, $newNamespace, $modelName);
    }

    /**
     * Look for existing model with the same name.
     *
     * @param string $modelName
     * @return array<int,array<string,string>>
     */
    private function searchExistingModel(string $modelName): array
    {
        //Search for all model with the same name.
        $modelsInfos = iterator_to_array(
            new \GlobIterator(
                $this->srcPath . '/Core/Domain/*/Model/' . $modelName . '.php'
            )
        );

        $foundModels = [];
        $index = 0;
        // Set file informations
        foreach ($modelsInfos as $model) {
            $foundModels[$index]['path'] = $model->getRealPath();
            $fileContent = file($model->getRealPath());

            // extract namespace
            foreach ($fileContent as $fileLine) {
                if (strpos($fileLine, 'namespace') !== false) {
                    $parts = explode(' ', $fileLine);
                    $namespace = rtrim(trim($parts[1]), ';\n');
                    $foundModels[$index]['namespace'] = $namespace;
                    break;
                }
            }
            $index++;
        }

        return $foundModels;
    }

    /**
     * Create the Model file.
     *
     * @param ModelTemplate $model
     * @return void
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

    public function createRepositoryInterfaceTemplateIfNotExist(
        OutputInterface $output,
        string $modelName,
        string $repositoryType
    ): void {
        $filePath = $this->srcPath . '/Core/Application/' . $modelName . '/Repository/' . $repositoryType .
        $modelName . 'RepositoryInterface.php';
        $namespace = 'Core\\Application\\' . $modelName . '\\Repository';
        if (!file_exists($filePath)) {
            $this->repositoryInterfaceTemplate = new RepositoryInterfaceTemplate(
                $filePath,
                $namespace,
                $repositoryType . $modelName . 'RepositoryInterface',
                false
            );
            preg_match('/^(.+).Write' . $modelName . 'RepositoryInterface\.php$/', $filePath, $matches);
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
                'Creating Repository Interface : ' . $this->repositoryInterfaceTemplate->namespace . '\\'
                    . $this->repositoryInterfaceTemplate->name
            );
        } else {
            $this->repositoryInterfaceTemplate = new RepositoryInterfaceTemplate(
                $filePath,
                $namespace,
                $repositoryType . $modelName . 'RepositoryInterface',
                true
            );
            $output->writeln(
                'Using Existing Repository Interface : ' . $this->repositoryInterfaceTemplate->namespace . '\\'
                    . $this->repositoryInterfaceTemplate->name
            );
        }
    }
}
