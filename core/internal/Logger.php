<?php
/*
 * Copyright 2005-2014 MERETHIS
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
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
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
        \Monolog\Registry::addLogger($logger);
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
