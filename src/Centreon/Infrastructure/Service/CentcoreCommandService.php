<?php

namespace Centreon\Infrastructure\Service;

use Centreon\Domain\Entity\Command;

class CentcoreCommandService
{
    /**
     * @param Command $command
     * @return mixed
     */
    public function sendCommand(Command $command)
    {
        // generate a hashed name to avoid conflict with other external commands
        $commandFile = hash('sha256', $command->getCommandLine()) . '.cmd';

        return file_put_contents(
            _CENTREON_VARLIB_ . '/centcore/' . $commandFile,
            $command->getCommandLine() . "\n",
            FILE_APPEND
        );
    }
}
