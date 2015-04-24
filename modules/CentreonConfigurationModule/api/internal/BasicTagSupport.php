<?php

/*
 * Copyright 2005-2015 CENTREON
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
 * As a special exception, the copyright holders of this program give CENTREON 
 * permission to link this program with independent modules to produce an executable, 
 * regardless of the license terms of these independent modules, and to copy and 
 * distribute the resulting executable under terms of CENTREON choice, provided that 
 * CENTREON also meet, for each linked independent module, the terms  and conditions 
 * of the license of that module. An independent module is a module which is not 
 * derived from this program. If you modify this program, you may extend this 
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 * 
 * For more information : contact@centreon.com
 * 
 */
namespace CentreonConfiguration\Api\Internal;

use Centreon\Api\Internal\BasicCrudCommand;
use CentreonAdministration\Repository\TagsRepository;

/**
 * 
 */
class BasicTagSupport extends BasicCrudCommand
{
    /**
     * 
     * @param string $object
     * @param string $tag
     */
    public function addTagAction($object, $tag)
    {
        try {
            $repository = $this->repository;
            $object = $repository::getIdFromUnicity($this->parseObjectParams($object));
            TagsRepository::add($tag, $this->objectName, $object, 3);
            \Centreon\Internal\Utils\CommandLine\InputOutput::display(
                "The tag has been successfully added to the object",
                true,
                'green'
            );
        } catch(\Exception $ex) {
            \Centreon\Internal\Utils\CommandLine\InputOutput::display($ex->getMessage(), true, 'red');
        }
    }
    
    /**
     * 
     * @param string $object
     */
    public function listTagAction($object = null)
    {
        try {
            $repository = $this->repository;
            $object = $repository::getIdFromUnicity($this->parseObjectParams($object));
            $TagList = TagsRepository::getList($this->objectName, $object, 1);
            foreach ($TagList as $tag) {
                echo $tag['text'] . "\n";
            }
        } catch (\Exception $ex) {
            \Centreon\Internal\Utils\CommandLine\InputOutput::display($ex->getMessage(), true, 'red');
        }
    }
    
    /**
     * 
     * @param string $object
     * @param string $tag
     */
    public function removeTagAction($object, $tag)
    {
        try {
            $repository = $this->repository;
            $object = $repository::getIdFromUnicity($this->parseObjectParams($object));
            TagsRepository::delete($tag, $this->objectName, $object);
            \Centreon\Internal\Utils\CommandLine\InputOutput::display(
                "The tag has been successfully removed from the object",
                true,
                'green'
            );
        } catch (\Exception $ex) {
            \Centreon\Internal\Utils\CommandLine\InputOutput::display($ex->getMessage(), true, 'red');
        }
    }
}
