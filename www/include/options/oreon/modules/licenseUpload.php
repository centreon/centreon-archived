<?php
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
require_once "/etc/centreon/centreon.conf.php";
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
            
            if ($zend_info['Admin '] < 1)
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
                    echo "Your license does not match any Zend ID of your machine";
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
