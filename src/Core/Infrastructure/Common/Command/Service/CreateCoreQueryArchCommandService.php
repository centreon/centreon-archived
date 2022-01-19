<?php

namespace Core\Infrastructure\Common\Command\Service;

use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Core\Infrastructure\Common\Command\Model\RepositoryTemplate\WriteRepositoryInterfaceTemplate;

class CreateCoreQueryArchCommandService
{
    public function __construct(private string $srcPath, private string $infrastructureDir)
    {
        $this->$infrastructureDir = $this->srcPath . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR .
            'Infrastructure';
    }

    public function askForWriteRepositoryInterfaceInformations(
        InputInterface $input,
        OutputInterface $output,
        $questionHelper,
        string $modelName
    ): WriteRepositoryInterfaceTemplate {
        $questionRepositoryInterfaceName = new Question(
            'What is the name of the repository interface ? ',
            'Write' . $modelName . 'RepositoryInterface'
        );
        $writeRepositoryInterfaceName = $questionHelper->ask($input, $output, $questionRepositoryInterfaceName);
        $foundInterfaces = $this->searchExistingWriteRepositoryInterface($writeRepositoryInterfaceName);
        if (!empty($foundInterfaces)) {
            $output->writeln('');
            $output->writeln('Some Interfaces for [' . $writeRepositoryInterfaceName . '] has been found:');

            // Extract namespace from files
            $existingNamespace = [];
            foreach ($foundInterfaces as $foundInterface) {
                $output->writeln('- ' . $foundInterface['namespace'] . '\\' . $writeRepositoryInterfaceName);
                $existingNamespace[] = $foundInterface['namespace'] . '\\' . $writeRepositoryInterfaceName;
            }
            $output->writeln('');

            $questionUseExistingInterface = new ConfirmationQuestion(
                'would you like to use one of those models ? ',
                false
            );
            $useExistingInterface = $questionHelper->ask($input, $output, $questionUseExistingInterface, false);

            // Use an existing model.
            if ($useExistingInterface === true) {
                $questionWhichExistingModelUsed = new ChoiceQuestion(
                    'Which interfaces would you like to use ? ',
                    $existingNamespace
                );
                $interfaceNamespace = $questionHelper->ask($input, $output, $questionWhichExistingModelUsed);
                $namespaceValue = preg_replace('/\\\\' . $writeRepositoryInterfaceName . '$/', '', $interfaceNamespace);
                foreach ($foundInterfaces as $key => $foundInterface) {
                    if ($foundInterface['namespace'] === $namespaceValue) {
                        $namespaceIndex = $key;
                        break;
                    }
                }
                return new WriteRepositoryInterfaceTemplate(
                    $foundInterfaces[$namespaceIndex]['path'],
                    $foundInterfaces[$namespaceIndex]['namespace'],
                    $writeRepositoryInterfaceName,
                    true
                );
            }
        }
        $questionNewNamespace = new Question('In which namespace would you like to create your Model ? ');
        $newNamespace = $questionHelper->ask($input, $output, $questionNewNamespace);
        $filePath = $this->srcPath . DIRECTORY_SEPARATOR . preg_replace("/\\\\/", DIRECTORY_SEPARATOR, $newNamespace) .
            DIRECTORY_SEPARATOR . $writeRepositoryInterfaceName . '.php';
        return new WriteRepositoryInterfaceTemplate($filePath, $newNamespace, $writeRepositoryInterfaceName);
    }

    public function searchExistingWriteRepositoryInterface(string $interfaceName): array
    {
        $interfacesInfos = iterator_to_array(
            new \GlobIterator(
                $this->srcPath . '/Core/Application/*/Repository/' . $interfaceName . '.php'
            )
        );

        $foundInterfaces = [];
        $index = 0;
        foreach ($interfacesInfos as $interface) {
            $foundInterfaces[$index]['path'] = $interface->getRealPath();
            $fileContent = file($interface->getRealPath());

            // extract namespace
            foreach ($fileContent as $fileLine) {
                if (strpos($fileLine, 'namespace') !== false) {
                    $parts = explode(' ', $fileLine);
                    $namespace = rtrim(trim($parts[1]), ';\n');
                    $foundInterfaces[$index]['namespace'] = $namespace;
                    break;
                }
            }
            $index++;
        }

        return $foundInterfaces;
    }
}
