<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
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

namespace CentreonConfiguration\Internal\Poller;

use Centreon\Internal\Exception;

/**
 * @author Julien Mathis <jmathis@centreon.com>
 * @version 3.0.0
 */

class WriteConfigFile
{
    /**
     * 
     * @param string $filename
     * @return mixed
     * @throws Exception
     */
    public static function initFile($filename)
    {
        /* Remove configuration file if the file exists */
        if (file_exists($filename)) {
            if (!@unlink($filename)) {
                throw new Exception('"Cannot remove "' . $filename . '"');
            }
        }

        /* Keep in memory the old umask */
        //$oldumask = umask(0113);
        
        /* Open File */
        if (!is_dir(dirname($filename))) {
            mkdir(dirname($filename), 0775, true);
        }
        if (!$handle = fopen($filename, 'w')) {
            throw new Exception('Cannot open file "' . $filename . '"');
        }
        
        /* Set the umask to the previous mod */
        //umask($oldumask);
        
        return $handle;
    }

    /**
     * 
     * @param string $filename
     * @return string
     */
    public static function getFileType($filename)
    {
        if ("command.cfg" == substr($filename, -11)
                   || "periods.cfg" == substr($filename, -11)
                   || "connectors.cfg" == substr($filename, -14)) {
            return "cfg_file";
        } elseif ("centengine.cfg" == substr($filename, -14)) {
            return "main_file";
        } elseif ("resources.cfg" == substr($filename, -13)) {
            return "cfg_include";
        } else {
            return "cfg_dir";
        }
    }

    /**
     * 
     * @param array $content
     * @param string $filename
     * @param array $filesList
     * @param string $user
     */
    public static function writeObjectFile($content, $filename, & $filesList, $user = "Anonymous")
    {
        /* Init File */
        $handle = static::initFile($filename);
            
        /* Add file into the list of file to include into centengine.cfg */
        static::addFile($filesList, $filename, static::getFileType($filename));

        /* Write Data */
        static::addHeader($handle, $user);
            
        /* Add Content to the configuration file */
        static::addObjectsContent($handle, $content);
           
        /* Close file */
        static::closeFile($handle);
    }

    /**
     * 
     * @param array $content
     * @param string $filename
     * @param array $filesList
     * @param string $user
     */
    public static function writeParamsFile($content, $filename, & $filesList, $user = "Anonymous")
    {
        /* Check that the content is not empty */
        if ($content != "") {
            /* Init File */
            $handle = static::initFile($filename);
            
            /* Add file into the list of file to include into centengine.cfg */
            static::addFile($filesList, $filename, static::getFileType($filename));

            /* Write Data */
            static::addHeader($handle, $user);
            
            /* Add Content to the configuration file */
            static::addParamsContent($handle, $content);
            
            /* Close file */
            static::closeFile($handle);
        }/* else {
            print "Content is empty for file '$filename'. File will not be created.\n";
        }*/
    }

    /**
     * Add new generated file into the file list used in centengine.cfg
     * 
     * @param array $filesList
     * @param type $newFile
     * @param string $type
     */
    private static function addFile(& $filesList, $newFile, $type = "empty")
    {
        if (!isset($filesList[$type])) {
            $filesList[$type] = array();
        }
        if ($type == "cfg_dir") {
            preg_match('/\/([a-zA-Z0-9\_\-\.]*\.cfg)/', $newFile, $matches);
            if (isset($matches[1])) {
                $newFile = str_replace($matches[1], "", $newFile);
            }
        }

        if ($type == "cfg_dir") {
            $flag = 0;
            foreach ($filesList[$type] as $key => $value) {
                if ($value == $newFile) {
                    $flag = 1;
                }
            }
            if (!$flag) {
                $filesList[$type][] = $newFile;
            }
        } elseif ($type != "cfg_dir") {
            $filesList[$type][] = $newFile;
        }
        
    }
    
    /**
     * Add content to the file after the header
     * 
     * @param type $handle
     * @param array $content
     * @return array
     */
    private static function addObjectsContent($handle, $content = null)
    {
        if (!isset($content)) {
            /* Array is empty */
            return array("status" => false, "message" => "Empty content");
        }

        /* Read the array of data */
        if (is_array($content)) {
            foreach ($content as $object) {
                /* Get the object type */
                if (isset($object["type"])) {
                    static::startObject($handle, $object["type"]);
                    foreach ($object["content"] as $field => $value) {
                        static::addParameters($handle, $field, $value);
                    }
                    static::closeObject($handle);
                } else {
                    /* Array is not well formated */
                    return array("status" => false, "message" => "Content array is not well formated");
                }
            }
        } else {
            return array("status" => false, "message" => "Content is not an array");
        }

    }

