<?php

namespace Core\Infrastructure\Common\Command\Model\UseCaseTemplate;

use Core\Infrastructure\Common\Command\Model\DtoTemplate\RequestDtoTemplate;
use Core\Infrastructure\Common\Command\Model\FileTemplate;
use Core\Infrastructure\Common\Command\Model\PresenterTemplate\CommandPresenterInterfaceTemplate;
use Core\Infrastructure\Common\Command\Model\RepositoryTemplate\RepositoryInterfaceTemplate;

class CommandUseCaseTemplate extends FileTemplate
{
    public function __construct(
        public string $filePath,
        public string $namespace,
        public string $name,
        public CommandPresenterInterfaceTemplate $presenter,
        public RequestDtoTemplate $request,
        public RepositoryInterfaceTemplate $repository,
        public bool $exists = false
    ) {
    }

    public function generateModelContent(): string
    {
        $presenterInterfaceNamespace = $this->presenter->namespace . '\\' . $this->presenter->name;
        $presenterInterfaceName = $this->presenter->name;
        $requestNamespace = $this->request->namespace . '\\' . $this->request->name;
        $requestName = $this->request->name;
        $repositoryNamespace = $this->repository->namespace . '\\' . $this->repository->name;
        $repositoryName = $this->repository->name;
        $repositoryVariable = 'repository';
        $presenterVariable = 'presenter';
        $requestVariable = 'request';


        $content = <<<EOF
        <?php
        $this->licenceHeader
        declare(strict_types=1);

        namespace $this->namespace;

        use $presenterInterfaceNamespace;
        use $requestNamespace;
        use $repositoryNamespace;

        class $this->name
        {
            /**
             * @param $repositoryName $$repositoryVariable
             */
            public function __construct(private $repositoryName $$repositoryVariable)
            {
            }

            /**
             * @param $presenterInterfaceName $$presenterVariable
             * @param $requestName $$requestVariable
             */
            public function __invoke(
                $presenterInterfaceName $$presenterVariable,
                $requestName $$requestVariable
            ): void {
            }
        }

        EOF;

        return $content;
    }

    public function __toString()
    {
        return $this->name;
    }
}
