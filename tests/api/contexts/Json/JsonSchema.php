<?php

namespace Centreon\Tests\Api\Contexts\Json;

use JsonSchema\SchemaStorage;
use JsonSchema\Validator;

class JsonSchema extends Json
{
    private $uri;

    public function __construct($content, $uri = null)
    {
        $this->uri = $uri;

        parent::__construct($content);
    }

    public function resolve(SchemaStorage $resolver)
    {
        if (!$this->hasUri()) {
            return $this;
        }

        $this->content = $resolver->resolveRef($this->uri);

        return $this;
    }

    public function validate(Json $json, Validator $validator)
    {
        $validator->check($json->getContent(), $this->getContent());

        if (!$validator->isValid()) {
            $msg = "JSON does not validate. Violations:".PHP_EOL;
            foreach ($validator->getErrors() as $error) {
                $msg .= sprintf("  - [%s] %s".PHP_EOL, $error['property'], $error['message']);
            }
            throw new \Exception($msg);
        }

        return true;
    }

    private function hasUri()
    {
        return null !== $this->uri;
    }
}
