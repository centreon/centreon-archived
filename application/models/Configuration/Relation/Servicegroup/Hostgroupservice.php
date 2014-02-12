<?php

namespace Models\Configuration;


class Relation\Servicegroup\Hostgroupservice extends Relation
{
    protected $relationTable = "servicegroup_relation";
    protected $firstKey = "servicegroup_sg_id";
    protected $secondKey = "service_service_id";

    /**
     * Used for inserting relation into database
     *
     * @param int $fkey
     * @param int $hgId
     * @param int $serviceId
     * @return void
     */
    public function insert($fkey, $hgId, $serviceId)
    {
        $sql = "INSERT INTO $this->relationTable ($this->firstKey, hostgroup_hg_id, $this->secondKey) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array($fkey, $hgId, $serviceId));
    }

    /**
     * Used for deleting relation from database
     *
     * @param int $fkey
     * @param int $hgId
     * @param int $serviceId
     * @return void
     */
    public function delete($fkey, $hgId = null, $serviceId = null)
    {
        if (isset($fkey) && isset($hgId) && isset($serviceId)) {
            $sql = "DELETE FROM $this->relationTable WHERE $this->firstKey = ? AND hostgroup_hg_id = ? AND $this->secondKey = ?";
            $args = array($fkey, $hgId, $serviceId);
        } elseif (isset($hgId) && isset($serviceId)) {
            $sql = "DELETE FROM $this->relationTable WHERE hostgroup_hg_id = ? AND $this->secondKey = ?";
            $args = array($hgId, $serviceId);
        } else {
            $sql = "DELETE FROM $this->relationTable WHERE $this->firstKey = ?";
            $args = array($fkey);
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($args);
    }

    /**
     * Get service group id from host id, service id
     *
     * @param int $hgId
     * @param int $serviceId
     * @return array
     */
    public function getServicegroupIdFromHostIdServiceId($hgId, $serviceId)
    {
        $sql = "SELECT $this->firstKey FROM $this->relationTable WHERE hostgroup_hg_id = ? AND $this->secondKey = ?";
        $result = $this->getResult($sql, array($hgId, $serviceId));
        $tab = array();
        foreach ($result as $rez) {
            $tab[] = $rez[$this->firstKey];
        }
        return $tab;
    }

    /**
     * Get Host id service id from service group id
     *
     * @param int $servicegroupId
     * @return array multidimentional array with hostgroup_id and service_id indexes
     */
    public function getHostGroupIdServiceIdFromServicegroupId($servicegroupId)
    {
        $sql = "SELECT hostgroup_hg_id, $this->secondKey FROM $this->relationTable WHERE $this->firstKey = ?";
        $result = $this->getResult($sql, array($servicegroupId));
        $tab = array();
        $i = 0;
        foreach ($result as $rez) {
            $tab[$i]['hostgroup_id'] = $rez['hostgroup_hg_id'];
            $tab[$i]['service_id'] = $rez[$this->secondKey];
            $i++;
        }
        return $tab;
    }

    /**
     * This call will directly throw an exception
     *
     * @param string $name
     * @param array $arg
     * @throws Exception
     */
    public function __call($name, $arg)
    {
       throw new Exception('Unknown method');
    }
}
