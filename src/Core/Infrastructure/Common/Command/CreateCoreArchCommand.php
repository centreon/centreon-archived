<?php

namespace Core\Infrastructure\Common\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Core\Infrastructure\Common\Command\Model\ModelTemplate\ModelTemplate;
use Core\Infrastructure\Common\Command\Model\RepositoryTemplate\WriteRepositoryInterfaceTemplate;
use Core\Infrastructure\Common\Command\Service\CreateCoreArchCommandService;
use Core\Infrastructure\Common\Command\Service\CreateCoreCommandArchCommandService;
use Core\Infrastructure\Common\Command\Service\CreateCoreQueryArchCommandService;

class CreateCoreArchCommand extends Command
{
    public const COMMAND_NAME = 'centreon:create-core-arch';
    public const COMMAND_CREATE  = 'Create';
    public const COMMAND_UPDATE  = 'Update';
    public const COMMAND_DELETE  = 'Delete';
    public const COMMAND_FIND = 'Find';
    public const COMMAND_ACTION = [
        self::COMMAND_CREATE,
        self::COMMAND_UPDATE,
        self::COMMAND_DELETE,
        self::COMMAND_FIND
    ];

    public const QUERY_USECASES = [self::COMMAND_CREATE, self::COMMAND_UPDATE, self::COMMAND_DELETE];
    public const COMMAND_USECASES = [self::COMMAND_FIND];

    private string $useCaseType;

    private ModelTemplate $modelTemplate;

    private WriteRepositoryInterfaceTemplate $repositoryInterfaceTemplate;

    /**
     * @param CreateCoreArchCommandService $commandService
     * @param CreateCoreQueryArchCommandService $queryArchCommandService
     * @param CreateCoreCommandArchCommandService $commandArchCommandService
     */
    public function __construct(
        private CreateCoreArchCommandService $commandService,
        private CreateCoreQueryArchCommandService $queryArchCommandService,
        private CreateCoreCommandArchCommandService $commandArchCommandService
    ) {
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    public function configure(): void
    {
        $this
            ->setName(self::COMMAND_NAME)
            ->setDescription('Create architecture for a command useCase')
            ->setHelp('This command allows you to create classes for a command useCase');
    }

    /**
     * @inheritDoc
     */
    public function interact(InputInterface $input, OutputInterface $output): void
    {
        $output->writeln("");
        $output->writeln('You are going to create a use case architecture.');
        $output->writeln("Let's answer few questions first !");
        $output->writeln("");

        $questionHelper = $this->getHelper('question');

        $this->useCaseType = $this->commandService->askForUseCaseType($input, $output, $questionHelper);
        $this->modelTemplate = $this->commandService->askForModel($input, $output, $questionHelper);
        if ($this->isAQueryUseCase()) {
            $this->repositoryInterfaceTemplate =
                $this->queryArchCommandService->askForWriteRepositoryInterfaceInformations(
                    $input,
                    $output,
                    $questionHelper,
                    $this->modelTemplate->name
                );
            dump($this->repositoryInterfaceTemplate);
            // $this->repositoryTemplate = $this->queryArchCommandService->askForWriteRepositoryInformations();
            // $this->dto = $this->queryArchCommandService->askForRequestDtoInformations();
        } else {
            // $this->repositoryInterfaceTemplate =
            //     $this->commandArchCommandService->askForReadRepositoryInterfaceInformations();
            // $this->repositoryTemplate =
            //     $this->commandArchCommandService->askForReadRepositoryInformations();
            // $this->dto =
            //     $this->commandArchCommandService->askForResponseDtoInformations();
        }
    }

    /**
     * @inheritDoc
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($this->modelTemplate->exists === false) {
            $this->commandService->createModel($this->modelTemplate);
        }
        return Command::SUCCESS;
    }

    /**
     * Check if the use case is a type Query.
     *
     * @return bool
     */
    public function isAQueryUseCase(): bool
    {
        return in_array($this->useCaseType, self::QUERY_USECASES);
    }
}
