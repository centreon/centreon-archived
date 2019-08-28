<?php
/*
 * Copyright 2005 - 2019 Centreon (https://www.centreon.com/)
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
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

namespace ConfigGenerateRemote;

use \Exception;

abstract class AbstractObject
{
    protected $backend_instance = null;
    protected $generate_filename = null;
    protected $table = null;
    protected $exported = [];
    protected $fp = null;
    protected $type = 'infile';
    protected $subdir = 'configuration';

    protected $attributes_write = [];
    protected $attributes_array = [];

    protected $engine = true;
    protected $broker = false;
    protected $dependencyInjector;

    protected $fieldSeparatorInfile = null;
    protected $lineSeparatorInfile = null;

    public static function getInstance(\Pimple\Container $dependencyInjector)
    {
        static $instances = [];
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

    public function __destruct()
    {
        $this->close_file();
    }

    public function close_file()
    {
        if (!is_null($this->fp)) {
            fclose($this->fp);
        }
        $this->fp = null;
    }

    public function reset($createfile = false)
    {
        $this->close_file();
        $this->exported = [];
        if ($createfile == true) {
            $this->createFile($this->backend_instance->getPath());
        }
    }

    protected function createFile($dir)
    {
        $fullFile = $dir . '/' . $this->subdir . '/' . $this->generate_filename;
        if (!($this->fp = @fopen($fullFile, 'w+'))) {
            throw new Exception("Cannot open file (writing permission) '" . $fullFile . "'");
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
                $line .= $append . 'NULL';
            }
            $append = $this->fieldSeparatorInfile;
        }

        fwrite($this->fp, $line . $this->lineSeparatorInfile);
    }

    protected function generateObjectInFile($object, $id = null)
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

        return [];
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
