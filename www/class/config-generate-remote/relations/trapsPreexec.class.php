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

namespace ConfigGenerateRemote;

use \PDO;

class trapsPreexec extends AbstractObject
{
    private $use_cache = 1;
    private $done_cache = 0;

    private $trap_preexec_cache = array();

    protected $table = 'traps_preexec';
    protected $generate_filename = 'traps_preexec.infile';
    protected $stmt_trap = null;
    
    protected $attributes_write = array(
        'trap_id',
        'tpe_order',
        'tpe_string'
    );

    public function __construct(\Pimple\Container $dependencyInjector)
    {
        parent::__construct($dependencyInjector);
        $this->buildCache();
    }

    private function cacheTrapPreexec()
    {
        $stmt = $this->backend_instance->db->prepare("SELECT 
                    * 
                FROM traps_preexec
        ");

        $stmt->execute();
        $values = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($values as &$value) {
            if (!isset($this->trap_preexec_cache[$value['trap_id']])) {
                $this->trap_preexec_cache[$value['trap_id']] = array();
            }
            $this->trap_preexec_cache[$value['trap_id']][] = &$value;
        }
    }

    private function buildCache()
    {
        if ($this->done_cache == 1) {
            return 0;
        }

        $this->cacheTrapPreexec();
        $this->done_cache = 1;
    }

    public function generateObject($trap_id, $trap_preexec_cache) {
        foreach ($trap_preexec_cache as $value) {
            $this->generateObjectInFile($value);
        }
    }

    public function getTrapPreexecByTrapId($trap_id)
    {
        # Get from the cache
        if (isset($this->trap_preexec_cache[$trap_id])) {
            $this->generateObject($trap_id, $this->trap_preexec_cache[$trap_id]);
            return $this->trap_preexec_cache[$trap_id];
        } else if ($this->use_cache == 1) {
            return null;
        }

        # We get unitary
        if (is_null($this->stmt_trap)) {
            $this->stmt_trap = $this->backend_instance->db->prepare("SELECT 
                    *
                FROM traps_preexec
                WHERE trap_id = :trap_id 
                ");
        }

        $this->stmt_trap->bindParam(':trap_id', $trap_id, PDO::PARAM_INT);
        $this->stmt_trap->execute();
        $trap_preexec_cache = array();
        foreach ($this->stmt_trap->fetchAll(PDO::FETCH_ASSOC) as &$value) {
            $trap_preexec_cache[$value['traps_id']] = $value;
        }
        
        $this->generateObject($trap_id, $trap_preexec_cache[$trap_id]);        
        return $trap_preexec_cache;
    }
}
