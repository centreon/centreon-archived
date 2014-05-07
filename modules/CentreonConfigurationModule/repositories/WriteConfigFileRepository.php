<?php
/*
 * Copyright 2005-2014 MERETHIS
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
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

namespace  CentreonConfiguration\Repository;

/**
 * Factory for 
 *
 * @author Julien Mathis <jmathis@merethis.com>
 * @version 3.0.0
 */

class WriteConfigFileRepository
{
    public static function initFile($filename) {
        /* Remove configuration file if the file exists */
        if (file_exists($filename)) {
            if (!unlink($filename)) {
                return array("status" => false, "message" => "Can't remove $filname.");
            }
        } 

        /* Keep in memory the old umask */
        $oldumask = umask(0113);
        
        /* Open File */
        if (!$handle = fopen($filename, 'w')) {
            throw new RuntimeException('Cannot open file "' . $filename . '"');
        }
        
        /* Set the umask to the previous mod */
        umask($oldumask);
        
        return $handle;
    }

    public static function getFileType($filename) 
    {
        if ("resources.cfg" == substr($filename, -13)) {
            return "resource_file";
        } else if ("command.cfg" == substr($filename, -11)
                   || "periods.cfg" == substr($filename, -11)
                   || "connectors.cfg" == substr($filename, -14)) {
            return "cfg_file";
        } else if ("centengine.cfg" == substr($filename, -14)) {
            return "main_file";
        } else {
            return "cfg_dir";
        }
    }

    /**
     * 
     * 
     */
    public static function writeObjectFile($content, $filename, & $filesList, $user = "Anonymous") 
    {
        /* Check that the content is not empty */
        if (count($content)) {            
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
        } else {
            print "Content is empty for file '$filename'. File will not be created.\n";
        }
    }

    /**
     * 
     * 
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
        } else {
            print "Content is empty for file '$filename'. File will not be created.\n";
        }
    }

    /** 
     * Add new generated file into the file list used in centengine.cfg
     * @param 
     *
     */
    private static function addFile(& $filesList, $newFile, $type = "empty")
    {
        if (!isset($filesList[$type])) {
            $filesList[$type] = array();
        }
        $filesList[$type][] = $newFile;
    }
    
    /**
     * Add content to the file after the header
     * @param
     * @param 
     * @return void
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
     * @return void
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

    private static function addParameters($handle, $field, $value = "")
    {
        if (strcmp($field, "")) {
            /* Build parameter line */
            $content = "\t".$field."\t".$value."\n";
            /* Write data into the file */
            if (!fwrite($handle, $content)) {
                throw new RuntimeException('Cannot write to file content ($content)');
            }
        } else {
            throw new RuntimeException('Cannot write parameter with empty field');
        }
    }

    private static function addGlobalParameters($handle, $field, $value = "")
    {
        if (strcmp($field, "")) {
            /* Build parameter line */
            $content = $field."=".$value."\n";
            /* Write data into the file */
            if (!fwrite($handle, $content)) {
                throw new RuntimeException('Cannot write to file content ($content)');
            }
        } else {
            throw new RuntimeException('Cannot write parameter with empty field');
        }
    }
    
    private static function startObject($handle, $objectType) 
    {
        /* Check if object typoe is empty */
        if ($objectType != "") {
            $content = "define ".$objectType."{\n";
            /* Write start object into the file */
            if (!fwrite($handle, $content)) {
                throw new RuntimeException('Cannot write start object.');
            }
        } else {
            throw new RuntimeException('Object type is empty');
        }
    }

    private static function closeObject($handle) 
    {
        /* Write end object into the file */
        if (!fwrite($handle, "}\n\n")) {
            throw new RuntimeException('Cannot write end object.');
        }
    }
    

    /**
     * Add a header on the top of the configuration file
     * @param $handle 
     * @var $name
     * @return value
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

    /*
     * Close the file descriptor
     * @var handler
     * @return void
     */
    private static function closeFile($handle)
    {
        fclose($handle);
    }
}



