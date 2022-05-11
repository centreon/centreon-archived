<?php

namespace Core\Infrastructure\Common\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Core\Infrastructure\Common\Command\Model\ModelTemplate\ModelTemplate;
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
    public const READ_REPOSITORY_TYPE = 'Read';
    public const WRITE_REPOSITORY_TYPE = 'Write';

    public const COMMAND_USECASES = [self::COMMAND_CREATE, self::COMMAND_UPDATE, self::COMMAND_DELETE];
    public const QUERY_USECASES = [self::COMMAND_FIND];

    private string $useCaseType;

    private ModelTemplate $modelTemplate;

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
            ->setDescription('Create architecture for a useCase')
            ->setHelp('This command allows you to create classes for a useCase');
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
    }

    /**
     * @inheritDoc
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($this->modelTemplate->exists === false) {
            $this->commandService->createModel($this->modelTemplate);
            $output->writeln('<info>Creating Model : ' . $this->modelTemplate->namespace . '\\' . $this->modelTemplate->name . '</info>');
        } else {
            $output->writeln(
                '<info>Using Existing Model : ' . $this->modelTemplate->namespace . '\\' . $this->modelTemplate->name . '</info>'
            );
        }
        $output->writeln('<comment>' . $this->modelTemplate->filePath . '</comment>');
        if ($this->isACommandUseCase()) {
            $this->createCommandArch($output);
        } else {
            $this->createQueryArch($output);
        }
        return Command::SUCCESS;
    }

    /**
     * Check if the use case is a type Command.
     *
     * @return bool
     */
    public function isACommandUseCase(): bool
    {
        return in_array($this->useCaseType, self::COMMAND_USECASES);
    }

    /**
     * Create all the file for a Command.
     *
     * @param OutputInterface $output
     */
    private function createCommandArch(OutputInterface $output): void
    {
        $this->commandArchCommandService->createRepositoryInterfaceTemplateIfNotExist(
            $output,
            $this->modelTemplate->name,
            self::WRITE_REPOSITORY_TYPE
        );
        $this->commandArchCommandService->createFactoryIfNotExist(
            $output,
            $this->modelTemplate
        );
        $this->commandArchCommandService->createWriteRepositoryTemplateIfNotExist(
            $output,
            $this->modelTemplate->name,
        );
        $this->commandArchCommandService->createRequestDtoTemplateIfNotExist(
            $output,
            $this->modelTemplate->name,
            $this->useCaseType
        );
        $this->commandArchCommandService->createPresenterInterfaceIfNotExist(
            $output,
            $this->modelTemplate->name,
            $this->useCaseType
        );
        $this->commandArchCommandService->createPresenterIfNotExist(
            $output,
            $this->modelTemplate->name,
            $this->useCaseType
        );
        $this->commandArchCommandService->createUseCaseIfNotExist(
            $output,
            $this->modelTemplate->name,
            $this->useCaseType,
        );
        $this->commandArchCommandService->createControllerIfNotExist(
            $output,
            $this->modelTemplate->name,
            $this->useCaseType,
        );
    }

    private function createQueryArch(OutputInterface $output): void
    {
        $this->queryArchCommandService->createRepositoryInterfaceTemplateIfNotExist(
            $output,
            $this->modelTemplate->name,
            self::READ_REPOSITORY_TYPE
        );
        $this->queryArchCommandService->createFactoryIfNotExist(
            $output,
            $this->modelTemplate
        );
        $this->queryArchCommandService->createReadRepositoryTemplateIfNotExist(
            $output,
            $this->modelTemplate->name,
        );
        $this->queryArchCommandService->createResponseDtoTemplateIfNotExist(
            $output,
            $this->modelTemplate->name,
            $this->useCaseType
        );
        $this->queryArchCommandService->createPresenterInterfaceIfNotExist(
            $output,
            $this->modelTemplate->name,
            $this->useCaseType
        );
        $this->queryArchCommandService->createPresenterIfNotExist(
            $output,
            $this->modelTemplate->name,
            $this->useCaseType
        );
        $this->queryArchCommandService->createUseCaseIfNotExist(
            $output,
            $this->modelTemplate,
            $this->useCaseType,
        );
        $this->queryArchCommandService->createControllerIfNotExist(
            $output,
            $this->modelTemplate->name,
            $this->useCaseType,
        );
    }
}
