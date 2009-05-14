<?php
/*
 * Copyright 2005-2009 MERETHIS
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

	function testExistence ($name = NULL)	{
		global $pearDB;
		global $form;
		$id = NULL;
		if (isset($form))
			$id = $form->getSubmitValue('img_id');
		$res =& $pearDB->query("SELECT img_name, img_id FROM view_img WHERE img_name = '".htmlentities($name, ENT_QUOTES)."'");
		$img =& $res->fetchRow();
		#Modif case
		if ($res->numRows() >= 1 && $img["img_id"] == $id)	
			return true;
		#Duplicate entry
		else if ($res->numRows() >= 1 && $img["img_id"] != $id)	
			return false;
		else
			return true;
	}

	function deleteImgInDB ($imgs = array(), $dir_alias = NULL, $img_path = NULL)	{
		global $pearDB;
		/*
		 * Delete selected images
		 */
		if (!$img_path && !$dir_alias) {
			foreach ($imgs as $key => $value)	{
				$DBRESULT =& $pearDB->query("SELECT dir_alias, img_path FROM view_img, view_img_dir, view_img_dir_relation WHERE img_id = '".$key."' AND img_id = img_img_id AND dir_dir_parent_id = dir_id");
				$img_path =& $DBRESULT->fetchRow();
				if (is_file("./img/media/".$img_path["dir_alias"]."/".$img_path["img_path"]))
					unlink("./img/media/".$img_path["dir_alias"]."/".$img_path["img_path"]);
				if (!is_file("./img/media/".$img_path["dir_alias"]."/".$img_path["img_path"]))	{
					$pearDB->query("DELETE FROM view_img WHERE img_id = '".$key."'");
				}
			}
		} else {
			/*
			 * Delete single image by name
			 */
			$DBRESULT =& $pearDB->query("SELECT img_id FROM view_img, view_img_dir, view_img_dir_relation WHERE img_path = '".htmlentities($img_path, ENT_QUOTES)."' AND dir_alias = '".htmlentities($dir_alias, ENT_QUOTES)."' AND dir_id = dir_dir_parent_id AND img_id = img_img_id");
			$img_id =& $DBRESULT->fetchRow();
			if (isset($img_id["img_id"]) && $img_id && is_file("./img/media/".$dir_alias."/".$img_path))
				unlink("./img/media/".$dir_alias."/".$img_path);
			if (isset($img_id["img_id"]) && $img_id && !is_file("./img/media/".$dir_alias."/".$img_path))	{
				$pearDB->query("DELETE FROM view_img WHERE img_id = '".$img_id["img_id"]."'");
			}
		}
	}
	
	function updateImgInDB ($img_id = NULL, $file = NULL, $path = NULL)	{
		if (!$img_id) return;
		updateimg($img_id, $file, $path);
		updateImgDirectories($img_id);
	}
	
	function updateImg($img_id, $file = NULL, $path = NULL)	{
		if (!$img_id) return;
		global $form;
		global $pearDB;
		$ret = array();
		$ret["img_path"] = NULL;
		$ret = $form->getSubmitValues();
		if (isset($ret["img_name"]) && $ret["img_name"])	{
			$rq = "UPDATE view_img SET ";
			$rq .= "img_name = '".$ret["img_name"]."' WHERE img_id = '".$img_id."'";
			$DBRESULT = $pearDB->query($rq);
			
		}
		if ($file->isUploadedFile())	{
			/*
			 * Delete old file
			 */
			$DBRESULT =& $pearDB->query("SELECT dir_alias, img_path FROM view_img, view_img_dir, view_img_dir_relation WHERE img_id = '".$img_id."' AND img_id = img_img_id AND dir_dir_parent_id = dir_id");
			$img_path =& $DBRESULT->fetchRow();
			if (is_file("./img/media/".$img_path["dir_alias"]."/".$img_path["img_path"]))
				unlink("./img/media/".$img_path["dir_alias"]."/".$img_path["img_path"]);
			/*
			 * Copy new file
			 */
			 if (!is_file("./img/media/".$img_path["dir_alias"]."/".$img_path["img_path"]))	{
				$DBRESULT =& $pearDB->query("SELECT dir_alias FROM view_img_dir WHERE dir_id = '".$ret["directories"]."'");
				$dir_alias =& $DBRESULT->fetchRow();			
				$file->moveUploadedFile("./img/media/".$img_path["dir_alias"]);
				$fDataz =& $file->getValue();
				rename("./img/media/".$img_path["dir_alias"]."/".$fDataz["name"], "./img/media/".$img_path["dir_alias"]."/".str_replace(" ", "_", $fDataz["name"]));
				# Delete space in image name
				$fDataz["name"] = str_replace(" ", "_", $fDataz["name"]);
				if (is_file("./img/media/".$img_path["dir_alias"]."/".$fDataz["name"]))	{
					# Manage name
					$pinfo = pathinfo("./img/media/".$img_path["dir_alias"]."/".$fDataz["name"]);
					$ret["img_name"] = $pinfo["filename"];
					$ret["img_path"] = $pinfo["basename"];
					/*
					 * 'pathinfo' func return a basename value NULL when PHP < 5.2
					 */
					if (!$ret["img_name"])	{
						$img_name = explode(".", $ret["img_path"]);
						$ret["img_name"] = $img_name[0];
					}					
					$rq = "UPDATE view_img SET ";
					$rq .= "img_name = '".$ret["img_name"]."', img_path = '".$ret["img_path"]."' WHERE img_id = '".$img_id."'";
					$DBRESULT = $pearDB->query($rq);
					$DBRESULT =& $pearDB->query("SELECT MAX(img_id) FROM view_img");
					$img_id =& $DBRESULT->fetchRow();
					updateImgDirectories($img_id["MAX(img_id)"]);
					$img_id = $img_id["MAX(img_id)"];
				}		
			 }
		}
		if (isset($ret["directories"]) && $ret["directories"])	{
			$DBRESULT =& $pearDB->query("SELECT dir_id, dir_alias FROM view_img_dir, view_img_dir_relation WHERE img_img_id = '".$img_id."' AND dir_dir_parent_id = dir_id");
			$dir_old =& $DBRESULT->fetchRow();
			/*
			 * Check if directory has been changed
			 */
			if ($ret["directories"] != $dir_old["dir_id"])	{
				$DBRESULT =& $pearDB->query("SELECT img_path FROM view_img WHERE img_id = '".$img_id."' LIMIT 1");
				$img_path =& $DBRESULT->fetchRow();
				$DBRESULT =& $pearDB->query("SELECT dir_alias FROM view_img_dir WHERE dir_id = '".$ret["directories"]."'");
				$dir_new =& $DBRESULT->fetchRow();
				/*
				 * Delete existing occurence
				 */
				if (is_file("./img/media/".$dir_new["dir_alias"]."/".$img_path["img_path"]))
					deleteImgInDB(array(), $dir_new["dir_alias"], $img_path["img_path"]);
				/*
				 * Moove file
				 */
				rename("./img/media/".$dir_old["dir_alias"]."/".$img_path["img_path"], "./img/media/".$dir_new["dir_alias"]."/".$img_path["img_path"]);
				if (is_file("./img/media/".$dir_new["dir_alias"]."/".$img_path["img_path"]))	{	
					$DBRESULT =& $pearDB->query("UPDATE view_img_dir_relation SET dir_dir_parent_id = '".htmlentities($ret["directories"], ENT_QUOTES)."' WHERE img_img_id = '".$img_id."'");
				}
			}
		}
	}
	
	function insertImgInDB ($file = NULL, $file1 = NULL, $file2 = NULL, $file3 = NULL, $file4 = NULL, $path = NULL)	{
		$img_id = insertImg($file, $file1, $file2, $file3, $file4, $path);
		return ($img_id);
	}
	
	function insertImg($file = NULL, $file1 = NULL, $file2 = NULL, $file3 = NULL, $file4 = NULL, $path = NULL)	{
		global $form;
		global $pearDB;
		global $oreon;
		$img_id = 0;
		$ret = array();
		$nbr_img = 0;
		$elem = $file->getValue();
		if ($elem["error"] == 0)
			$nbr_img = $nbr_img + 1;
		$elem = $file1->getValue();
		if ($elem["error"] == 0)
			$nbr_img = $nbr_img + 1;
		$elem = $file2->getValue();
		if ($elem["error"] == 0)
			$nbr_img = $nbr_img + 1;
		$elem = $file3->getValue();
		if ($elem["error"] == 0)
			$nbr_img = $nbr_img + 1;
		$elem = $file4->getValue();
		if ($elem["error"] == 0)
			$nbr_img = $nbr_img + 1;
		$ret["img_path"] = NULL;
		$ret = $form->getSubmitValues();		
		$dir_id = $ret["directories"];
		$rq = "SELECT dir_alias FROM view_img_dir WHERE dir_id = '".$dir_id."' LIMIT 1";
		$DBRESULT =& $pearDB->query($rq);
		$dir_alias =& $DBRESULT->fetchRow();		
		if ($file)	{
			$fDataz =& $file->getValue();
			if (stristr($fDataz["type"], "image") && !is_file("./img/media/".$dir_alias["dir_alias"]."/".str_replace(" ", "_", $fDataz["name"])))	{
				# Moove image in 'media' directory
				$file->moveUploadedFile("./img/media/".$dir_alias["dir_alias"]);
				rename("./img/media/".$dir_alias["dir_alias"]."/".$fDataz["name"], "./img/media/".$dir_alias["dir_alias"]."/".str_replace(" ", "_", $fDataz["name"]));
				# Delete space in image name
				$fDataz["name"] = str_replace(" ", "_", $fDataz["name"]);
				if (is_file("./img/media/".$dir_alias["dir_alias"]."/".$fDataz["name"]))	{
					# Manage name
					$pinfo = pathinfo("./img/media/".$dir_alias["dir_alias"]."/".$fDataz["name"]);
					$ret["img_name"] = $pinfo["filename"];
					$ret["img_path"] = $pinfo["basename"];
					/*
					 * 'pathinfo' func return a basename value NULL when PHP < 5.2
					 */
					if (!$ret["img_name"])	{
						$img_name = explode(".", $ret["img_path"]);
						$ret["img_name"] = $img_name[0];
					}					
					$rq = "INSERT INTO view_img ";
					$rq .= "(img_name, img_path, img_comment) ";
					$rq .= "VALUES ";
					$rq .= "('".htmlentities($ret["img_name"], ENT_QUOTES)."', '".$ret["img_path"]."', '".htmlentities($ret["img_comment"], ENT_QUOTES)."')";
					$pearDB->query($rq);
					$res =& $pearDB->query("SELECT MAX(img_id) FROM view_img");
					$img_id =& $res->fetchRow();
					updateImgDirectories($img_id["MAX(img_id)"]);
				}
			}
		}
		if ($file1)	{
			$fDataz =& $file1->getValue();
			if (stristr($fDataz["type"], "image") && !is_file("./img/media/".$dir_alias["dir_alias"]."/".str_replace(" ", "_", $fDataz["name"])))	{
				# Moove image in 'media' directory
				$file1->moveUploadedFile("./img/media/".$dir_alias["dir_alias"]);
				rename("./img/media/".$dir_alias["dir_alias"]."/".$fDataz["name"], "./img/media/".$dir_alias["dir_alias"]."/".str_replace(" ", "_", $fDataz["name"]));
				# Delete space in image name
				$fDataz["name"] = str_replace(" ", "_", $fDataz["name"]);
				if (is_file("./img/media/".$dir_alias["dir_alias"]."/".$fDataz["name"]))	{
					# Manage name
					$pinfo = pathinfo("./img/media/".$dir_alias["dir_alias"]."/".$fDataz["name"]);
					$ret["img_name"] = $pinfo["filename"];
					$ret["img_path"] = $pinfo["basename"];
					/*
					 * 'pathinfo' func return a basename value NULL when PHP < 5.2
					 */
					if (!$ret["img_name"])	{
						$img_name = explode(".", $ret["img_path"]);
						$ret["img_name"] = $img_name[0];
					}					
					$rq = "INSERT INTO view_img ";
					$rq .= "(img_name, img_path, img_comment) ";
					$rq .= "VALUES ";
					$rq .= "('".htmlentities($ret["img_name"], ENT_QUOTES)."', '".$ret["img_path"]."', '".htmlentities($ret["img_comment"], ENT_QUOTES)."')";
					$pearDB->query($rq);
					$res =& $pearDB->query("SELECT MAX(img_id) FROM view_img");
					$img_id =& $res->fetchRow();
					updateImgDirectories($img_id["MAX(img_id)"]);
				}
			}
		}
		if ($file2)	{
			$fDataz =& $file2->getValue();
			if (stristr($fDataz["type"], "image") && !is_file("./img/media/".$dir_alias["dir_alias"]."/".str_replace(" ", "_", $fDataz["name"])))	{
				# Moove image in 'media' directory
				$file2->moveUploadedFile("./img/media/".$dir_alias["dir_alias"]);
				rename("./img/media/".$dir_alias["dir_alias"]."/".$fDataz["name"], "./img/media/".$dir_alias["dir_alias"]."/".str_replace(" ", "_", $fDataz["name"]));
				# Delete space in image name
				$fDataz["name"] = str_replace(" ", "_", $fDataz["name"]);
				if (is_file("./img/media/".$dir_alias["dir_alias"]."/".$fDataz["name"]))	{
					# Manage name
					$pinfo = pathinfo("./img/media/".$dir_alias["dir_alias"]."/".$fDataz["name"]);
					$ret["img_name"] = $pinfo["filename"];
					$ret["img_path"] = $pinfo["basename"];
					/*
					 * 'pathinfo' func return a basename value NULL when PHP < 5.2
					 */
					if (!$ret["img_name"])	{
						$img_name = explode(".", $ret["img_path"]);
						$ret["img_name"] = $img_name[0];
					}					
					$rq = "INSERT INTO view_img ";
					$rq .= "(img_name, img_path, img_comment) ";
					$rq .= "VALUES ";
					$rq .= "('".htmlentities($ret["img_name"], ENT_QUOTES)."', '".$ret["img_path"]."', '".htmlentities($ret["img_comment"], ENT_QUOTES)."')";
					$pearDB->query($rq);
					$res =& $pearDB->query("SELECT MAX(img_id) FROM view_img");
					$img_id =& $res->fetchRow();
					updateImgDirectories($img_id["MAX(img_id)"]);
				}
			}
		}
		if ($file3)	{
			$fDataz =& $file3->getValue();
			if (stristr($fDataz["type"], "image") && !is_file("./img/media/".$dir_alias["dir_alias"]."/".str_replace(" ", "_", $fDataz["name"])))	{
				# Moove image in 'media' directory
				$file3->moveUploadedFile("./img/media/".$dir_alias["dir_alias"]);
				rename("./img/media/".$dir_alias["dir_alias"]."/".$fDataz["name"], "./img/media/".$dir_alias["dir_alias"]."/".str_replace(" ", "_", $fDataz["name"]));
				# Delete space in image name
				$fDataz["name"] = str_replace(" ", "_", $fDataz["name"]);
				if (is_file("./img/media/".$dir_alias["dir_alias"]."/".$fDataz["name"]))	{
					# Manage name
					$pinfo = pathinfo("./img/media/".$dir_alias["dir_alias"]."/".$fDataz["name"]);
					$ret["img_name"] = $pinfo["filename"];
					$ret["img_path"] = $pinfo["basename"];
					/*
					 * 'pathinfo' func return a basename value NULL when PHP < 5.2
					 */
					if (!$ret["img_name"])	{
						$img_name = explode(".", $ret["img_path"]);
						$ret["img_name"] = $img_name[0];
					}					
					$rq = "INSERT INTO view_img ";
					$rq .= "(img_name, img_path, img_comment) ";
					$rq .= "VALUES ";
					$rq .= "('".htmlentities($ret["img_name"], ENT_QUOTES)."', '".$ret["img_path"]."', '".htmlentities($ret["img_comment"], ENT_QUOTES)."')";
					$pearDB->query($rq);
					$res =& $pearDB->query("SELECT MAX(img_id) FROM view_img");
					$img_id =& $res->fetchRow();
					updateImgDirectories($img_id["MAX(img_id)"]);
				}
			}
		}
		if ($file4)	{
			$fDataz =& $file4->getValue();
			if (stristr($fDataz["type"], "image") && !is_file("./img/media/".$dir_alias["dir_alias"]."/".str_replace(" ", "_", $fDataz["name"])))	{
				# Moove image in 'media' directory
				$file4->moveUploadedFile("./img/media/".$dir_alias["dir_alias"]);
				rename("./img/media/".$dir_alias["dir_alias"]."/".$fDataz["name"], "./img/media/".$dir_alias["dir_alias"]."/".str_replace(" ", "_", $fDataz["name"]));
				# Delete space in image name
				$fDataz["name"] = str_replace(" ", "_", $fDataz["name"]);
				if (is_file("./img/media/".$dir_alias["dir_alias"]."/".$fDataz["name"]))	{
					# Manage name
					$pinfo = pathinfo("./img/media/".$dir_alias["dir_alias"]."/".$fDataz["name"]);
					$ret["img_name"] = $pinfo["filename"];
					$ret["img_path"] = $pinfo["basename"];
					/*
					 * 'pathinfo' func return a basename value NULL when PHP < 5.2
					 */
					if (!$ret["img_name"])	{
						$img_name = explode(".", $ret["img_path"]);
						$ret["img_name"] = $img_name[0];
					}					
					$rq = "INSERT INTO view_img ";
					$rq .= "(img_name, img_path, img_comment) ";
					$rq .= "VALUES ";
					$rq .= "('".htmlentities($ret["img_name"], ENT_QUOTES)."', '".$ret["img_path"]."', '".htmlentities($ret["img_comment"], ENT_QUOTES)."')";
					$pearDB->query($rq);
					$res =& $pearDB->query("SELECT MAX(img_id) FROM view_img");
					$img_id =& $res->fetchRow();
					updateImgDirectories($img_id["MAX(img_id)"]);
				}
			}
		}
		return ($img_id["MAX(img_id)"]);
	}

	function updateImgDirectories($img_id, $ret = array())	{
		if (!$img_id) return;
		global $form;
		global $pearDB;
		$rq = "DELETE FROM view_img_dir_relation ";
		$rq .= "WHERE img_img_id = '".$img_id."'";
		$DBRESULT =& $pearDB->query($rq);
		if (isset($ret["directories"]))
			$ret = $ret["directories"];
		else
		$ret = $form->getSubmitValue("directories");
		$rq = "INSERT INTO view_img_dir_relation ";
		$rq .= "(dir_dir_parent_id, img_img_id) ";
		$rq .= "VALUES ";
		$rq .= "('".$ret."', '".$img_id."')";
		$DBRESULT =& $pearDB->query($rq);		
	}
?>