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
 * For more information : contact@centreon.com
 * 
 */

namespace Centreon\Internal;

/**
 * Class for loading logger informations
 *
 * @see http://www.php.net/manual/en/class.pdo.php
 * @authors Maximilien Bersoult
 * @package Centreon
 * @subpackage Core
 */
class Logger
{
    private static $loggerHandler = array(
        'file' => array(
            'class' => '\Monolog\Handler\StreamHandler',
            'extraArgs' => array(
                'filename'
            ),
            'callbackArgs' => null
        ),
        'syslog' => array(
            'class' => '\Monolog\Handler\SyslogHandler',
            'extraArgs' => array(
                'ident',
                'facility'
            ),
            'callbackArgs' => null
        ),
        'phplog' => array(
            'class' => '\Monolog\Handler\ErrorLogHandler',
            'extraArgs' => array(
                'messageType'
            ),
            'callbackArgs' => null
        ),
        'chromephp' => array(
            'class' => '\Monolog\Handler\ChromePHPHandler',
            'extraArgs' => array(),
            'callbackArgs' => null
        ),
        'firephp' => array(
            'class' => '\Monolog\Handler\FirePHPHandler',
            'extraArgs' => array(),
            'callbackArgs' => null
        )
    );

    private static $reflectionHandler = array();

    /**
     * Load loggers and register them into \Monolog\Registry
     *
     * @param $config \Centreon\Config The application configuration
     */
    public static function load($config)
    {
        $logger = new \Monolog\Logger('MAIN');
        $loggers = $config->get('loggers', 'logger');
        foreach ($loggers as $loggerName) {
            $loggerType = $config->get('logger_' . $loggerName, 'type');
            if (!is_null($loggerType) && isset(self::$loggerHandler[$loggerType])) {
                try {
                    $handler = self::createHandler($config->getGroup('logger_' . $loggerName), $loggerName);
                    $logger->pushHandler($handler);
                } catch (\Exception $e) {
                }
            }
        }
        \Monolog\Registry::addLogger($logger, $logger->getName(), true);
    }

    /**
     * Create an logger handler
     *
     * @param $info array The information for the handler
     * @param $loggerName string The logger name
     * @return \Monolog\AbstractHandler
     * @throws \Centreon\Exception If a parameters doesn't set in configuration
     */
    private static function createHandler($info, $loggerName)
    {
        /* Init relfextion */
        if (!isset($reflectionHandler[$info['type']])) {
            self::$reflectionHandler[$info['type']] = new \ReflectionClass(
                self::$loggerHandler[$info['type']]['class']
            );
        }
        /* Prepare args */
        $args = array();
        foreach (self::$loggerHandler[$info['type']]['extraArgs'] as $argName) {
            if (false === isset($info[$argName])) {
                throw new Exception("The configuration value $argName for logger $loggerName is not set.");
            }
            $args[] = $info[$argName];
        }
        $args[] = constant('\Monolog\Logger::' . $info['level']);
        /* Callback for handler arguments if set */
        if (false === is_null(self::$loggerHandler[$info['type']]['callbackArgs'])) {
            // @Todo
        }
        return self::$reflectionHandler[$info['type']]->newInstanceArgs($args);
    }
}
