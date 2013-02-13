<?php
/*
 * Copyright 2005-2011 MERETHIS
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
 * SVN : $URL: http://svn.centreon.com/trunk/centreon/www/include/options/oreon/modules/licenseUpload.php $
 * 
 */

// License validator
function parse_zend_license_file($file)
{
    $lines = preg_split('/\n/', file_get_contents($file));
    $infos = array();
    foreach ($lines as $line)
    {
        if (preg_match('/^([^= ]+)\s*=\s*(.+)$/', $line, $match))
            $infos[$match[1]] = $match[2];
    }
    return $infos;
}
    
// Load conf
//ini_set('display_errors', '1');
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
            if (zend_loader_file_encoded($centreon_path . "www/modules/" . $filename . "/license/merethis_lic_temp.zl"))
                $zend_info = zend_loader_file_licensed($centreon_path . "www/modules/" . $filename . "/license/merethis_lic_temp.zl");
            else
                $zend_info = parse_zend_license_file($centreon_path . "www/modules/" . $filename . "/license/merethis_lic_temp.zl");

            $licenseMatchedProduct = false;
            $licenseMatchedZendID = false;
            $licenseHasAdmin = true;
            $licenseExpired = true;

            if ($zend_info['Product-Name'] == 'merethis_'.$filename)
                $licenseMatchedProduct = true;

            $ZendIds = zend_get_id();
            foreach($ZendIds as $zendId)
            {
                if ($zendId == $zend_info['Zendid'])
                    $licenseMatchedZendID = true;
            }
            if ((isset($zend_info['Zendid']) && ($zend_info['Zendid'] == 'Not-Locked')) || (isset($zend_info['Hardware-Locked']) && ($zend_info['Hardware-Locked'] == 'No')))
                $licenseMatchedZendID = true;

            $license_expires = strtotime($zend_info['Expires']);
            if ($license_expires > time())
                $licenseExpired = false;
            
            if (isset($zend_info['Admin']) && $zend_info['Admin'] < 1 && ($filename == 'centreon-map-server'))
                $licenseHasAdmin = false;

            if ($licenseMatchedProduct && $licenseMatchedZendID && $licenseHasAdmin && ($licenseExpired == FALSE))
            {
                if(file_exists($centreon_path . "www/modules/" . $filename . "/license/merethis_lic.zl"))
                    unlink($centreon_path . "www/modules/" . $filename . "/license/merethis_lic.zl");

                rename($centreon_path . "www/modules/" . $filename . "/license/merethis_lic_temp.zl", $centreon_path . "www/modules/" . $filename . "/license/merethis_lic.zl");
                zend_loader_install_license($centreon_path . "www/modules/" . $filename . "/license/merethis_lic.zl", true);
                echo 'License sucessfully installed';
            }
            else
            {
                echo "Sorry your license is not valid\n";
                if ($licenseMatchedProduct == false)
                    echo "Your license is not valid for this product";
                if ($licenseMatchedZendID == false)
                    echo "Your license don't match any Zend ID of your machine";
                if ($licenseHasAdmin == false)
                    echo "Your license don't include any Administrator";
                if ($licenseExpired == true)
                    echo "Your license has expired";
            }
        }
    }
    else
        echo "License upload has failed.\n"
            ."Destination directory doesn't exist or your webserver's user don't have the right to access it";
}
else
    echo 'License file not valid';



?>
