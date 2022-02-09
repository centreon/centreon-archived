<?php

namespace Core\Infrastructure\Common\Command\Model\ControllerTemplate;

use Core\Infrastructure\Common\Command\Model\DtoTemplate\RequestDtoTemplate;
use Core\Infrastructure\Common\Command\Model\FileTemplate;
use Core\Infrastructure\Common\Command\Model\PresenterTemplate\CommandPresenterInterfaceTemplate;
use Core\Infrastructure\Common\Command\Model\UseCaseTemplate\CommandUseCaseTemplate;

class CommandControllerTemplate extends FileTemplate
{
    public function __construct(
        public string $filePath,
        public string $namespace,
        public string $name,
        public CommandUseCaseTemplate $useCase,
        public CommandPresenterInterfaceTemplate $presenter,
        public RequestDtoTemplate $request,
        public bool $exists = false
    ) {
    }

    public function generateModelContent(): string
    {
        $useCaseNamespace = $this->useCase->namespace . '\\' . $this->useCase->name;
        $presenterNamespace = $this->presenter->namespace . '\\' . $this->presenter->name;
        $requestNamespace = $this->request->namespace . '\\' . $this->request->name;

        $useCaseVariable = 'useCase';
        $requestVariable = 'request';
        $requestGetContent = 'request->getContent()';
        $requestDtoVariable = lcfirst($this->request->name);
        $presenterVariable = 'presenter';
        $show = 'presenter->show()';
        $createDto = 'this->create' . $this->request->name . '($request)';
        $requestDataVariable = 'requestData';

        $content = <<<EOF
        <?php
        $this->licenceHeader
        declare(strict_types=1);

        namespace $this->namespace;

        use Symfony\Component\HttpFoundation\Request;
        use $useCaseNamespace;
        use $presenterNamespace;
        use $requestNamespace;

        class $this->name
        {
            public function __invoke(
                $this->useCase $$useCaseVariable,
                Request $$requestVariable,
                $this->presenter $$presenterVariable
            ): object {
                $$requestDtoVariable = $$createDto;
                $$useCaseVariable($$presenterVariable, $$requestDtoVariable);

                return $$show;
            }

            public function create$this->request(Request $$requestVariable): $this->request
            {
                $$requestDataVariable = json_decode((string) $$requestGetContent, true);
                $$requestDtoVariable = new $this->request($$requestDataVariable);

                return $$requestDtoVariable;
            }
        }

        EOF;

        return $content;
    }
}
