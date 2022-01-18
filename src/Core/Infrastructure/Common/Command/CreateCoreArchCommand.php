<?php

namespace Core\Infrastructure\Common\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Core\Infrastructure\Common\Command\Model\DomainModel;
use Core\Infrastructure\Common\Command\Service\CreateCoreArchCommandService;

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

    private string $useCaseType;

    private DomainModel $model;

    public function __construct(private CreateCoreArchCommandService $commandService)
    {
        $this->commandService = $commandService;
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

    public function interact(InputInterface $input, OutputInterface $output): void
    {
        $output->writeln("");
        $output->writeln('You are going to create a use case architecture.');
        $output->writeln("Let's answer few questions first !");
        $output->writeln("");

        $questionHelper = $this->getHelper('question');

        $this->useCaseType = $this->commandService->askForUseCaseType($input, $output, $questionHelper);
        $this->model = $this->commandService->askForModel($input, $output, $questionHelper);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($this->model->exists === false) {
            $this->commandService->createModel($this->model);
        }
        return Command::SUCCESS;
    }
}
