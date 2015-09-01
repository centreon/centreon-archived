<?php

/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 */

namespace Centreon\Internal\Exception;

use Centreon\Internal\Exception as CentreonException;

/**
 * Description of HttpException
 *
 * @author lionel
 */
class HttpException extends CentreonException
{
    /**
     *
     * @var type 
     */
    protected $httpErrorTitle = '';
    
    /**
     *
     * @var type 
     */
    protected $internalCode = '';
    
    /**
     * 
     * @param type $message
     * @param type $code
     * @param type $previous
     */
    public function __construct($title, $message = "", $code = 0, $internalCode = 0, $previous = NULL)
    {
        if (is_array($message)) {
            $message = implode("\n", $message);
        }
        parent::__construct($message, $code, $previous);
        $this->httpErrorTitle = $title;
        $this->internalCode = $internalCode;
    }
    
    /**
     * 
     * @return type
     */
    public function getTitle()
    {
        return $this->httpErrorTitle;
    }
    
    /**
     * 
     */
    public function getInternalCode()
    {
        
    }
}
