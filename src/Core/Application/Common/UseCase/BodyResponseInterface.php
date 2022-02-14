<?php

namespace Core\Application\Common\UseCase;

interface BodyResponseInterface
{
    /**
     * @param mixed $body
     */
    public function setBody(mixed $body): void;

    public function getBody(): mixed;
}
