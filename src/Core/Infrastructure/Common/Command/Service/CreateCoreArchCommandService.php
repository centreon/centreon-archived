<?php

namespace Core\Infrastructure\Common\Command\Service;

use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Core\Infrastructure\Common\Command\Model\DomainModel;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Core\Infrastructure\Common\Command\CreateCoreArchCommand;

class CreateCoreArchCommandService
{

    private $licenceHeader =
    "
/*
 * Copyright 2005 - 2021 Centreon (https://www.centreon.com/)
 *
 * Licensed under the Apache License, Version 2.0 (the \"License\");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an \"AS IS\" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * For more information : contact@centreon.com
 *
 */
";

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
            'What kind of use case would you like to create ?',
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
     * @return array<string,string> return the model path and his namespace.
     */
    public function askForModel(InputInterface $input, OutputInterface $output, $questionHelper): DomainModel
    {
        $questionModelName = new Question('For which model is this use case intended? ');
        $modelName = $questionHelper->ask($input, $output, $questionModelName);
        $output->writeln('You have selected: [' . $modelName . '] Model.');

        //Search for already existing models.
        $foundModels = $this->searchExistingModel($modelName);
        $questionNewNamespace = new Question('In which namespace would you like to create your Model ?');
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

            $questionUseExistingModel = new ConfirmationQuestion('would you like to use one of those models ?', false);
            $useExistingModel = $questionHelper->ask($input, $output, $questionUseExistingModel, false);

            // Use an existing model.
            if ($useExistingModel === true) {
                $questionWhichExistingModelUsed = new ChoiceQuestion(
                    'Which models would you like to use ?',
                    $existingNamespace
                );
                $modelIndex = $questionHelper->ask($input, $output, $questionWhichExistingModelUsed);

                return new DomainModel(
                    $foundModels[$modelIndex]['path'],
                    $foundModels[$modelIndex]['namespace'],
                    $modelName,
                    true
                );
            }
        }

        // If the model doesn't exist or if the user want to create a new one.
        $newNamespace = $questionHelper->ask($input, $output, $questionNewNamespace);
        $filePath = $this->srcPath . DIRECTORY_SEPARATOR . preg_replace("/\\\\/", DIRECTORY_SEPARATOR, $newNamespace) .
            DIRECTORY_SEPARATOR . $modelName . '.php';

        return new DomainModel($filePath, $newNamespace, $modelName);
    }

    /**
     * Look for existing model with the same name.
     *
     * @param string $modelName
     * @return array<int,array<string,string>>
     */
    private function searchExistingModel(string $modelName): array
    {
        $modelsInfos = iterator_to_array(
            new \GlobIterator(
                $this->srcPath . '/Core/Domain/*/Model/' . $modelName . '.php'
            )
        );

        $foundModels = [];
        $index = 0;
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
     * @return string
     */
    public function getLicenceHeader(): string
    {
        return $this->licenceHeader;
    }

    /**
     * Create the Model file.
     *
     * @param DomainModel $model
     * @return void
     */
    public function createModel(DomainModel $model): void
    {
        preg_match('/^(.+).' . $model->name . '\.php$/', $model->filePath, $matches);
        $dirLocation = $matches[1];

        if (!is_dir($dirLocation)) {
            mkdir($dirLocation, 0777, true);
        }
        file_put_contents($model->filePath, $this->generateModelContect($model));
    }

    private function generateModelContect(DomainModel $model): string
    {
        $content = <<<EOF
        <?php
        $this->licenceHeader
        declare(strict_types=1);

        namespace $model->namespace;

        class $model->name
        {
        }

        EOF;

        return $content;
    }
}
