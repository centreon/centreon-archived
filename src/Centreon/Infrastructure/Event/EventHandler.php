<?php
/*
 * Copyright 2005-2019 Centreon
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
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
 *
 */

namespace Centreon\Infrastructure\Event;

/**
 * This class is for use with the EventDispatcher class.
 *
 * @see EventDispatcher
 * @package Centreon\Domain\Entity
 */
class EventHandler
{
    /**
     * @var array List of callable functions ordered by priority. They will be
     * loaded before the callable functions defined in the list named 'processing'.
     */
    private $preProcessing = [];
    /**
     * @var array List of callable functions ordered by priority. They will be
     * loaded before the callable functions defined in the list named
     * 'postProcessing' and after callable functions defined in the list named
     * 'preProcessing'.
     */
    private $processing = [];
    /**
     * @var array List of callable functions ordered by priority. They will be
     * loaded after the callable functions defined in the list named 'processing'.
     */
    private $postProcessing = [];

    /**
     * @see EventHandler::$preProcessing
     * @return array List of callable functions ordered by priority.
     */
    public function getPreProcessing(): array
    {
        return $this->preProcessing;
    }

    /**
     * @param callable $preProcessing Callable function
     * @param int $priority Execution priority of the callable function
     */
    public function setPreProcessing(callable $preProcessing, int $priority = 20): void
    {
        $this->preProcessing[$priority][] = $preProcessing;
    }

    /**
     * @see EventHandler::$processing
     * @return array List of callable functions ordered by priority.
     */
    public function getProcessing(): array
    {
        return $this->processing;
    }

    /**
     * @param callable $processing Callable function.
     * <code>
     * <?php>
     * $eventHandler = new EventHandler();
     * $eventHandler->setProcessing(
     *     function(int $eventType, array $arguments, array $executionContext) {...},
     *     20
     * );
     * ?>
     * <code>
     * @param int $priority Execution priority of the callable function
     */
    public function setProcessing(callable $processing, int $priority = 20): void
    {
        $this->processing[$priority][] = $processing;
    }

    /**
     * @see EventHandler::$postProcessing
     * @return array List of callable functions ordered by priority.
     */
    public function getPostProcessing(): array
    {
        return $this->postProcessing;
    }

    /**
     * @param callable $postProcessing Callable function
     * @param int $priority Execution priority of the callable function
     */
    public function setPostProcessing(callable $postProcessing, int $priority = 20): void
    {
        $this->postProcessing[$priority][] = $postProcessing;
    }
}
