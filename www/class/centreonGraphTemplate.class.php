<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of centreonGraphTemplate
 *
 * @author bsauveton
 */
class centreonGraphTemplate
{
    
    
    /**
     *
     * @var type 
     */
    protected $db;
    
    /**
     *
     * @var type 
     */
    protected $instanceObj;
    

    /**
     * Constructor
     *
     * @param CentreonDB $db
     * @return void
     */
    function __construct($db)
    {
        $this->db = $db;
        $this->instanceObj = new CentreonInstance($db);
    }
        /**
     * 
     * @param array $values
     * @return array
     */
    public function getObjectForSelect2($values = array(), $options = array(), $register = '1')
    {
        
        $explodedValues = implode(',', $values);
        if (empty($explodedValues)) {
            $explodedValues = "''";
        }
        
        $query = "SELECT graph_id, name FROM giv_graphs_template WHERE graph_id IN (" . $explodedValues . ") ORDER BY name";
        $resRetrieval = $this->db->query($query);
        while ($row = $resRetrieval->fetchRow()) {
            $items[] = array(
                'id' => $row['graph_id'],
                'text' => $row['name']
            );
        }

        return $items;

    }
}
