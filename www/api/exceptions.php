<?php
/*
 * Copyright 2005-2015 Centreon
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

/**
 * Class for REST API exception
 */
class RestException extends Exception
{
    public function __construct($message = "", $code = 0, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

class RestBadRequestException extends RestException
{
    protected $code = 400;
    
    public function __construct($message = "", $code = 0, $previous = null)
    {
        parent::__construct($message, $this->code, $previous);
    }
}

class RestUnauthorizedException extends RestException
{
    protected $code = 401;
    
    public function __construct($message = "", $code = 0, $previous = null)
    {
        parent::__construct($message, $this->code, $previous);
    }
}

class RestForbiddenException extends RestException
{
    protected $code = 403;
    
    public function __construct($message = "", $code = 0, $previous = null)
    {
        parent::__construct($message, $this->code, $previous);
    }
}

class RestNotFoundException extends RestException
{
    protected $code = 404;
    
    public function __construct($message = "", $code = 0, $previous = null)
    {
        parent::__construct($message, $this->code, $previous);
    }
}

class RestMethodNotAllowedException extends RestException
{
    protected $code = 405;
    
    public function __construct($message = "", $code = 0, $previous = null)
    {
        parent::__construct($message, $this->code, $previous);
    }
}

class RestConflictException extends RestException
{
    protected $code = 409;
    
    public function __construct($message = "", $code = 0, $previous = null)
    {
        parent::__construct($message, $this->code, $previous);
    }
}

class RestInternalServerErrorException extends RestException
{
    protected $code = 500;
    
    public function __construct($message = "", $code = 0, $previous = null)
    {
        parent::__construct($message, $this->code, $previous);
    }
}

class RestBadGatewayException extends RestException
{
    protected $code = 502;
    
    public function __construct($message = "", $code = 0, $previous = null)
    {
        parent::__construct($message, $this->code, $previous);
    }
}

class RestServiceUnavailableException extends RestException
{
    protected $code = 503;
    
    public function __construct($message = "", $code = 0, $previous = null)
    {
        parent::__construct($message, $this->code, $previous);
    }
}

class RestGatewayTimeOutException extends RestException
{
    protected $code = 504;
    
    public function __construct($message = "", $code = 0, $previous = null)
    {
        parent::__construct($message, $this->code, $previous);
    }
}
