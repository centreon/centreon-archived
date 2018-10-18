<?php

namespace Centreon\Infrastructure\Service;

use Centreon\Domain\Entity\Command;

class CentcoreCommandService
{
    CONST CENTCORE_COMMAND_QUEUE_FILE = 'centcore.cmd';

    /**
     * @param Command $command
     * @return mixed
     */
    public function sendCommand(Command $command)
    {
        return file_put_contents(_CENTREON_VARLIB_ . '/' . self::CENTCORE_COMMAND_QUEUE_FILE,$command->getCommandLine() . "\n");
    }
}
