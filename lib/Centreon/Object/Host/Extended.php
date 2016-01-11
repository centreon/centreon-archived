<?php
require_once "Centreon/Object/Object.php";

/**
 * Used for interacting with host extended information
 *
 * @author sylvestre
 */
class Centreon_Object_Host_Extended extends Centreon_Object
{
    protected $table = "extended_host_information";
    protected $primaryKey = "host_host_id";
    protected $uniqueLabelField = "host_host_id";

    /**
     * Used for inserting object into database
     *
     * @param array $params
     * @return int
     */
    public function insert($params = array())
    {
        $sql = "INSERT INTO $this->table ";
        $sqlFields = "";
        $sqlValues = "";
        $sqlParams = array();
        foreach ($params as $key => $value) {
            if ($sqlFields != "") {
                $sqlFields .= ",";
            }
            if ($sqlValues != "") {
                $sqlValues .= ",";
            }
            $sqlFields .= $key;
            $sqlValues .= "?";
            $sqlParams[] = $value;
        }
        if ($sqlFields && $sqlValues) {
            $sql .= "(".$sqlFields.") VALUES (".$sqlValues.")";
            $this->db->query($sql, $sqlParams);
            return $this->db->lastInsertId($this->table, $this->primaryKey);
        }
        return null;
    }

    /**
     * Get object parameters
     *
     * @param int $objectId
     * @param mixed $parameterNames
     * @return array
     */
    public function getParameters($objectId, $parameterNames)
    {
	$params = parent::getParameters($objectId, $parameterNames);
	$params_image = array("ehi_icon_image", "ehi_vrml_image", "ehi_statusmap_image");
	foreach ($params_image as $image) {
  	    if (array_key_exists($image, $params)) {
	        $sql = "SELECT dir_name,img_path 
                        FROM view_img vi 
                        LEFT JOIN view_img_dir_relation vidr ON vi.img_id = vidr.img_img_id 
                        LEFT JOIN view_img_dir vid ON vid.dir_id = vidr.dir_dir_parent_id 
                        WHERE img_id = ?";
                $res = $this->getResult($sql, array($params[$image]), "fetch");
		if (is_array($res)) {
                    $params[$image] = $res["dir_name"]."/".$res["img_path"];
                }
            }
        }	
        return $params;
    }

    public function duplicate()
    {

    }
}
