<?php

abstract class AbstractObjectXML {
    protected $backend_instance = null;
    protected $generate_subpath = 'nagios';
    protected $generate_filename = null;
    protected $rootXML = 'centreonBroker';
    protected $exported = array();
    protected $fp = null;
    
    protected $attributes_write = array();
    protected $attributes_array = array();
    protected $attributes_hash = array();
    protected $attributes_default = array();

    public static function getInstance() {
        static $instances = array();

        $calledClass = get_called_class();

        if (!isset($instances[$calledClass])) {
            $instances[$calledClass] = new $calledClass();
        }

        return $instances[$calledClass];
    }
    
    protected function __construct() {
        $this->backend_instance = Backend::getInstance();

        $this->writer = new XMLWriter();
        $this->writer->openMemory();
        $this->writer->setIndent(true);
        $this->writer->startDocument('1.0', 'UTF-8');
    }

    public function reset() {
        $this->exported = array();
    }
    
    protected function writeFile($dir) {
        $full_file = $dir . '/' . $this->generate_filename;
        $this->writer->endDocument();
        $content = $this->writer->outputMemory(true);
        if ($handle = fopen($full_file, 'w')) {
            if (strcmp($content, "") && !fwrite($handle, $content)) {
                throw new RuntimeException('Cannot write to file "' . $full_file . '"');
            }
        } else {
            throw new Exception("Cannot open file " . $full_file);
        }
    }
    
    protected function generateFile($object, $cdata = true, $root = null) {
        if (!is_null($root)) {
            $this->writer->startElement($root);
        }
        foreach ($object as $key => $value) {
            if (is_string($key) && $key == '@attributes') {
                foreach ($value as $subkey => $subvalue) {
                    $this->writer->writeAttribute($subkey, $subvalue);
                }
            } else if (!is_numeric($key) && is_array($value)) {
                $this->writer->startElement($key);
                $this->generateFile($value);
                $this->writer->endElement();
            } else if (is_array($value)) {
                $this->generateFile($value);
            } else {
                $this->writeElement($key, $value, $cdata);
            }
        }
        if (!is_null($root)) {
            $this->writer->endElement();
        }
    }

    protected function writeElement($key, $value, $cdata) {
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

    protected function cleanStr($str) {
        $str = preg_replace('/[\x00-\x09\x0B-\x0C\x0E-\x1F\x0D]/', "", $str);
        return $str;
    }
}

?>
