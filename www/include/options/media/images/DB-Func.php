<?php
/*
 * Copyright 2005-2010 MERETHIS
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
 * SVN : $URL$
 * SVN : $Id$
 *
 */

	function sanitizeFilename($filename) {
		$cleanstr = htmlentities($filename, ENT_QUOTES, "UTF-8");
		$cleanstr = str_replace(" ", "_", $cleanstr);
		$cleanstr = str_replace("/", "_", $cleanstr);
		$cleanstr = str_replace("\\", "_", $cleanstr);
		return $cleanstr;
	}

	function sanitizePath($path) {
		$cleanstr = htmlentities($path, ENT_QUOTES, "UTF-8");
		$cleanstr = str_replace("/", "_", $cleanstr);
		$cleanstr = str_replace("\\", "_", $cleanstr);
		return $cleanstr;
	}


	function extractDir($zipfile, $path) {
		if (file_exists($zipfile)) {
		    $files = array();
		    $zip = new ZipArchive;
		    if ($zip->open($zipfile) === TRUE) {
    			if ($zip->extractTo($path) === TRUE)
    			    return TRUE;
    			else
    			    return FALSE;
    			$zip->close();
		    } else
		      return FALSE;
		} else
		    return FALSE;
	}


	function isValidImage($filename) {
		if (!$filename) {
			return false;
		$imginfo = getimagesize($filename);
		if ($imginfo) {
			return true;
		} else {
			$gd_res = imagecreatefromgd2($filename);
			if ($gd_res) {
				imagedestroy($gd_res);
				return true;
			}
		}
		return false;
	}
	
	
	function handleUpload($HTMLfile, $dir_alias, $img_comment = "") {
		if (!$HTMLfile || !$dir_alias) {
			return false;
		}
		$fileinfo = $HTMLfile->getValue();
		if (!isset($fileinfo["name"]) | !isset($fileinfo["type"])) {
			return false;
		}

		$uploaddir = "../filesUpload/images/";

                switch ($fileinfo["type"]) {
			// known archive types
                        case "application/zip" :
                        case "application/x-tar" :
                        case "application/x-gzip" :
                        case "application/x-bzip" :
                        case "application/x-zip-compressed" :
			    $HTMLfile->moveUploadedFile($uploaddir);
			    $arc = new CentreonEasyArchive();
			    $filelist = $arc->extract($uploaddir.$fileinfo["name"]);
			    if ($filelist!==false) {
				foreach ($filelist as $file) {
				    if (is_dir($uploaddir.$file))
					continue; // skip directories in list
				    if (!isValidImage($uploaddir.$file))
				    	continue;
				    $img_ids[] = insertImg($uploaddir, $file, $dir_alias, $file, $img_comment);
				}
				unlink($uploaddir.$fileinfo["name"]);
				return $img_ids;
			    }
			    return false;
			    break;
                        default :
			    if (isValidImage($uploaddir.$fileinfo["name"]) ) {
				$HTMLfile->moveUploadedFile($uploaddir);
				return insertImg($uploaddir, $fileinfo["name"], $dir_alias, $fileinfo["name"], $img_comment);
			    } else {
				return false;
			    }
                }
	}

	function insertImg ($src_dir, $src_file, $dst_dir, $dst_file, $img_comment = "") {
		global $pearDB;
		$mediadir = "./img/media/";

		if (!($dir_id = testDirectoryExistence($dst_dir)))
			$dir_id = insertDirectory($dst_dir);

		$dst_file = sanitizeFilename($dst_file);
		$dst  = $mediadir.$dst_dir."/".$dst_file;
		if (is_file($dst))
			return false; // file exists
		if (!rename($src_dir.$src_file, $dst))
			return false; // access denied, path error

		$img_parts = explode(".", $dst_file);
		$img_name = $img_parts[0];
		$rq = "INSERT INTO view_img ";
		$rq .= "(img_name, img_path, img_comment) ";
		$rq .= "VALUES ";
		$rq .= "('".htmlentities($img_name, ENT_QUOTES)."', '".htmlentities($dst_file, ENT_QUOTES)."', '".htmlentities($img_comment, ENT_QUOTES)."')";
		$pearDB->query($rq);
		$res =& $pearDB->query("SELECT MAX(img_id) FROM view_img");
		$img_id =& $res->fetchRow();
		$img_id = $img_id["MAX(img_id)"];
		$res =& $pearDB->query("INSERT INTO view_img_dir_relation (dir_dir_parent_id, img_img_id) VALUES ('".$dir_id."', '".$img_id."')");
//		$res->free();

		return ($img_id);
	}

	function deleteMultImg ($images = array()) {
		foreach($images as $selector => $val) {
			$id = explode('-',$selector);
			if (count($id)!=2)
				continue;
			deleteImg($id[1]);
		}
	}

	function deleteImg ($img_id) {
		if (!isset($img_id))
			return;
		
		global $pearDB;
		
		$mediadir = "./img/media/";

		$rq = "SELECT dir_alias, img_path FROM view_img, view_img_dir, view_img_dir_relation ";
		$rq .= " WHERE img_id = '".$img_id."' AND img_id = img_img_id AND dir_dir_parent_id = dir_id";
		$DBRESULT =& $pearDB->query($rq);
		while ($img_path =& $DBRESULT->fetchRow()) {
			$fullpath = $mediadir.$img_path["dir_alias"]."/".$img_path["img_path"];
			if (is_file($fullpath)) {
				unlink($fullpath);
			}
			$pearDB->query("DELETE FROM view_img WHERE img_id = '".$img_id."'");
			$pearDB->query("DELETE FROM view_img_dir_relation WHERE img_img_id = '".$img_id."'");
		}
		$DBRESULT->free();
	}


	function updateImg($img_id, $HTMLfile, $dir_alias, $img_name, $img_comment) {
		if (!$img_id)
			return;
		global $pearDB;
		$mediadir = "./img/media/";
		$uploaddir = "../filesUpload/images/";
		/*
		 * 1. upload new file
		 * 2. rename to new name
		 * 3. move to new directory (if not 1.)
		 * 4. update comment
		 */
		$rq = "SELECT dir_id, dir_alias, img_path, img_comment FROM view_img, view_img_dir, view_img_dir_relation ";
		$rq .= " WHERE img_id = '".$img_id."' AND img_id = img_img_id AND dir_dir_parent_id = dir_id";
		$DBRESULT =& $pearDB->query($rq);
		if (!$DBRESULT)
		    return;
		$img_info =& $DBRESULT->fetchRow();

		if ($dir_alias)
			$dir_alias = sanitizePath($dir_alias);
		else
			$dir_alias = $img_info["dir_alias"];
		/* insert new file */
		if ($HTMLfile && $HTMLfile->isUploadedFile()) {
			$fileinfo = $HTMLfile->getValue();
			if (!isset($fileinfo["name"]))
				return false;
			if (isValidImage($uploaddir.$fileinfo["name"]) )
				$HTMLfile->moveUploadedFile($uploaddir);
			else
				return false;
			deleteImg($img_id);
			$img_id = insertImg ($uploaddir, $fileinfo["name"], $dir_alias, $img_info["img_path"], $img_info["img_comment"]);

		}
		/* rename AND not moved*/
		if ($img_name && $dir_alias == $img_info["dir_alias"]) {
			$img_ext = pathinfo($img_info["img_path"], PATHINFO_EXTENSION);
			$filename= $img_name.".".$img_ext;
			$oldname = $mediadir.$img_info["dir_alias"]."/".$img_info["img_path"];
			$newname = $mediadir.$img_info["dir_alias"]."/".$filename;
			if (rename($oldname,$newname)) {
				$img_info["img_path"] = $filename;
				$DBRESULT = $pearDB->query("UPDATE view_img SET img_name = '".$img_name."', img_path = '".$filename."' WHERE img_id = '".$img_id."'");
			}
		}
		/* move to new dir - only processed if no file was uploaded */
		if (!$HTMLfile->isUploadedFile() && $dir_alias != $img_info["dir_alias"]) {
			if (!($dir_id = testDirectoryExistence($dir_alias)) )
				$dir_id = insertDirectory($dir_alias);
			$oldpath = $mediadir.$img_info["dir_alias"]."/".$img_info["img_path"];
			$newpath = $mediadir.$dir_alias."/".$img_info["img_path"];
			if (rename($oldpath,$newpath))
				$DBRESULT =& $pearDB->query("UPDATE view_img_dir_relation SET dir_dir_parent_id = '".$dir_id."' WHERE img_img_id = '".$img_id."'");
		}
		if ($img_comment) {
			$DBRESULT = $pearDB->query("UPDATE view_img SET img_comment = '".htmlentities($img_comment, ENT_QUOTES)."' WHERE img_id = '".$img_id."'");
		}
	}

	function moveMultImg ($images, $dirName) {
		if (count($images)>0)
		    foreach($images as $id) {
			moveImg($id, $dirName);
		    }
	}

	function moveImg($img_id, $dir_alias) {
		if (!$img_id)
			return;
		global $pearDB;
		$mediadir = "./img/media/";
		$rq = "SELECT dir_id, dir_alias, img_path, img_comment FROM view_img, view_img_dir, view_img_dir_relation ";
		$rq .= " WHERE img_id = '".$img_id."' AND img_id = img_img_id AND dir_dir_parent_id = dir_id";
		$DBRESULT =& $pearDB->query($rq);
		if (!$DBRESULT)
		    return;
		$img_info =& $DBRESULT->fetchRow();

		if ($dir_alias)
			$dir_alias = sanitizePath($dir_alias);
		else
			$dir_alias = $img_info["dir_alias"];
		if ($dir_alias != $img_info["dir_alias"]) {
			if (!testDirectoryExistence($dir_alias))
				$dir_id = insertDirectory($dir_alias);
			else {
				$rq = "SELECT dir_id FROM view_img_dir WHERE dir_alias = '".$dir_alias."'";
				$DBRESULT =& $pearDB->query($rq);
				if (!$DBRESULT)
					return;
				$dir_info =& $DBRESULT->fetchRow();
				$dir_id = $dir_info["dir_id"];
			}
			$oldpath = $mediadir.$img_info["dir_alias"]."/".$img_info["img_path"];
			$newpath = $mediadir.$dir_alias."/".$img_info["img_path"];
			if (rename($oldpath,$newpath))
				$DBRESULT =& $pearDB->query("UPDATE view_img_dir_relation SET dir_dir_parent_id = '".$dir_id."' WHERE img_img_id = '".$img_id."'");
		}
	}


	function testDirectoryCallback ($name) {
		return testDirectoryExistence($name)==0;
	}

	function testDirectoryExistence ($name)	{
		global $pearDB;
		$dir_id = 0;
		$DBRESULT =& $pearDB->query("SELECT dir_name, dir_id FROM view_img_dir WHERE dir_name = '".htmlentities($name, ENT_QUOTES)."'");
		if ($DBRESULT->numRows() >= 1) {
			$dir =& $DBRESULT->fetchRow();
			$dir_id = $dir["dir_id"];
		}
		return $dir_id;
	}

	function testDirectoryIsEmpty($dir_id) {
		if (!$dir_id)
			return true;
		global $pearDB;

		$rq = "SELECT img_img_id FROM view_img_dir_relation WHERE dir_dir_parent_id = '".$dir_id."'";
		$DBRESULT =& $pearDB->query($rq);
		$empty = true;
		if ($DBRESULT && $DBRESULT->numRows() >= 1)
			$empty = false;
		$DBRESULT->free();
		return $empty;
	}

	function insertDirectory($dir_alias, $dir_comment = "")	{
		global $pearDB;
		$mediadir = "./img/media/";
		$dir_alias = sanitizePath($dir_alias);
		@mkdir($mediadir.$dir_alias);
		if (is_dir($mediadir.$dir_alias)) {
			touch($mediadir.$dir_alias."/index.html");
			$rq = "INSERT INTO view_img_dir ";
			// NF: do we need alias and name?
			$rq .= "(dir_name, dir_alias, dir_comment) ";
			$rq .= "VALUES ";
			$dir_alias_safe = htmlentities($dir_alias, ENT_QUOTES);
			$rq .= "('".$dir_alias_safe."', '".$dir_alias_safe."', '".htmlentities($dir_comment, ENT_QUOTES)."')";
			$DBRESULT =& $pearDB->query($rq);
			$DBRESULT =& $pearDB->query("SELECT MAX(dir_id) FROM view_img_dir");
			$dir_id =& $DBRESULT->fetchRow();
			$DBRESULT->free();
			return ($dir_id["MAX(dir_id)"]);
		}
		else
			return "";
	}


	function deleteMultDirectory($dirs = array()) {
		foreach($dirs as $selector => $val) {
			$id = explode('-',$selector);
			if (count($id)!=1)
				continue;
			deleteDirectory($id[0]);
		}
	}

	function deleteDirectory ($dirid ) {
		global $pearDB;
		$mediadir = "./img/media/";
		/*
		 * Purge images of the directory
		 */
		$rq = "SELECT img_img_id FROM view_img_dir_relation WHERE dir_dir_parent_id = '".$dirid."'";
		$DBRESULT =& $pearDB->query($rq);
		while ($img =& $DBRESULT->fetchRow())
			deleteImg($img["img_img_id"]);
		/*
		 * Delete directory
		 */
		$rq = "SELECT dir_alias FROM view_img_dir WHERE dir_id = '".$dirid."'";
		$DBRESULT =& $pearDB->query($rq);
		$dir_alias =& $DBRESULT->fetchRow();
		$fileTab = scandir($mediadir . $dir_alias["dir_alias"]);
		foreach ($fileTab as $fileName) {
			if (is_file($mediadir . $dir_alias["dir_alias"] . "/" . $fileName))
				unlink($mediadir . $dir_alias["dir_alias"] . "/" . $fileName);
		}
		rmdir($mediadir.$dir_alias["dir_alias"]);
		if (!is_dir($mediadir.$dir_alias["dir_alias"]))	{
			$DBRESULT =& $pearDB->query("DELETE FROM view_img_dir WHERE dir_id = '".$dirid."'");
		}
	}


	function updateDirectory($dir_id, $dir_alias, $dir_comment = "") {
		if (!$dir_id)
			return;
		global $pearDB;
		$mediadir = "./img/media/";
		$rq = "SELECT dir_alias FROM view_img_dir WHERE dir_id = '".$dir_id."'";
		$DBRESULT =& $pearDB->query($rq);
		$old_dir =& $DBRESULT->fetchRow();
		$dir_alias = sanitizePath($dir_alias);
		if (!is_dir($mediadir.$old_dir["dir_alias"]))
			mkdir($mediadir.$dir_alias);
		else
			rename($mediadir.$old_dir["dir_alias"], $mediadir.$dir_alias);
		if (is_dir($mediadir.$dir_alias))	{
			$rq = "UPDATE view_img_dir ";
			$rq .= "SET      dir_name = '".htmlentities($dir_alias, ENT_QUOTES)."', " .
					"dir_alias = '".$dir_alias."', " .
					"dir_comment = '".htmlentities($dir_comment, ENT_QUOTES)."' " .
					"WHERE dir_id = '".$dir_id."'";
			$DBRESULT =& $pearDB->query($rq);
		}
	}

?>
