<?php

namespace Core\Infrastructure\Common\Command\Model\UnitTestTemplate;

use Core\Infrastructure\Common\Command\Model\FileTemplate;
use Core\Infrastructure\Common\Command\Model\UseCaseTemplate\CommandUseCaseTemplate;

class UnitTestTemplate extends FileTemplate
{
    public function generateContentForUseCase(CommandUseCaseTemplate $useCase)
    {
        $namespace = $useCase->namespace;

        $content = <<<EOF
        <?php
        $this->licenceHeader
        declare(strict_types=1);

        namespace $namespace;

        it('should be erased or throw an error', function () {
            expect(false)-toBeTrue();
        })

        EOF;

        return $content;
    }
}