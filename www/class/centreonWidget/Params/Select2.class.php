<?php

require_once "class/centreonWidget/Params.class.php";

class CentreonWidgetParamsSelect2 extends CentreonWidgetParams
{
    public function __construct($db, $quickform, $userId)
    {
        parent::__construct($db, $quickform, $userId);
    }

    public function init($params)
    {
        parent::init($params);
        if (isset($this->quickform)) {
            $tab = $this->getListValues($params['parameter_id']);
            /*
            var_dump($tab);
            $attrTimezones = array(
                'datasourceOrigin' => 'ajax',
                'availableDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_timezone&action=list',
                'defaultDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_timezone&action=defaultValues&target=host&field=host_location&id=' . $host_id,
                'multiple' => false,
                'linkedObject' => 'centreonGMT'
            );
            */

            $this->element = $this->quickform->addElement(
                'select2',
                'param_'.$params['parameter_id'],
                $params['parameter_name'],
                array(),
                $tab
            );
        }
    }
}