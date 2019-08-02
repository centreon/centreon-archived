<?php
/*
 * Copyright 2005 - 2019 Centreon (https://www.centreon.com/)
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
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

namespace ConfigGenerateRemote;

use \PDO;

class Media extends AbstractObject
{
    private $medias = null;
    protected $table = 'view_img';
    protected $generate_filename = 'view_img.infile';
    protected $attributes_select = '
        img_id,
        img_name,
        img_path,
        img_comment,
        dir_id,
        dir_name,
        dir_alias,
        dir_comment
    ';
    protected $attributes_write = array(
        'img_id',
        'img_name',
        'img_path',
        'img_comment',
    );
    protected $path_img = null;

    private function getMedias()
    {
        $query = "
            SELECT $this->attributes_select
            FROM view_img, view_img_dir_relation, view_img_dir " .
            "WHERE view_img.img_id = view_img_dir_relation.img_img_id " .
            "AND view_img_dir_relation.dir_dir_parent_id = view_img_dir.dir_id";
        $stmt = $this->backend_instance->db->prepare($query);
        $stmt->execute();
        $this->medias = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);

        $stmt = $this->backend_instance->db->prepare('SELECT * FROM options WHERE `key` = "nagios_path_img"');
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->path_img = $row['value'];
    }

    protected function copyMedia($dir, $file) {
        $this->backend_instance->createDirectories(array($this->backend_instance->getPath() . '/media/' . $dir));
        @copy($this->path_img . '/' . $dir . '/' . $file, $this->backend_instance->getPath() . '/media/' . $dir . '/' . $file);
    }

    public function getMediaPathFromId($media_id)
    {
        if (is_null($this->medias)) {
            $this->getMedias();
        }

        $result = null;
        if (!is_null($media_id) && isset($this->medias[$media_id])) {
            $result = $this->medias[$media_id]['dir_name'] . '/' . $this->medias[$media_id]['img_path'];
            if ($this->checkGenerate($media_id)) {
                return $result;
            }

            $media = array(
                'img_id' => $media_id,
                'img_name' => $this->medias[$media_id]['img_name'],
                'img_path' => $this->medias[$media_id]['img_path'],
                'img_comment' => $this->medias[$media_id]['img_comment'],
            );
            $this->generateObjectInFile($media, $media_id);
            viewImgDirRelation::getInstance($this->dependencyInjector)->addRelation($media_id, $this->medias[$media_id]['dir_id']);
            viewImageDir::getInstance($this->dependencyInjector)->add($this->medias[$media_id], $this->medias[$media_id]['dir_id']);
            $this->copyMedia($this->medias[$media_id]['dir_name'], $this->medias[$media_id]['img_path']);
        }

        return $result;
    }
}
