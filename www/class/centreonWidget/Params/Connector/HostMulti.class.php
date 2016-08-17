<?php

require_once "class/centreonWidget/Params/Select2.class.php";

class CentreonWidgetParamsConnectorHostMulti extends CentreonWidgetParamsSelect2
{
    public function __construct($db, $quickform, $userId)
    {
        parent::__construct($db, $quickform, $userId);
    }

    public function getListValues($paramId)
    {
        static $tab;

        if (!isset($tab)) {
            $attrHosts = array (
                'datasourceOrigin' => 'ajax',
                'availableDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_host&action=list',
                'defaultDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_widget_host_monitoring&action=defaultValues&q='.$this->params['widget_id'],
                'multiple' => true
            );

            $tab = $attrHosts;
        }

        return $tab;
    }

}
