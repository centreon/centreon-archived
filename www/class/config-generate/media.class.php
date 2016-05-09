<?php
/*
 * Copyright 2005-2015 Centreon
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

class Media extends AbstractObject {
    private $medias = null;
    
    private function getMedias() {        
        $stmt = $this->backend_instance->db->prepare("SELECT 
              img_id, img_name, img_path, dir_name
            FROM view_img, view_img_dir_relation, view_img_dir
            WHERE view_img.img_id = view_img_dir_relation.img_img_id 
                AND view_img_dir_relation.dir_dir_parent_id = view_img_dir.dir_id
            ");
        $stmt->execute();
        $this->medias = $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC);
    }
    
    public function getMediaPathFromId($media_id) {
        if (is_null($this->medias)) {
            $this->getMedias();
        }
        
        $result = null;
        if (!is_null($media_id) && isset($this->medias[$media_id])) {
            $result = $this->medias[$media_id]['dir_name'] . '/' . $this->medias[$media_id]['img_path'];
        }
        
        return $result;
    }
}
