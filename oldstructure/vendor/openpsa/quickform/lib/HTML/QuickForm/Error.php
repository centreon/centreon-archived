<?php
/**
 * Class for errors thrown by HTML_QuickForm package
 *
 * @package     HTML_QuickForm
 * @author      Adam Daniel <adaniel1@eesus.jnj.com>
 * @author      Bertrand Mansion <bmansion@mamasam.com>
 */
class HTML_QuickForm_Error extends Exception
{
    /**
     * Prefix for all error messages
     * @var string
     */
    var $error_message_prefix = 'QuickForm Error: ';

    /**
     * Creates a quickform error exception
     *
     * @param string $message The error message
     * @param int $code The error code
     */
    function __construct($message, $code = QUICKFORM_ERROR)
    {
        if (is_int($code)) {
            parent::__construct(HTML_QuickForm::errorMessage($code) . ': ' . $message, $code);
        } else {
            throw new self("Invalid error code: $code", QUICKFORM_ERROR, $this);
        }
    }
}
