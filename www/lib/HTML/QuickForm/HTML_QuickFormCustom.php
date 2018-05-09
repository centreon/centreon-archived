<?php

class HTML_QuickFormCustom extends HTML_QuickForm
{

    /** @var bool */
    public $_tokenValidated = false;


    /**
     * Class constructor
     * @param    string $formName Form's name.
     * @param    string $method (optional)Form's method defaults to 'POST'
     * @param    string $action (optional)Form's action
     * @param    string $target (optional)Form's target defaults to '_self'
     * @param    mixed $attributes (optional)Extra attributes for <form> tag
     * @param    bool $trackSubmit (optional)Whether to track if the form was submitted by adding a special hidden field
     * @access   public
     */
    public function __construct($formName = '', $method = 'post', $action = '', $target = '', $attributes = null, $trackSubmit = false)
    {
        parent::__construct($formName, $method, $action, $target, $attributes, $trackSubmit);

        $this->addFormRule([$this, 'checkSecurityToken']);
    }

    /**
     * Accepts a renderer
     *
     * @param object An HTML_QuickForm_Renderer object
     */
    public function accept(&$renderer)
    {
        $this->createSecurityToken();
        parent::accept($renderer);
    }

    /**
     * Create the CSRF Token to be set in every form using QuickForm
     */
    public function createSecurityToken()
    {
        $token = md5(uniqid());

        if (false === isset($_SESSION['x-centreon-token']) &&
            (isset($_SESSION['x-centreon-token']) &&
                false === is_array($_SESSION['x-centreon-token']))
        ) {
            $_SESSION['x-centreon-token'] = array();
            $_SESSION['x-centreon-token-generated-at'] = array();
        }

        $_SESSION['x-centreon-token'][] = $token;
        $_SESSION['x-centreon-token-generated-at'][(string)$token] = time();

        $myTokenElement = $this->addElement('hidden', 'centreon_token');
        $myTokenElement->setValue($token);
    }

    /**
     * Check if the CSRF Token is still valid
     *
     * @param array $submittedValues
     * @return boolean
     */
    public function checkSecurityToken($submittedValues)
    {
        $success = false;

        if ($this->_tokenValidated) {
            $success = true;
        } else {
            if (isset($submittedValues['centreon_token']) &&
                in_array($submittedValues['centreon_token'], $_SESSION['x-centreon-token'])
            ) {
                $elapsedTime =
                    time() - $_SESSION['x-centreon-token-generated-at'][(string)$submittedValues['centreon_token']];
                if ($elapsedTime < (15 * 60)) {
                    $key = array_search((string)$submittedValues['centreon_token'], $_SESSION['x-centreon-token']);
                    unset($_SESSION['x-centreon-token'][$key]);
                    unset($_SESSION['x-centreon-token-generated-at'][(string)$submittedValues['centreon_token']]);
                    $success = true;
                    $this->_tokenValidated = true;
                }
            }
        }

        if ($success) {
            $error = true;
        } else {
            $error = array('centreon_token' => 'The Token is invalid');
            echo "<div class='msg' align='center'>" .
                _("The form has not been submitted since 15 minutes. Please retry to resubmit") .
                "<a href='' OnLoad = windows.location(); alt='reload'> " . _("here") . "</a></div>";
        }

        $this->purgeToken();

        return $error;
    }

    /**
     * Empty all elapsed Token stored
     */
    public function purgeToken()
    {
        foreach ($_SESSION['x-centreon-token-generated-at'] as $key => $value) {
            $elapsedTime = time() - $value;

            if ($elapsedTime > (15 * 60)) {
                $tokenKey = array_search((string)$key, $_SESSION['x-centreon-token']);
                unset($_SESSION['x-centreon-token'][$tokenKey]);
                unset($_SESSION['x-centreon-token-generated-at'][(string)$key]);
            }
        }
    }
}
