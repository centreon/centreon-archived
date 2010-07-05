<?php
/*
 based on EasyArchive V0.2 by Alban LOPEZ
	from http://www.phpclasses.org/browse/package/4239.html
 modified for Centreon project by Nikolaus Filus

 EasyArchive is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.
 EasyArchive is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 See the GNU General Public License for more details on http://www.gnu.org/licenses/
*/

// uses extensions: zip, bzip2, ZipArchive
require_once("Archive/Tar.php");

class CentreonEasyArchive
{
	var $archiveExt = array (
		'zip'	=>'zip',
		'tar'	=>'tar',
		'gz'	=>'gz', 'tgzip' =>'gz','tgz' =>'gz',
		'bz'	=>'bz', 'tbzip'	=>'bz','tbz' =>'bz','bzip' =>'bz',
		'bz2'	=>'bz', 'tbzip2'=>'bz','tbz2'=>'bz','bzip2'=>'bz'
	);

	function extractZip($src, $dest)
	{
		$zip = new ZipArchive;
		if ($zip->open($src)===true) {
			$filelist = array();
			$success = $zip->extractTo($dest);
			if($success) {
			    for($i = 0; $i < $zip->numFiles; $i++) {
				$filelist[] = $zip->getNameIndex($i);
    			    }
			    $zip->close();
			    return $filelist;
			} else {
			    $zip->close();
			    return false;
			}
		}
		return false;
	}

	/* return:
	 * filelist[] on success, false on failure
	 */
	function extract ($data, $dest=false)
	{
		$path_parts = pathinfo ($data);
		if (!$dest)
			$dest = $path_parts['dirname'].'/';
		elseif (substr($dest, -1)!='/')
		    $dest .= '/';
		$ext = $path_parts['extension'];
		$name = $path_parts['filename'];
		if (!isset($this->archiveExt[$ext]))
			return false;

		if ($ext == 'zip')
			return $this->extractZip($data, $dest);
		else {
			$tar = new Archive_Tar($data);
			$tarlist = $tar->listContent();
			foreach ($tarlist as $tarelem)
			    $content[] = $tarelem["filename"];
			if (count($content) && $tar->extractList($content, $dest))
			    return $content;
			else
			    return false;
		}
	}

}
?>
