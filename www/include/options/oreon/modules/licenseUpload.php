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

// License validator
function parse_zend_license_file($file)
{
    $lines = preg_split('/\n/', file_get_contents($file));
    $infos = array();
    foreach ($lines as $line)
    {
        if (preg_match('/^([^= ]+)\s*=\s*(.+)$/', $line, $match)) {
            $infos[$match[1]] = $match[2];
        }
    }
    return $infos;
}
    
// Load conf
require_once "@CENTREON_ETC@/centreon.conf.php";
require_once $centreon_path . '/www/autoloader.php';

$LicenseFileInfos = $_FILES['licensefile'];
$filename = str_replace('/', '', $_GET['module']);

if ($LicenseFileInfos['name'] == 'merethis_lic.zl')
{
    if (is_writable($centreon_path . "www/modules/" . $filename . "/license/"))
    {
        if (move_uploaded_file($_FILES["licensefile"]["tmp_name"], $centreon_path . "www/modules/" . $filename . "/license/merethis_lic_temp.zl"))
        {
            if (zend_loader_file_encoded($centreon_path . "www/modules/" . $filename . "/license/merethis_lic_temp.zl")) {
                $zend_info = zend_loader_file_licensed($centreon_path . "www/modules/" . $filename . "/license/merethis_lic_temp.zl");
            } else {
                $zend_info = parse_zend_license_file($centreon_path . "www/modules/" . $filename . "/license/merethis_lic_temp.zl");
            }
            
            $licenseMatchedProduct = false;
            $licenseMatchedZendID = false;
            $licenseHasAdmin = true;
            $licenseExpired = true;

            if ($zend_info['Product-Name'] == 'merethis_'.$filename) {
                $licenseMatchedProduct = true;
            }

            // Check ZendId
            $serverZendIds = zend_get_id();
            $licenseFileZendIds = explode(';', $zend_info['Zendid']);
            foreach($serverZendIds as $serverZendId)
            {
                if (in_array($serverZendId, $licenseFileZendIds)) {
                    $licenseMatchedZendID = true;
                    break;
                }
            }
            
            if ((isset($zend_info['Zendid']) && ($zend_info['Zendid'] == 'Not-Locked')) || (isset($zend_info['Hardware-Locked']) && ($zend_info['Hardware-Locked'] == 'No'))) {
                $licenseMatchedZendID = true;
            }

            $license_expires = strtotime($zend_info['Expires']);
            if ($license_expires > time()) {
                $licenseExpired = false;
            }
            
            if (isset($zend_info['Admin']) && $zend_info['Admin'] < 1 && ($filename == 'centreon-map-server')) {
                $licenseHasAdmin = false;
            }

            if ($licenseMatchedProduct && $licenseMatchedZendID && $licenseHasAdmin && ($licenseExpired == FALSE))
            {
                if(file_exists($centreon_path . "www/modules/" . $filename . "/license/merethis_lic.zl")) {
                    unlink($centreon_path . "www/modules/" . $filename . "/license/merethis_lic.zl");
                }

                rename($centreon_path . "www/modules/" . $filename . "/license/merethis_lic_temp.zl", $centreon_path . "www/modules/" . $filename . "/license/merethis_lic.zl");
                clearstatcache(true, $centreon_path . "www/modules/" . $filename . "/license/merethis_lic.zl");
                if (zend_loader_install_license($centreon_path . "www/modules/" . $filename . "/license/merethis_lic.zl", true)) {
                    echo _("The license has been sucessfully installed");
                } else {
                    echo _("An error occured");
                }
            }
            else
            {
                echo _("Sorry your license is not valid\n");
                if ($licenseMatchedProduct == false) {
                    echo _("Your license is not valid for this product");
                }
                
                if ($licenseMatchedZendID == false) {
                    echo _("Your license doesn't match any Zend ID of your machine");
                }
                
                if ($licenseHasAdmin == false) {
                    echo _("Your license doesn't include any Administrator");
                }
                
                if ($licenseExpired == true) {
                    echo _("Your license has expired");
                }
            }
        }
    } else {
        echo _("License upload has failed.\nDestination directory doesn't exist or your webserver's user don't have the right to access it");
    }
} else {
    echo _("The given license file is not valid");
}
