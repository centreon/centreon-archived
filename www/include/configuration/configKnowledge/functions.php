<?php
/**
 * CENTREON
 *
 * Source Copyright 2005-2015 CENTREON
 *
 * Unauthorized reproduction, copy and distribution
 * are not allowed.
 *
 * For more information : contact@centreon.com
 *
 */

function versionCentreon($pearDB) {
	if (is_null($pearDB)) {
		return;
	}

	$query = 'SELECT `value` FROM `informations` WHERE `key` = "version"';
	$res = $pearDB->query($query);
	if (PEAR::isError($res)) {
		return null;
	}
	$row = $res->fetchRow();
	return $row['value'];	
}

function Mediawikiconfigexist($url) {

        $file_headers = @get_headers($url);
        if($file_headers[0] == 'HTTP/1.1 404 Not Found')
            return false;

        return true;
}




