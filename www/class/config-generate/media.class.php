<?php

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

?>
