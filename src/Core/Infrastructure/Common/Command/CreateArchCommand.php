<?php

namespace Core\Infrastructure\Common\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateArchCommand extends Command
{
    protected static $commandName = 'centreon:create-arch';

    protected $licenceHeader =
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
 */";


    protected function configure(): void
    {
        $this
            ->setName(self::$commandName)
            ->setDescription('Create classes for a given useCase and entity')
            ->setHelp('This command allows you to create classes for a given useCase');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
    }

    private function createController($useCaseName, $entityName): void
    {
        $controllerPath = __DIR__ . '/../../Infrastructure/' . $entityName . '/Api/' . $useCaseName;
        if (!is_dir($controllerPath)) {
            mkdir($controllerPath, 0777, true);
        }
        $controllerName = $useCaseName . 'Controller';
        file_put_contents(
            $controllerPath . '/' . $controllerName . '.php',
            $this->generateControllerContent($useCaseName, $entityName, $controllerName)
        );
    }

    private function generateControllerContent($useCaseName, $entityName, $controllerName): string
    {
        $content = <<<EOF
        <?php
        $this->licenceHeader
        declare(strict_types=1);

        namespace Centreon\Infrastructure\\$entityName\API\\$useCaseName;

        use Centreon\Infrastructure\Common\Controller\AbstractController;

        class $controllerName extends AbstractController
        {
            public function __invoke()
            {
            }
        }

        EOF;

        return $content;
    }

    private function createUseCase($useCaseName, $entityName): void
    {
        $useCasePath = __DIR__ . '/../../Application/' . $entityName . '/UseCase';
        if (!is_dir($useCasePath)) {
            mkdir($useCasePath, 0777, true);
        }
        file_put_contents(
            $useCasePath . '/' . $useCaseName . '.php',
            $this->generateUseCaseContent($useCaseName, $entityName)
        );
    }

    private function generateUseCaseContent($useCaseName, $entityName): string
    {
        $content = <<<EOF
        <?php
        $this->licenceHeader
        declare(strict_types=1);

        namespace Centreon\Application\\$entityName\UseCase;

        class $useCaseName
        {
            public function __invoke()
            {
            }
        }

        EOF;
        return $content;
    }

    protected function generateRepositoryContent(
        string $entityName,
        string $repositoryName,
        string $repositoryInterfaceName
    ): string {
        $content = <<<EOF
        <?php
        $this->licenceHeader
        declare(strict_types=1);

        namespace Centreon\Infrastructure\\$entityName\Repository;

        use Centreon\Application\\$entityName\Repository\\$repositoryInterfaceName;

        class $repositoryName implements $repositoryInterfaceName
        {
        }

        EOF;
        return $content;
    }

    protected function generateRepositoryInterfaceContent(
        string $entityName,
        string $repositoryInterfaceName
    ): string {
        $content = <<<EOF
        <?php
        $this->licenceHeader
        declare(strict_types=1);

        namespace Centreon\Application\\$entityName\Repository;

        interface $repositoryInterfaceName
        {
        }

        EOF;
        return $content;
    }
}
