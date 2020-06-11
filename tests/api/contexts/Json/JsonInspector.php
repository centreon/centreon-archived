<?php

namespace Centreon\Tests\Api\Contexts\Json;

use Symfony\Component\PropertyAccess\PropertyAccessor;

class JsonInspector
{
    private $accessor;

    public function __construct()
    {
        $this->accessor = new PropertyAccessor(false, true);
    }

    public function evaluate(Json $json, $expression)
    {
        $expression = str_replace('->', '.', $expression);

        try {
            return json_encode($json->read($expression, $this->accessor));
        } catch (\Exception $e) {
            throw new \Exception("Failed to evaluate expression '$expression'");
        }
    }

    public function validate(Json $json, JsonSchema $schema)
    {
        $validator = new \JsonSchema\Validator();

        $resolver = new \JsonSchema\SchemaStorage(new \JsonSchema\Uri\UriRetriever, new \JsonSchema\Uri\UriResolver);
        $schema->resolve($resolver);

        return $schema->validate($json, $validator);
    }
}
