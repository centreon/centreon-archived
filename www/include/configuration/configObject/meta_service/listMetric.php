<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
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

	$calcType = array("AVE"=>_("Average"), "SOM"=>_("Sum"), "MIN"=>_("Min"), "MAX"=>_("Max"));

	if (!isset($oreon))
		exit();

	include("./include/common/autoNumLimit.php");

	/*
	 * Smarty template Init
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	/* Access level */
	($centreon->user->access->page($p) == 1) ? $lvl_access = 'w' : $lvl_access = 'r';
	$tpl->assign('mode_access', $lvl_access);

	require_once("./class/centreonDB.class.php");
	$pearDBO = new CentreonDB("centstorage");

	$DBRESULT = $pearDB->query("SELECT * FROM meta_service WHERE meta_id = '".$meta_id."'");

	$meta = $DBRESULT->fetchRow();
	$tpl->assign("meta", array(	"meta" => _("Meta Service"),
								"name" => $meta["meta_name"],
								"calc_type" => $calcType[$meta["calcul_type"]]));
	$DBRESULT->free();

	/*
	 * start header menu
	 */
	$tpl->assign("headerMenu_icone", "<img src='./img/icones/16x16/pin_red.gif'>");
	$tpl->assign("headerMenu_host", _("Host"));
	$tpl->assign("headerMenu_service", _("Services"));
	$tpl->assign("headerMenu_metric", _("Metrics"));
	$tpl->assign("headerMenu_status", _("Status"));
	$tpl->assign("headerMenu_options", _("Options"));

    $aclFrom = "";
    $aclCond = "";
    if (!$oreon->user->admin) {
        $aclFrom = ", $aclDbName.centreon_acl acl ";
        $aclCond = " AND acl.host_id = msr.host_id
                     AND acl.group_id IN (".$acl->getAccessGroupsString().") ";
    }

	$rq = "SELECT DISTINCT msr.*
           FROM `meta_service_relation` msr $aclFrom
           WHERE msr.meta_id = '".$meta_id."'
           $aclCond
           ORDER BY host_id";
    $results = $pearDB->query($rq);
    $ar_relations = array();

	$form = new HTML_QuickForm('Form', 'POST', "?p=".$p);

    /*
    * Construct request
    */
    $in_statement = "";
    $in_statement_append = "";
    while ($row = $results->fetchRow()) {
        $ar_relations[$row['metric_id']][] = array("activate" => $row['activate'], "msr_id" => $row['msr_id']);
        $in_statement .= $in_statement_append . $row['metric_id'];
        $in_statement_append = ",";
    }

    if ($in_statement != "")  {
        $DBRESULTO = $pearDBO->query("SELECT * FROM metrics m, index_data i WHERE m.metric_id IN ($in_statement) and m.index_id=i.id ORDER BY i.host_name, i.service_description, m.metric_name");
    	/*
    	 * Different style between each lines
    	 */
    	$style = "one";
    	/*
    	 * Fill a tab with a mutlidimensionnal Array we put in $tpl
    	 */
    	$elemArr1 = array();
        $i = 0;
        while ($metric = $DBRESULTO->fetchRow()) {
            foreach ($ar_relations[$metric['metric_id']] as $relation) {
                    $moptions = "";
                    $selectedElements = $form->addElement('checkbox', "select[".$relation['msr_id']."]");
                    if ($relation["activate"])
                        $moptions .= "<a href='main.php?p=".$p."&msr_id=".$relation['msr_id']."&o=us&meta_id=".$meta_id."&metric_id=".$metric['metric_id']."'><img src='img/icones/16x16/element_previous.gif' border='0' alt='"._("Disabled")."'></a>&nbsp;&nbsp;";
                    else
                        $moptions .= "<a href='main.php?p=".$p."&msr_id=".$relation['msr_id']."&o=ss&meta_id=".$meta_id."&metric_id=".$metric['metric_id']."'><img src='img/icones/16x16/element_next.gif' border='0' alt='"._("Enabled")."'></a>&nbsp;&nbsp;";
                    $metric["service_description"] = str_replace("#S#", "/", $metric["service_description"]);
                    $metric["service_description"] = str_replace("#BS#", "\\", $metric["service_description"]);
                    $elemArr1[$i] = array(  "MenuClass"=>"list_".$style,
                                                            "RowMenu_select"=>$selectedElements->toHtml(),
                                                            "RowMenu_host"=>htmlentities($metric["host_name"], ENT_QUOTES, "UTF-8"),
                                                            "RowMenu_link"=>"?p=".$p."&o=ws&msr_id=".$relation['msr_id'],
                                                            "RowMenu_service"=>htmlentities($metric["service_description"], ENT_QUOTES, "UTF-8"),
                                                            "RowMenu_metric"=>$metric["metric_name"]." (".$metric["unit_name"].")",
                                                            "RowMenu_status"=>$relation["activate"] ? _("Enabled") : _("Disabled"),
                                                            "RowMenu_options"=>$moptions);
                    $style != "two" ? $style = "two" : $style = "one";
                    $i++;
            }
        }
    }
    if (isset($elemArr1)) {
	    $tpl->assign("elemArr1", $elemArr1);
    } else {
        $tpl->assign("elemArr1", array());
    }

	/*
	 * Different messages we put in the template
	 */
	$tpl->assign('msg', array ("addL1"=>"?p=".$p."&o=as&meta_id=".$meta_id, "addT"=>_("Add"), "delConfirm"=>_("Do you confirm the deletion ?")));

	/*
	 * Element we need when we reload the page
	 */
	$form->addElement('hidden', 'p');
	$form->addElement('hidden', 'meta_id');
	$tab = array ("p" => $p, "meta_id"=>$meta_id);
	$form->setDefaults($tab);

	/*
	 * Toolbar select
	 */
	?>
	<script type="text/javascript">
	function setO(_i) {
		document.forms['form'].elements['o'].value = _i;
	}
	</SCRIPT>
	<?php
	$attrs1 = array(
		'onchange'=>"javascript: " .
				"if (this.form.elements['o1'].selectedIndex == 1 && confirm('"._("Do you confirm the deletion ?")."')) {" .
				" 	setO(this.form.elements['o1'].value); submit();} ");
	$form->addElement('select', 'o1', NULL, array(NULL=>_("More actions..."), "ds"=>_("Delete")), $attrs1);
	$form->setDefaults(array('o1' => NULL));


	$attrs2 = array(
		'onchange'=>"javascript: " .
				"if (this.form.elements['o2'].selectedIndex == 1 && confirm('"._("Do you confirm the deletion ?")."')) {" .
				" 	setO(this.form.elements['o2'].value); submit();} ");
    $form->addElement('select', 'o2', NULL, array(NULL=>_("More actions..."), "ds"=>_("Delete")), $attrs2);
	$form->setDefaults(array('o2' => NULL));

	$o1 = $form->getElement('o1');
	$o1->setValue(NULL);
	$o1->setSelected(NULL);

	$o2 = $form->getElement('o2');
	$o2->setValue(NULL);
	$o2->setSelected(NULL);

	$tpl->assign('limit', $limit);

	/*
	 * Apply a template definition
	 */
	$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);
	$tpl->assign('form', $renderer->toArray());
	$tpl->display("listMetric.ihtml");
?>
