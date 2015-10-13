<?php

class Timezone extends AbstractObject {
    private $aTimezone = null;
    
    private function getTimezone()
    {   
        if (!is_null($this->aTimezone)) {
            return $this->aTimezone;
        }

        $stmt = $this->backend_instance->db->prepare("SELECT 
                timezone_id,
                timezone_name
            FROM timezone");
        $stmt->execute();
        $resulats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($resulats as $res) {
            $this->aTimezone[$res['timezone_id']] = $res['timezone_name'];
        }
        
    }
    
    public function getTimezoneFromId($iTimezone)
    {
        if (is_null($this->aTimezone)) {
            $this->getTimezone();
        }
        
        $result = null;
        if (!is_null($iTimezone) && isset($this->aTimezone[$iTimezone])) {
            $result = $this->aTimezone[$iTimezone];
        }
        
        return $result;
    }
}

?>
