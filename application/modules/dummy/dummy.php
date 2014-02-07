<?php

namespace Modules;

class Dummy extends \Centreon\Core\Module
{
    public function __construct() {
        parent::__construct();
    }

    public function install() {
        parent::install();
        $this->registerHook(
            'displayMonitoringDetailPageLeft',
            'dummyBlock',
            'dummy block'
        );
        $this->registerHook(
            'actionHostAfterCreate',
            'dummyAction',
            'dummy action'
        );
    }

    public function uninstall() {
        parent::uninstall();
    }

    public static function displayMonitoringDetailPageLeft($params) {
        return array(
            realpath(dirname(__FILE__)) . '/templates/dummy.tpl',
            array(
                'message' => 'hello world'
            )
        );
    }

    public static function actionHostAfterCreate($params) {
        echo "i'm executed right after a host creation<br/>";
    }
}