    /**
     * Add content to the file after the header
     * 
     * @param type $handle
     * @param array $content
     * @return array
     */
    private static function addParamsContent($handle, $content = null)
    {
        if (!isset($content)) {
            /* Array is empty */
            return array("status" => false, "message" => "Empty content");
        }

        /* Read the array of data */
        if (is_array($content)) {
            foreach ($content as $field => $value) {
                if (is_array($value)) {
                    foreach ($value as $v) {
                        static::addGlobalParameters($handle, $field, $v);
                    }
                } else {
                    static::addGlobalParameters($handle, $field, $value);
                }
            }
        } else {
            return array("status" => false, "message" => "Content is not an array");
        }

    }

    /**
     * 
     * @param type $handle
     * @param string $field
     * @param string $value
     * @throws Exception
     */
    private static function addParameters($handle, $field, $value = "")
    {
        if (strcmp($field, "")) {
            /* Build parameter line */
            $content = "\t".$field."\t".$value."\n";
            /* Write data into the file */
            if (!fwrite($handle, $content)) {
                throw new Exception('Cannot write to file content ($content)');
            }
        } else {
            throw new Exception('Cannot write parameter with empty field');
        }
    }

    /**
     * 
     * @param type $handle
     * @param string $field
     * @param string $value
     * @throws Exception
     */
    private static function addGlobalParameters($handle, $field, $value = "")
    {
        if (strcmp($field, "")) {
            /* Build parameter line */
            $content = $field."=".$value."\n";
            /* Write data into the file */
            if (!fwrite($handle, $content)) {
                throw new Exception('Cannot write to file content ($content)');
            }
        } else {
            throw new Exception('Cannot write parameter with empty field');
        }
    }
    
    /**
     * 
     * @param type $handle
     * @param string $objectType
     * @throws Exception
     */
    private static function startObject($handle, $objectType)
    {
        /* Check if object typoe is empty */
        if ($objectType != "") {
            $content = "define ".$objectType." {\n";
            /* Write start object into the file */
            if (!fwrite($handle, $content)) {
                throw new Exception('Cannot write start object.');
            }
        } else {
            throw new Exception('Object type is empty');
        }
    }

    /**
     * 
     * @param type $handle
     * @throws Exception
     */
    private static function closeObject($handle)
    {
        /* Write end object into the file */
        if (!fwrite($handle, "}\n\n")) {
            throw new Exception('Cannot write end object.');
        }
    }

    /**
     * Add a header on the top of the configuration file
     * 
     * @param type $handle
     * @param string $name
     */
    private static function addHeader($handle, $name)
    {
        $time = date("F j, Y, g:i a");

        $by = $name;
        $str  = "###################################################################\n";
        /* Get line lenght */
        $len = strlen($str);

        $str .= "#                                                                 #\n";
        $str .= "#                     Generated by Centreon 3                     #\n";
        $str .= "#                                                                 #\n";
        $str .= "#      Developped by : Julien Mathis and Romain Le Merlus         #\n";
        $str .= "#                                                                 #\n";
        $str .= "#                        www.centreon.com                         #\n";
        $str .= "###################################################################\n";
        $str .= "#                                                                 #\n";
        $str .= "#         Last modification: " . $time;
        
        /* Add space to put text on center */
        for ($i = 0; $i != $len - 29 - strlen($time) - 2; $i++) {
            $str  .= " ";
        }
        
        $str .= "#\n";
        $str .= "#         By " . $by;
        $len_by = mb_strlen($by, 'UTF-8');

        /* Add space to put text on center */
        for ($i = 0; $i != $len - 13 - $len_by - 2; $i++) {
            $str  .= " ";
        }

        $str .= "#\n";
        $str .= "#                                                                 #\n";
        $str .= "###################################################################\n\n";
        fwrite($handle, $str);
    }

    /**
     * Close the file descriptor
     * 
     * @param type $handle
     */
    private static function closeFile($handle)
    {
        fclose($handle);
    }
}
