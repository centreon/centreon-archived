<?php

namespace Modules;

class Dummy extends \Centreon\Core\Module
{
    public function __construct() {
        parent::__construct();
    }

    public function install() {
        parent::__install();
	$this->registerHook(
	    'displayMonitoringDetailPageLeft',
            'dummyBlock',
	    'dummy block'
        );
    }

    public function uninstall() {
        parent::__uninstall();
    }

    public static function displayMonitoringDetailPageLeft($params) {
        return array(
            realpath(dirname(__FILE__)) . '/templates/dummy.tpl',
	    array(
	        'message' => 'hello world'
	    )
	);
    }
}
