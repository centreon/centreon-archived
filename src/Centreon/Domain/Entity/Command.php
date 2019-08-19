<?php

namespace Centreon\Domain\Entity;

use JMS\Serializer\Annotation as Serializer;

class Command
{
    const COMMAND_START_IMPEX_WORKER = 'STARTWORKER:1';
    const COMMAND_TRANSFER_EXPORT_FILES = 'SENDEXPORTFILE:';

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("command_line")
     */
    private $commandLine;

    /**
     * @return string
     */
    public function getCommandLine(): string
    {
        return $this->commandLine;
    }

    /**
     * @param string $commandLine
     */
    public function setCommandLine(string $commandLine): void
    {
        $this->commandLine = $commandLine;
    }
}
