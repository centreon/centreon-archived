<?php
namespace Centreon\Application\DataRepresenter;

use JsonSerializable;

/**
 * Unification of API response
 */
class Response implements JsonSerializable
{

    /**
     * @var bool
     */
    private $status;

    /**
     * @var mixed
     */
    private $result;

    /**
     * Construct
     * 
     * @param mixed $result
     * @param bool $status
     */
    public function __construct($result, bool $status = true)
    {
        $this->status = $status;
        $this->result = $result;
    }

    /**
     * JSON serialization of response
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'status' => $this->status,
            'result' => $this->result,
        ];
    }
}
