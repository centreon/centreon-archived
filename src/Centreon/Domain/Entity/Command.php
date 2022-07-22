<?php

namespace Centreon\Domain\Entity;

class Command
{
    final const COMMAND_START_IMPEX_WORKER = 'STARTWORKER:1';
    final const COMMAND_TRANSFER_EXPORT_FILES = 'SENDEXPORTFILE:';

    private ?string $commandLine = null;

    public function getCommandLine(): string
    {
        return $this->commandLine;
    }

    public function setCommandLine(string $commandLine): void
    {
        $this->commandLine = $commandLine;
    }
}
