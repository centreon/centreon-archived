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

class Timeperiod extends AbstractObject {
    private $timeperiods = null;
    protected $generate_filename = 'timeperiods.cfg';
    protected $object_name = 'timeperiod';
    protected $attributes_select = '
        tp_id,
        tp_name as timeperiod_name,
        tp_alias as alias,
        tp_sunday as sunday,
        tp_monday as monday,
        tp_tuesday as tuesday,
        tp_wednesday as wednesday,
        tp_thursday as thursday,
        tp_friday as friday,
        tp_saturday as saturday
    ';
    protected $attributes_write = array(
        'name',
        'timeperiod_name',
        'alias',
        'sunday',
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
    );
    protected $attributes_array = array(
        'use',
        'exclude'
    );
    protected $attributes_hash = array(
        'exceptions'
    );
    protected $stmt_extend = array('include' => null, 'exclude' => null);
    
    public function getTimeperiods() {        
        $stmt = $this->backend_instance->db->prepare("SELECT 
              $this->attributes_select
            FROM timeperiod
            ");
        $stmt->execute();
        $this->timeperiods = $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC);
    }
    
    protected function getTimeperiodExceptionFromId($timeperiod_id) {
        if (isset($this->timeperiods[$timeperiod_id]['exceptions'])) {
            return 1;
        }

        $stmt = $this->backend_instance->db->prepare("SELECT 
              days, timerange
            FROM timeperiod_exceptions
            WHERE timeperiod_id = :timeperiod_id
        ");
        $stmt->bindParam(':timeperiod_id', $timeperiod_id, PDO::PARAM_INT);
        $stmt->execute();
        $exceptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->timeperiods[$timeperiod_id]['exceptions'] = array();
        foreach ($exceptions as $exception) {
            $this->timeperiods[$timeperiod_id]['exceptions'][$exception['days']] = $exception['timerange'];
        }
    }
    
    protected function getTimeperiodExtendFromId($timeperiod_id, $db_label, $label) {
        if (!isset($this->timeperiods[$timeperiod_id][$label . '_cache'])) {
            if (is_null($this->stmt_extend[$db_label])) {
                $this->stmt_extend[$db_label] = $this->backend_instance->db->prepare("SELECT 
                    timeperiod_" . $db_label . "_id as period_id
                    FROM timeperiod_" . $db_label . "_relations
                    WHERE timeperiod_id = :timeperiod_id
                ");
            }
            $this->stmt_extend[$db_label]->bindParam(':timeperiod_id', $timeperiod_id, PDO::PARAM_INT);
            $this->stmt_extend[$db_label]->execute();
            $this->timeperiods[$timeperiod_id][$label . '_cache'] = $this->stmt_extend[$db_label]->fetchAll(PDO::FETCH_COLUMN);
        }
        
        $this->timeperiods[$timeperiod_id][$label] = array();
        foreach ($this->timeperiods[$timeperiod_id][$label . '_cache'] as $period_id) {
            $this->timeperiods[$timeperiod_id][$label][] = $this->generateFromTimeperiodId($period_id);
        }
    }
    
    public function generateFromTimeperiodId($timeperiod_id) {        
        if (is_null($timeperiod_id)) {
            return null;
        }
        if (is_null($this->timeperiods)) {
            $this->getTimeperiods();
        }
        
        if (!isset($this->timeperiods[$timeperiod_id])) {
            return null;
        }
        if ($this->checkGenerate($timeperiod_id)) {
            return $this->timeperiods[$timeperiod_id]['timeperiod_name'];
        }
        
        $this->timeperiods[$timeperiod_id]['name'] = $this->timeperiods[$timeperiod_id]['timeperiod_name'];
        $this->getTimeperiodExceptionFromId($timeperiod_id);
        $this->getTimeperiodExtendFromId($timeperiod_id, 'exclude', 'exclude');
        $this->getTimeperiodExtendFromId($timeperiod_id, 'include', 'use');
        
        $this->generateObjectInFile($this->timeperiods[$timeperiod_id], $timeperiod_id);
        return $this->timeperiods[$timeperiod_id]['timeperiod_name'];
    }
}
