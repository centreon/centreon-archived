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
 * Class EventDispatcher
 *
 * @see EventHandler
 * @package Centreon\Domain\Entity
 */
class EventDispatcher
{
    /**
     * Event types
     */
    public const EVENT_ADD     = 1;
    public const EVENT_UPDATE  = 2;
    public const EVENT_DELETE  = 4;
    public const EVENT_READ    = 8;
    public const EVENT_DISPLAY = 16;
    public const EVENT_DUPLICATE = 32;
    public const EVENT_SYNCHRONIZE = 64;

    /**
     * @var array List a values returned by callable function defined in the
     * event handler. Their values are partitioned by context name.
     */
    private $executionContext = [];

    /**
     * @var []
     */
    private $eventHandlers;

    /**
     * @var array Sorted list of methods that will be called in the event handler.
     */
    private $eventMethods = ['preProcessing', 'processing', 'postProcessing'];

    /**
     * @var DispatcherLoaderInterface
     */
    private $dispatcherLoader;

    /**
     * @return DispatcherLoaderInterface
     */
    public function getDispatcherLoader(): ?DispatcherLoaderInterface
    {
        return $this->dispatcherLoader;
    }

    /**
     * @param DispatcherLoaderInterface $dispatcherLoader Loader that will be
     * used to include PHP files in which we add event handlers.
     */
    public function setDispatcherLoader(DispatcherLoaderInterface $dispatcherLoader): void
    {
        $this->dispatcherLoader = $dispatcherLoader;
    }

    /**
     * Add a new event handler which will be called by the method 'notify'
     *
     * @see EventDispatcher::notify()
     * @param string $context Name of the context in which we add the event handler
     * @param int $eventType Event type
     * @param EventHandler $eventHandler Event handler to add
     */
    public function addEventHandler(string $context, int $eventType, EventHandler $eventHandler): void
    {
        foreach ($this->eventMethods as $eventMethod) {
            $methodName = 'get' . ucfirst($eventMethod);
            foreach (call_user_func(array($eventHandler, $methodName)) as $priority => $callables) {
                $this->eventHandlers[$context][$eventType][$eventMethod][$priority] = array_merge(
                    $this->eventHandlers[$context][$eventType][$eventMethod][$priority] ?? [],
                    $callables
                );
            }
        }
    }

    /**
     * Notify all event handlers for a specific context and type of event.
     *
     * @param string $context Name of the context in which we will call all the
     * registered event handlers.
     * @param int $eventType Event type. Only event handlers registered for this event will be called.
     * We can add several types of events using the binary operator '|'
     * @param array $arguments Array of arguments that will be passed to callable
     * functions defined in event handlers
     */
    public function notify(string $context, int $eventType, $arguments = []): void
    {
        $sortedCallables = $this->getSortedCallables($context, $eventType);

        /*
         * Pay attention,
         * The order of this loop is important because we have to call the
         * callable functions in this precise order
         * "pre-processing", "processing" and "post-processing".
         */
        foreach ($this->eventMethods as $eventMethod) {
            if (isset($sortedCallables[$eventMethod])) {
                /*
                 * We will call all the callable functions defined in order of priority
                 * (from the lowest priority to the highest)
                 * All results returned by the callable functions will be saved
                 * in the execution context and isolated by the context name.
                 */
                foreach ($sortedCallables[$eventMethod] as $priority => $callables) {
                    foreach ($callables as $callable) {
                        $currentExecutionContext =
                            $this->executionContext[$context][$eventType] ?? [];
                        $result = call_user_func_array(
                            $callable,
                            array($arguments, $currentExecutionContext, $eventType)
                        );
                        if (isset($result)) {
                            $this->executionContext[$context][$eventType] =
                                array_merge(
                                    $this->executionContext[$context][$eventType] ?? [],
                                    $result
                                );
                        }
                    }
                }
            }
        }
    }

    /**
     * Retrieve all callable functions sorted by method and priority for a
     * specific context and event type.
     *
     * @param string $context Name of the context.
     * @param int $eventType Event type.
     * @return array List of partitioned event handlers by processing method
     * ('preProcessing', 'processing', 'postProcessing') and processing priority
     */
    private function getSortedCallables(string $context, int $eventType): array
    {
        $sortedCallables = [];
        if (isset($this->eventHandlers[$context])) {
            foreach ($this->eventHandlers[$context] as $contextEventType => $callablesSortedByMethod) {
                if ($contextEventType & $eventType) {
                    foreach ($callablesSortedByMethod as $method => $callablesSortedByPriority) {
                        foreach ($callablesSortedByPriority as $priority => $callables) {
                            $sortedCallables[$method][$priority] = array_merge_recursive(
                                $sortedCallables[$method][$priority] ?? [],
                                $callables
                            );
                            /*
                             * It is important to sort from the lowest priority
                             * to the highest because the callable function with
                             * the lowest priority will be called first.
                             */
                            ksort($sortedCallables[$method]);
                        }
                    }
                }
            }
        }
        return $sortedCallables;
    }
}
