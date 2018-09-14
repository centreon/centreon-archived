<?php
/*
 * Copyright 2005-2018 Centreon
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

/**
 * This feature allows you to manage the sending of license files by module.
 */
$extensionFileLicense = '.license';
$licenseFileInfos = $_FILES['licensefile'];
$licenseModuleName = str_replace($extensionFileLicense, '', $licenseFileInfos['name']);
$moduleName = $_POST['module'];

if ($licenseModuleName !== $moduleName) {
    responseUploadJsonFormat("Your license is not valid for this product", 404);
} else {
    // Directory for put license files
    $licensePath = '/etc/centreon/license.d/';
    $destination = $licensePath . $licenseFileInfos['name'];

    if (move_uploaded_file($licenseFileInfos['tmp_name'], $destination)) {
        responseUploadJsonFormat("The license has been successfully uploaded");
    }

    if ($errorStatus) {
        responseUploadJsonFormat("An error occurred");
    }
}

/**
 * Function that allows to manage the sending of the data in JSON format
 * with an error code and an associated message.
 * @param message Untranslated message to be sent back
 * @param codeStatus HTTP error code
 */
function responseUploadJsonFormat($message, $codeStatus = 200)
{
    header('Content-Type: application/json');

    echo json_encode(
        array(
            "code" => $codeStatus,
            "message" => _($message)
        )
    );
    if ($codeStatus == 404) {
        http_response_code(404);
        exit;
    }
}
