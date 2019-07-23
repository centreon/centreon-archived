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

abstract class AbstractObject
{
    protected $backend_instance = null;
    protected $generate_filename = null;
    protected $table = null;
    protected $exported = array();
    protected $fp = null;
    protected $type = 'infile';
    protected $subdir = 'configuration';

    protected $attributes_write = array();
    protected $attributes_array = array();

    protected $engine = true;
    protected $broker = false;
    protected $dependencyInjector;

    protected $fieldSeparatorInfile = null;
    protected $lineSeparatorInfile = null;

    public static function getInstance(\Pimple\Container $dependencyInjector)
    {
        static $instances = array();
        $calledClass = get_called_class();

        if (!isset($instances[$calledClass])) {
            $instances[$calledClass] = new $calledClass($dependencyInjector);
        }

        return $instances[$calledClass];
    }

    protected function __construct(\Pimple\Container $dependencyInjector)
    {
        $this->dependencyInjector = $dependencyInjector;
        $this->backend_instance = Backend::getInstance($this->dependencyInjector);
        $this->fieldSeparatorInfile = $this->backend_instance->getFieldSeparatorInfile();
        $this->lineSeparatorInfile = $this->backend_instance->getLineSeparatorInfile();
    }

    public function __destruct() {
        $this->close_file();
    }

    public function close_file()
    {
        if (!is_null($this->fp)) {
            fclose($this->fp);
        }
        $this->fp = null;
    }

    public function reset()
    {
        $this->close_file();
        $this->exported = array();
        $this->createFile($this->backend_instance->getPath());
    }

    protected function createFile($dir)
    {
        $full_file = $dir . '/' . $this->subdir . '/' . $this->generate_filename;
        if (!($this->fp = @fopen($full_file, 'w+'))) {
            throw new Exception("Cannot open file (writing permission) '" . $full_file . "'");
        }

        if ($this->type == 'infile') {
            Manifest::getInstance($this->dependencyInjector)->addFile(
                $this->generate_filename,
                $this->type,
                $this->table,
                $this->attributes_write
            );
        }
    }

    private function toUTF8($str)
    {
        $finalString = $str;
        if (mb_detect_encoding($finalString, 'UTF-8', true) !== 'UTF-8') {
            $finalString = mb_convert_encoding($finalString, 'UTF-8');
        }
        return $finalString;
    }

    protected function writeObject($object)
    {
        $line = '';
        $append = '';
        for ($i = 0; $i < count($this->attributes_write); $i++) {
            if (isset($object[$this->attributes_write[$i]]) && strlen($object[$this->attributes_write[$i]])) {
                $line .= $append . $object[$this->attributes_write[$i]];
            } else {
                $line .= $append . '\N';
            }
            $append = $this->fieldSeparatorInfile;
        }
    
        fwrite($this->fp, $line . $this->lineSeparatorInfile);
    }

    protected function generateObjectInFile($object, $id=null)
    {
        if (is_null($this->fp)) {
            $this->createFile($this->backend_instance->getPath());
        }
        $this->writeObject($object);
        if (!is_null($id)) {
            $this->exported[$id] = 1;
        }
    }

    private function writeNoObject($object)
    {
        foreach ($this->attributes_array as &$attr) {
            if (isset($object[$attr]) && !is_null($object[$attr]) && is_array($object[$attr])) {
                foreach ($object[$attr] as $v) {
                    fwrite($this->fp, $this->toUTF8($attr . "=" . $v . "\n"));
                }
            }
        }

        foreach ($this->attributes_hash as &$attr) {
            if (!isset($object[$attr])) {
                continue;
            }
            foreach ($object[$attr] as $key => &$value) {
                fwrite($this->fp, $this->toUTF8($key . "=" . $value . "\n"));
            }
        }

        foreach ($this->attributes_write as &$attr) {
            if (isset($object[$attr]) && !is_null($object[$attr]) && $object[$attr] != '') {
                fwrite($this->fp, $this->toUTF8($attr . "=" . $object[$attr] . "\n"));
            }
        }

        foreach ($this->attributes_default as &$attr) {
            if (isset($object[$attr]) && !is_null($object[$attr]) && $object[$attr] != 2) {
                fwrite($this->fp, $this->toUTF8($attr . "=" . $object[$attr] . "\n"));
            }
        }
    }

    protected function generateFile($object)
    {
        if (is_null($this->fp)) {
            $this->createFile($this->backend_instance->getPath());
        }

        $this->writeNoObject($object);
    }

    public function checkGenerate($id)
    {
        if (isset($this->exported[$id])) {
            return 1;
        }
        return 0;
    }

    public function getExported()
    {
        if (isset($this->exported)) {
            return $this->exported;
        }
        return array();
    }

    public function isEngineObject()
    {
        return $this->engine;
    }

    public function isBrokerObject()
    {
        return $this->broker;
    }
}
