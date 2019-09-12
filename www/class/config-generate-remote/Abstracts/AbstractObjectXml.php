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

namespace ConfigGenerateRemote\Abstracts;

use \Exception;
use ConfigGenerateRemote\Backend;

abstract class AbstractObjectXML
{
    protected $backendInstance = null;
    protected $generate_subpath = 'nagios';
    protected $generateFilename = null;
    protected $rootXML = 'centreonBroker';
    protected $exported = [];
    protected $fp = null;

    protected $attributesWrite = [];
    protected $attributesArray = [];
    protected $attributesHash = [];
    protected $attributesDefault = [];
    protected $dependencyInjector;

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
        $this->backendInstance = Backend::getInstance($this->dependencyInjector);

        $this->writer = new XMLWriter();
        $this->writer->openMemory();
        $this->writer->setIndent(true);
        $this->writer->startDocument('1.0', 'UTF-8');
    }

    public function reset()
    {
        $this->exported = [];
    }

    protected function writeFile($dir)
    {
        $fullFile = $dir . '/' . $this->generateFilename;
        $this->writer->endDocument();
        $content = $this->writer->outputMemory(true);
        if ($handle = fopen($fullFile, 'w')) {
            if (strcmp($content, "") && !fwrite($handle, $content)) {
                throw new RuntimeException('Cannot write to file "' . $fullFile . '"');
            }
        } else {
            throw new Exception("Cannot open file " . $fullFile);
        }
    }

    protected function generateFile($object, $cdata = true, $root = null)
    {
        if (!is_null($root)) {
            $this->writer->startElement($root);
        }
        foreach ($object as $key => $value) {
            if (is_string($key) && $key == '@attributes') {
                foreach ($value as $subkey => $subvalue) {
                    $this->writer->writeAttribute($subkey, $subvalue);
                }
            } elseif (!is_numeric($key) && is_array($value)) {
                $this->writer->startElement($key);
                $this->generateFile($value);
                $this->writer->endElement();
            } elseif (is_array($value)) {
                $this->generateFile($value);
            } else {
                $this->writeElement($key, $value, $cdata);
            }
        }
        if (!is_null($root)) {
            $this->writer->endElement();
        }
    }

    protected function writeElement($key, $value, $cdata)
    {
        $this->writer->startElement($key);
        $value = $this->cleanStr($value);
        $value = html_entity_decode($value);
        if ($cdata) {
            $this->writer->writeCData($value);
        } else {
            $this->writer->text($value);
        }
        $this->writer->endElement();
    }

    protected function cleanStr($str)
    {
        $str = preg_replace('/[\x00-\x09\x0B-\x0C\x0E-\x1F\x0D]/', "", $str);
        return $str;
    }
}
