<?php
/*
 * Copyright 2005-2011 MERETHIS
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
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 * SVN : $URL$
 * SVN : $Id$
 *
 */

	if (!isset($oreon)) {
		exit();
	}

	if (!isset($default_poller)) {
		include_once "./include/monitoring/status/Common/default_poller.php";
	}

	$broker = $centreon->broker->getBroker();

?>
// Dynamique
var _sid='<?php echo $sid?>';
<?php if (isset($search_type_host)) { ?>
var _search_type_host='<?php echo $search_type_host?>';
<?php } ?>
<?php if (isset($search_type_service)) { ?>
var _search_type_service='<?php echo $search_type_service?>';
<?php } ?>

var _search = '<?php global $url ; echo ($search ? $search : (isset($centreon->historySearchService[$url]) ? $centreon->historySearchService[$url] : ""));?>';
var _host_search = '<?php global $url ; echo (isset($search_host) && $search_host != "" ? $search_host : (isset($centreon->historySearch[$url]) ? $centreon->historySearch[$url] : "")); ?>';
var _output_search = '<?php global $url ; echo (isset($search_output) && $search_output != "" ? $search_output : (isset($centreon->historySearchOutput[$url]) ? $centreon->historySearchOutput[$url] : "")); ?>';

var _num='<?php echo $num?>';
var _limit='<?php echo $limit?>';
var _sort_type='<?php echo $sort_type?>';
var _order='<?php echo $order?>';
var _date_time_format_status='<?php echo addslashes(_("d/m/Y H:i:s"))?>';
var _o='<?php echo (isset($obis) && $obis) ? $obis : $o;?>';
var _p='<?php echo $p?>';

// Parameters
var _timeoutID = 0;
var _counter = 0;
var _hostgroup_enable = 1;
var _on = 1;
var _time_reload = <?php echo $tM?>;
var _time_live = <?php echo $tFM?>;
var _nb = 0;
var _oldInputFieldValue = '';
var _oldInputHostFieldValue = '';
var _oldInputOutputFieldValue = '';
var _currentInputFieldValue=""; // valeur actuelle du champ texte
var _resultCache=new Object();
var _first = 1;
var _lock = 0;
var _instance = "-1";
var _default_hg = "<?php if (isset($default_hg)) { echo htmlentities($default_hg, ENT_QUOTES, "UTF-8"); } ?>";
var _default_instance = "<?php echo $default_poller?>";
var _nc = 0;
var _poppup = (navigator.appName.substring(0,3) == "Net") ? 1 : 0;
var _popup_no_comment_msg = '<?php echo addslashes(_("Please enter a comment")); ?>';


// Hosts WS For Poppin
var _addrXMLSpanHost = "./include/monitoring/status/Services/xml/<?php print $centreon->broker->getBroker(); ?>/makeXMLForOneHost.php";
var _addrXSLSpanhost = "./include/monitoring/status/Services/xsl/popupForHost.xsl";

// Services WS For Poppin
var _addrXMLSpanSvc = "./include/monitoring/status/Services/xml/<?php print $centreon->broker->getBroker(); ?>/makeXMLForOneService.php";
var _addrXSLSpanSvc = "./include/monitoring/status/Services/xsl/popupForService.xsl";

// Position
var tempX = 0;
var tempY = 0;

if (navigator.appName.substring(0, 3) == "Net") {
	document.captureEvents(Event.MOUSEMOVE);
}
document.onmousemove = position;


function monitoringCallBack(t)
{
	resetSelectedCheckboxes();
	mk_pagination(t.getXmlDocument());
	set_header_title();

    set_displayIMG();
    set_displayPOPUP();
    set_displayGenericInfo();
}

function resetSelectedCheckboxes()
{
	$$('input[type="checkbox"]').each(function(el) {
		if (typeof(_selectedElem) != "undefined" && _selectedElem[encodeURIComponent(el.id)]) {
			el.checked = true;
		}
	});
}

function getXhrC()
{
	if (window.XMLHttpRequest) {
		// Firefox et autres
		var xhrC = new XMLHttpRequest();
	} else if (window.ActiveXObject) { // Internet Explorer
	   	try {
			var xhrC = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
        	var xhrC = new ActiveXObject("Microsoft.XMLHTTP");
        }
	} else { // XMLHttpRequest non support2 par le navigateur
	   	alert("Votre navigateur ne supporte pas les objets XMLHTTPRequest...");
	   	var xhrC = false;
	}
	return xhrC;
}

function addORdelTab(_name) {
	var d = document.getElementsByName('next_check_case');
	if (d[0].checked == true) {
		_nc = 1;
	} else {
		_nc = 0;
	}
	monitoring_refresh();
}

function advanced_options(id) {
	var d = document.getElementById(id);
	if (d) {
		if (d.style.display == 'block') {
			d.style.display='none';
		} else {
			d.style.display='block';
		}
	}
}

function construct_selecteList_ndo_instance(id){
	var displayPoller = <?php echo $oreon->user->access->checkAction("poller_listing");?>

	if (!displayPoller) {
		return null;
	}
	if (!document.getElementById("select_instance")){
		var select_index = new Array();
		var _select_instance = document.getElementById(id);
		if (_select_instance == null) {
			return;
		}
		var _select = document.createElement("select");
		_select.name = "select_instance";
		_select.id = "select_instance";
		_select.onchange = function() {
			_instance = this.value;
			_default_instance = this.value;
			xhr = new XMLHttpRequest();
			xhr.open('GET','./include/monitoring/status/Common/updateContactParam.php?uid=<?php echo $oreon->user->user_id; ?>&instance_id='+this.value, true);
			xhr.send(null);
			xhr.onreadystatechange = function() { monitoring_refresh(); };
		};
		var k = document.createElement('option');
		k.value= -1;
		var l = document.createTextNode("");
		k.appendChild(l);
		_select.appendChild(k);
		var i = 1;

<?php
    $pollerArray = $oreon->user->access->getPollers();
    /** *************************************
     * Get instance listing
     */
    if ($broker == "broker") {
    	if ($oreon->user->admin || !count($pollerArray)) {
	        $instanceQuery = "SELECT instance_id, name FROM `instances` ORDER BY name";
		} else {
		    $instanceQuery = "SELECT instance_id, name  ".
		    				 "FROM `instances` WHERE name IN (". $oreon->user->access->getPollerString('NAME') .") ORDER BY name";
		}
		$DBRESULT = $pearDBO->query($instanceQuery);
   		 while ($nagios_server = $DBRESULT->fetchRow())	{   ?>
			var m = document.createElement('option');
			m.value= "<?php echo $nagios_server["instance_id"]; ?>";
			_select.appendChild(m);
			var n = document.createTextNode("<?php echo $nagios_server["name"] . "  "; ?>   ");
			m.appendChild(n);
			_select.appendChild(m);
			select_index["<?php echo $nagios_server["instance_id"]; ?>"] = i;
			i++;
	<?php }	?>
			_select.selectedIndex = select_index[_default_instance];
			_select_instance.appendChild(_select);
		}
		<?php
    } else {
		if ($oreon->user->admin || !count($pollerArray)) {
	        $instanceQuery = "SELECT instance_id, instance_name FROM `".getNDOPrefix()."instances` ORDER BY instance_name";
		} else {
		    $instanceQuery = "SELECT instance_id, instance_name  ".
		    				 "FROM `".getNDOPrefix()."instances` WHERE instance_name IN (". $oreon->user->access->getPollerString('NAME') .") ORDER BY instance_name";
		}
		$DBRESULT = $pearDBndo->query($instanceQuery);
		while ($nagios_server = $DBRESULT->fetchRow())	{
?>
			var m = document.createElement('option');
			m.value= "<?php echo $nagios_server["instance_id"]; ?>";
			_select.appendChild(m);
			var n = document.createTextNode("<?php echo $nagios_server["instance_name"] . "  "; ?>   ");
			m.appendChild(n);
			_select.appendChild(m);
			select_index["<?php echo $nagios_server["instance_id"]; ?>"] = i;
			i++;
	<?php }	?>
			_select.selectedIndex = select_index[_default_instance];
			_select_instance.appendChild(_select);
		}
	<?php
    }
    ?>
}

function construct_HostGroupSelectList(id) {
	if (!document.getElementById("hostgroups")) {
		var select_index = new Array();
		var _select_hostgroups = document.getElementById(id);
		if (_select_hostgroups == null) {
			return;
		}
		var _select = document.createElement("select");
		_select.name = "hostgroups";
		_select.id = "hostgroups";
		_select.onchange = function() {
			_default_hg = this.value;
			xhr = new XMLHttpRequest();
			xhr.open('GET','./include/monitoring/status/Common/updateContactParamHostGroups.php?uid=<?php echo $oreon->user->user_id; ?>&hostgroups='+this.value, true);
			xhr.send(null);
			xhr.onreadystatechange = function() {
				monitoring_refresh();
			};
		};
		var k = document.createElement('option');
		k.value= "0";
		_select.appendChild(k);
		var i = 1;
<?php
		$hgNdo = array();
		$hgBrk = array();
		if ($broker == 'broker') {
		    $acldb = $pearDBO;
		} else {
            $acldb = new CentreonDB("ndo");
		}
		if (!$oreon->user->access->admin) {
			$query = "SELECT DISTINCT hg.hg_alias, hg.hg_name AS name
				  FROM hostgroup hg, acl_resources_hg_relations arhr
				  WHERE hg.hg_id = arhr.hg_hg_id
                                  AND arhr.acl_res_id IN (".$oreon->user->access->getResourceGroupsString().")
                                  AND hg.hg_activate = '1'
			          AND hg.hg_id in (SELECT hostgroup_hg_id
			            		   FROM hostgroup_relation
			            		   WHERE host_host_id IN (".$oreon->user->access->getHostsString("ID", $acldb)."))";
			$DBRESULT = $pearDB->query($query);
			while ($data = $DBRESULT->fetchRow()) {
				$hgNdo[$data["name"]] = 1;
				$hgBrk[$data["name"]] = 1;
			}
			$DBRESULT->free();
			unset($data);
		}

		if ($broker == 'broker') {
			$DBRESULT = $pearDBO->query("SELECT DISTINCT `name`, hostgroups.hostgroup_id FROM `hostgroups`, `hosts_hostgroups` WHERE hostgroups.hostgroup_id = hosts_hostgroups.hostgroup_id AND name NOT LIKE 'meta_%' ORDER BY `name`");
		} else {
			$DBRESULT = $pearDB->query("SELECT DISTINCT `hg_name` as name, `hg_alias` as alias , `hg_id` as hostgroup_id FROM `hostgroup` ORDER BY `name`");
		}
		while ($hostgroups = $DBRESULT->fetchRow()) {
			if ($broker == 'broker') {
				if ($oreon->user->access->admin || ($oreon->user->access->admin == 0 && isset($hgBrk[$hostgroups["name"]]))) {
				    if (!isset($tabHG)) {
				        $tabHG = array();
				    }
				    if (!isset($tabHG[$hostgroups["name"]])) {
				        $tabHG[$hostgroups["name"]] = "";
				    } else {
				        $tabHG[$hostgroups["name"]] .= ",";
				    }
                    $tabHG[$hostgroups["name"]] = $hostgroups["hostgroup_id"];
	 		    }
			} else {
				if ($oreon->user->access->admin || ($oreon->user->access->admin == 0 && isset($hgNdo[$hostgroups["name"]]))) { ?>
					var m = document.createElement('option');
					m.value= "<?php echo addslashes($hostgroups['alias']); ?>";
					_select.appendChild(m);
					var n = document.createTextNode("<?php echo $hostgroups["name"]; ?>   ");
					m.appendChild(n);
					_select.appendChild(m);
					select_index["<?php echo addslashes($hostgroups['alias']); ?>"] = i;
					i++;
	<?php 		}
			}
		}

		if ($broker == 'broker') {
			if (isset($tabHG)) {
				foreach ($tabHG as $name => $id) {
	                ?>
	                var m = document.createElement('option');
						m.value= "<?php echo $id; ?>";
						_select.appendChild(m);
						var n = document.createTextNode("<?php echo $name; ?>   ");
						m.appendChild(n);
						_select.appendChild(m);
						select_index["<?php echo $id; ?>"] = i;
						i++;
					<?php
	            }
			}
		}
?>
		if (typeof(_default_hg) != "undefined") {
			_select.selectedIndex = select_index[_default_hg];
		}
		_select_hostgroups.appendChild(_select);
	}
}


function viewDebugInfo(_str) {
	if (_debug)	{
		_nb = _nb + 1;
		var mytable=document.getElementById("debugtable")
		var newrow=mytable.insertRow(0) //add new row to end of table
		var newcell=newrow.insertCell(0) //insert new cell to row
		newcell.innerHTML='<td>line:' + _nb + ' ' + _str + '</td>';
	}
}

function change_page(page_number) {
	_selectedElem = new Array();
	viewDebugInfo('change page');
	_num = page_number;
	monitoring_refresh();
	pagination_changed();
	set_page(page_number);
}

function change_type_order(_type) {
	if (_sort_type != _type){
		_sort_type = _type;
		monitoring_refresh();
	}
}

function change_order(_odr) {
	if (_order == 'ASC'){
		_order = 'DESC';
	} else {
		_order = 'ASC';
	}
	monitoring_refresh();
}

function change_limit(l) {
	_limit= l;
	pagination_changed();
	monitoring_refresh();
	var _sel1 = document.getElementById('l1');
	for(i=0 ; _sel1[i] && _sel1[i].value != l ; i++)
		;
	_sel1.selectedIndex = i;
	set_limit(l);
}

var _numRows = 0;

function getVar (nomVariable) {
	var infos = location.href.substring(location.href.indexOf("?")+1, location.href.length)+"&";
	if (infos.indexOf("#")!=-1)
	infos = infos.substring(0,infos.indexOf("#"))+"&";
	var variable=''
	{
		nomVariable = nomVariable + "=";
		var taille = nomVariable.length;
		if (infos.indexOf(nomVariable)!=-1)
		variable = infos.substring(infos.indexOf(nomVariable)+taille,infos.length).substring(0,infos.substring(infos.indexOf(nomVariable)+taille,infos.length).indexOf("&"))
	}
	return variable;
}

function mk_img(_src, _alt)	{
	var _img = document.createElement("img");
  	_img.src = _src;
  	_img.alt = _alt;
  	_img.title = _alt;
  	if (_img.complete){
  		_img.alt = _alt;
  	} else {
  		_img.alt = "";
  	}
	return _img;
}

function mk_imgOrder(_src, _alt)	{
	var _img = document.createElement("img");
  	_img.src = _src;
  	_img.alt = _alt;
  	_img.title = _alt;
  	_img.style.paddingLeft = '10px';
  	_img.style.marginBottom = '0.5px';
  	if (_img.complete){
  		_img.alt = _alt;
  	} else {
  		_img.alt = "";
  	}
	return _img;
}

function mk_pagination(resXML){
	viewDebugInfo('mk pagination');

	var flag = 0;
	var infos = resXML.getElementsByTagName('i');

	var _nr = infos[0].getElementsByTagName('numrows')[0].firstChild.data;
	var _nl = infos[0].getElementsByTagName("limit")[0].firstChild.data;
	var _nn = infos[0].getElementsByTagName("num")[0].firstChild.data;

	if (_numRows != _nr) {
		_numRows = _nr;
		flag = 1;
	}
	if (_num != _nn) {
		_num = _nn;
		flag = 1;
	}
	if (_limit != _nl) {
		_limit = _nl;
		flag = 1;
	}
	if (flag == 1) {
		pagination_changed();
	}
}

function mk_paginationFF(resXML){
	viewDebugInfo('mk pagination');

	var flag = 0;
	var infos = resXML.getElementsByTagName('i');
	if (infos[0]) {
		var _nr = infos[0].getElementsByTagName('numrows')[0].firstChild.data;
		var _nl = infos[0].getElementsByTagName("limit")[0].firstChild.data;
		var _nn = infos[0].getElementsByTagName("num")[0].firstChild.data;

		if (_numRows != _nr){
			_numRows = _nr;
			flag = 1;
		}
		if (_num != _nn){
			_num = _nn;
			flag = 1;
		}
		if (_limit != _nl){
			_limit = _nl;
			flag = 1;
		}
		if (flag == 1){
			pagination_changed();
		}
	}
}

function pagination_changed(){
	viewDebugInfo('pagination_changed');

	// compute Max Page
	var page_max = 0;
	if ((_numRows % _limit) == 0)	{
		page_max =  Math.round( (_numRows / _limit));
	} else{
		page_max =  Math.round( (_numRows / _limit) + 0.5);
	}

	if (_num >= page_max && _numRows && _num > 0){
		viewDebugInfo('!!num!!'+_num);
		viewDebugInfo('!!max!!'+page_max);
		_num = Number(page_max) - 1;
		viewDebugInfo('new:'+_num);
		monitoring_refresh();
	}

	var p = getVar('p');
	var o = getVar('o');
	var search = '' + getVar('search');
	var _numnext = Number(_num) + 1;
	var _numprev = Number(_num) - 1;

<?php
	for ($i = 1; $i <= 2; $i++) { ?>
	var _img_previous<?php echo $i; ?> 	= mk_img("./img/icones/16x16/arrow_left_blue.gif", "previous");
	var _img_next<?php echo $i; ?> 		= mk_img("./img/icones/16x16/arrow_right_blue.gif", "next");
	var _img_first<?php echo $i; ?> 	= mk_img("./img/icones/16x16/arrow_left_blue_double.gif", "first");
	var _img_last<?php echo $i; ?> 		= mk_img("./img/icones/16x16/arrow_right_blue_double.gif", "last");

	var _linkaction_right<?php echo $i; ?> = document.createElement("a");
	_linkaction_right<?php echo $i; ?>.href = '#' ;
	_linkaction_right<?php echo $i; ?>.indice = _numnext;
	_linkaction_right<?php echo $i; ?>.onclick=function(){change_page(Number(this.indice))}
	_linkaction_right<?php echo $i; ?>.appendChild(_img_next<?php echo $i; ?>);

	var _linkaction_last<?php echo $i; ?> = document.createElement("a");
	_linkaction_last<?php echo $i; ?>.href = '#' ;
	_linkaction_last<?php echo $i; ?>.indice = page_max - 1;
	_linkaction_last<?php echo $i; ?>.onclick=function(){change_page(Number(this.indice))}
	_linkaction_last<?php echo $i; ?>.appendChild(_img_last<?php echo $i; ?>);

	var _linkaction_first<?php echo $i; ?> = document.createElement("a");
	_linkaction_first<?php echo $i; ?>.href = '#' ;
	_linkaction_first<?php echo $i; ?>.indice = 0;
	_linkaction_first<?php echo $i; ?>.onclick=function(){change_page(Number(this.indice))}
	_linkaction_first<?php echo $i; ?>.appendChild(_img_first<?php echo $i; ?>);

	var _linkaction_left<?php echo $i; ?> = document.createElement("a");
	_linkaction_left<?php echo $i; ?>.href = '#' ;
	_linkaction_left<?php echo $i; ?>.indice = _numprev;
	_linkaction_left<?php echo $i; ?>.onclick=function(){change_page(Number(this.indice))}
	_linkaction_left<?php echo $i; ?>.appendChild(_img_previous<?php echo $i; ?>);

	var _pagination<?php echo $i; ?> = document.getElementById('pagination<?php echo $i; ?>');

	_pagination<?php echo $i; ?>.innerHTML ='';
	if (_num > 0){
		_pagination<?php echo $i; ?>.appendChild(_linkaction_first<?php echo $i; ?>);
		_pagination<?php echo $i; ?>.appendChild(_linkaction_left<?php echo $i; ?>);
	}
<?php }

	/*
	 * Page Number
	 */

for ($i = 1; $i <= 2; $i++) { ?>
	var istart = 0;

	for (i = 5, istart = _num; istart && i > 0 && istart > 0; i--) {
		istart--;
	}

	for (i2 = 0, iend = _num; ( iend <  (_numRows / _limit -1)) && ( i2 < (5 + i)); i2++) {
		iend++;
	}

	for (i = istart; i <= iend && page_max > 1; i++) {
		var span_space = document.createElement("span");
		span_space.innerHTML = '&nbsp;';
		_pagination<?php echo $i; ?>.appendChild(span_space);

		var _linkaction_num = document.createElement("a");
  		_linkaction_num.href = '#' ;
  		_linkaction_num.indice = i;
  		_linkaction_num.onclick=function(){change_page(this.indice)};
		_linkaction_num.innerHTML = parseInt(i + 1);
		_linkaction_num.className = "otherPageNumber";

		if (i == _num)
			_linkaction_num.className = "currentPageNumber";
		_pagination<?php echo $i; ?>.appendChild(_linkaction_num);

		var span_space = document.createElement("span");
		span_space.innerHTML = '&nbsp;';
		_pagination<?php echo $i; ?>.appendChild(span_space);
	}

	if (_num < page_max - 1){
		_pagination<?php echo $i; ?>.appendChild(_linkaction_right<?php echo $i; ?>);
		_pagination<?php echo $i; ?>.appendChild(_linkaction_last<?php echo $i; ?>);
	}
<?php
} ?>

	var _sel1 = document.getElementById('sel1');
	_sel1.innerHTML ='';

	var _sel2 = document.getElementById('sel2');
	_sel2.innerHTML ='';

	var sel1 = document.createElement('select');
	sel1.name = 'l';
	sel1.id = 'l1';
	sel1.onchange = function() { change_limit(this.value) };

	var sel2 = document.createElement('select');
	sel2.name = 'l';
	sel2.id = 'l2';
	sel2.onchange = function() { change_limit(this.value) };

	var _max = 100;
	if (_limit > 100) {
		_max = 1000;
	}

	var _index;
	for (i = 10, j = 0 ; i <= 100 ; i += 10, j++) {
		var k = document.createElement('option');
		k.value = i;
		sel1.appendChild(k);
		if (_limit == i) {
			_index = j;
		}
		var l = document.createTextNode(i);
		k.appendChild(l);
	}
	for (i = 200; i <= 500 ; i += 100, j++) {
		var k = document.createElement('option');
		k.value = i;
		sel1.appendChild(k);
		if (_limit == i) {
			_index = j;
		}
		var l = document.createTextNode(i);
		k.appendChild(l);
	}

	for (i = 10, j = 0; i <= 100 ; i += 10, j++) {
		var k = document.createElement('option');
		k.value = i;
		sel2.appendChild(k);
		if (_limit == i) {
			_index = j;
		}
		var l = document.createTextNode(i);
		k.appendChild(l);
	}
	for (i = 200; i <= 500 ; i += 100, j++) {
		var k = document.createElement('option');
		k.value = i;
		sel2.appendChild(k);
		if (_limit == i) {
			_index = j;
		}
		var l = document.createTextNode(i);
		k.appendChild(l);
	}

	sel1.selectedIndex = _index;
	_sel1.appendChild(sel1);

	sel2.selectedIndex = _index;
	_sel2.appendChild(sel2);
}

function escapeURI(La) {
	if (encodeURIComponent) {
    	return encodeURIComponent(La);
  	}
  	if (escape) {
  	  	return escape(La)
  	}
}

function mainLoop() {
 	_currentInputField = document.getElementById('input_search');
  	if (document.getElementById('input_search') && document.getElementById('input_search').value) {
  		_currentInputFieldValue = document.getElementById('input_search').value;
  	} else {
  		_currentInputFieldValue = "";
  	}

 	_currentInputHostField = document.getElementById('host_search');
  	if (document.getElementById('host_search') && document.getElementById('host_search').value) {
  		_currentInputHostFieldValue = document.getElementById('host_search').value;
  	} else {
  		_currentInputHostFieldValue = "";
  	}

 	_currentInputOutputField = document.getElementById('output_search');
  	if (document.getElementById('output_search') && document.getElementById('output_search').value) {
  		_currentInputOutputFieldValue = document.getElementById('output_search').value;
	} else {
		_currentInputOutputFieldValue = "";
	}

  	if (((_currentInputFieldValue.length >= 3 || _currentInputFieldValue.length == 0) && _oldInputFieldValue != _currentInputFieldValue)
  		|| ((_currentInputHostFieldValue.length >= 3 || _currentInputHostFieldValue.length == 0) && _oldInputHostFieldValue != _currentInputHostFieldValue)
  		|| ((_currentInputOutputFieldValue.length >= 3 || _currentInputOutputFieldValue.length == 0) && _oldInputOutputFieldValue != _currentInputOutputFieldValue)){

    	if (!_lock) {
			set_search(escapeURI(_currentInputFieldValue));
			_search = _currentInputFieldValue;
			set_search_host(escapeURI(_currentInputHostFieldValue));
			_host_search = _currentInputHostFieldValue;
			set_search_output(escapeURI(_currentInputOutputFieldValue));
			_output_search = _currentInputOutputFieldValue;

			monitoring_refresh();

			if (isset(_currentInputFieldValue.className) && _currentInputFieldValue.length >= 3) {
				_currentInputField.className = "search_input_active";
			} else if (isset(_currentInputFieldValue.className)) {
				_currentInputField.className = "search_input";
			}
			if (isset(_currentInputHostFieldValue.className) && _currentInputHostFieldValue.length >= 3) {
				_currentInputHostField.className = "search_input_active";
			} else if (isset(_currentInputHostFieldValue.className)) {
				_currentInputHostField.className = "search_input";
			}
			if (isset(_currentInputOutputFieldValue.className) && _currentInputOutputFieldValue.length >= 3) {
				_currentInputOutputField.className = "search_input_active";
			} else if (isset(_currentInputOutputFieldValue.className)) {
				_currentInputOutputField.className = "search_input";
			}
		}
	}
	_oldInputFieldValue = _currentInputFieldValue;
	_oldInputHostFieldValue = _currentInputHostFieldValue;
	_oldInputOutputFieldValue = _currentInputOutputFieldValue;

	setTimeout("mainLoop()",250);
}

// History Functions

function set_limit(limit)	{
	var xhrM = getXhrC();
	xhrM.open("POST","./include/monitoring/status/Common/setHistory.php",true);
	xhrM.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
	_var = "sid=<?php echo $sid; ?>&limit="+limit+"&url=<?php echo $url; ?>";
	xhrM.send(_var);
}

function set_search(search)	{
	var xhrM = getXhrC();
	xhrM.open("POST","./include/monitoring/status/Common/setHistory.php",true);
	xhrM.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
	_var = "sid=<?php echo $sid; ?>&search="+search+"&url=<?php echo $url; ?>";
	xhrM.send(_var);
}

function set_search_host(search_host) {
	var xhrM = getXhrC();
	xhrM.open("POST","./include/monitoring/status/Common/setHistory.php",true);
	xhrM.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
	_var = "sid=<?php echo $sid; ?>&search_host="+search_host+"&url=<?php echo $url; ?>";
	xhrM.send(_var);
}

function set_search_output(search_output) {
	var xhrM = getXhrC();
	xhrM.open("POST","./include/monitoring/status/Common/setHistory.php",true);
	xhrM.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
	_var = "sid=<?php echo $sid; ?>&search_output="+search_output+"&url=<?php echo $url; ?>";
	xhrM.send(_var);
}

function set_page(page)	{
	var xhrM = getXhrC();
	xhrM.open("POST","./include/monitoring/status/Common/setHistory.php",true);
	xhrM.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
	_var = "sid=<?php echo $sid; ?>&page="+page+"&url=<?php echo $url; ?>";
	xhrM.send(_var);
}

// Popin images
function set_displayIMG() {
        jQuery('a .graph-volant').mouseenter(func_displayIMG);
        jQuery('a .graph-volant').mouseleave(func_hideIMG);
}

var func_displayIMG = function(event) {
        var NewImage = new Image();

        jQuery('.img_volante').html('<img src="img/misc/ajax-loader.gif" />');
        jQuery('.img_volante').css('left', event.pageX + 20);
        jQuery('.img_volante').css('top', (jQuery(window).height() / 2) - (jQuery('.img_volante').height() / 2));
        jQuery('.img_volante').show();

        var elements = $(this).id.split('-');
        var NewImageAlt = 'graph popup' + '&index=' + elements[0] + '&time=<?php print time(); ?>';
        NewImage.onload = function(){
                jQuery('.img_volante').html('<img style="display: none" src="' + encodeURI(this.src) + '" alt="' + NewImageAlt + '" title="' + NewImageAlt + '" />');
                <?php   if ($centreon->user->get_js_effects() > 0) { ?>
                jQuery('.img_volante').animate({width: this.width, height: this.height, top: (jQuery(window).height() / 2) - (this.height / 2)}, "slow");
                jQuery('.img_volante img').fadeIn(1000);
                <?php } else { ?>
                jQuery('.img_volante').css('left', jQuery('.img_volante').attr('left'));
                jQuery('.img_volante').css('top', (jQuery(window).height() / 2) - (this.height / 2));
                jQuery('.img_volante img').show();
                <?php } ?>
        };
        NewImage.src = 'include/views/graphs/generateGraphs/generateImage.php?session_id='+ _sid +'&index='+ elements[0];
        if (NewImage.complete) {
                jQuery('.img_volante').html('<img style="display: none" src="' + NewImage.src + '" alt="' + NewImageAlt + '" title="' + NewImageAlt + '" />');
                <?php   if ($centreon->user->get_js_effects() > 0) { ?>
                jQuery('.img_volante').animate({width: NewImage.width, height: NewImage.height, top: (jQuery(window).height() / 2) - (NewImage.height / 2)}, "slow");
                jQuery('.img_volante img').fadeIn(1000);
                <?php } else { ?>
                jQuery('.img_volante').css('left', jQuery('.img_volante').attr('left'));
                jQuery('.img_volante').css('top', (jQuery(window).height() / 2) - (NewImage.height / 2));
                jQuery('.img_volante img').show();
                <?php } ?>
        }
};

var func_hideIMG = function(event) {
        jQuery('.img_volante').hide();
        jQuery('.img_volante').empty();
        jQuery('.img_volante').css('width', 'auto');
        jQuery('.img_volante').css('height', 'auto');
};

// Poppin Function
var popup_counter = {};

function set_displayPOPUP() {
        jQuery('.link_popup_volante').mouseenter(func_displayPOPUP);
        jQuery('.link_popup_volante').mouseleave(func_hidePOPUP);
}

var func_popupXsltCallback = function(trans_obj) {
        var target_element = trans_obj.getTargetElement();
        if (popup_counter[target_element] == 0) {
                return ;
        }

        jQuery('.popup_volante .container-load').empty();
<?php   if ($centreon->user->get_js_effects() > 0) { ?>
        jQuery('.popup_volante').stop(true, true).animate({width: jQuery('#' + target_element).width(), height: jQuery('#' + target_element).height(),
                             top: (jQuery(window).height() / 2) - (jQuery('#' + target_element).height() / 2)}, "slow");
        jQuery('#' + target_element).stop(true, true).fadeIn(1000);
<?php } else { ?>
        jQuery('.popup_volante').css('left', jQuery('#' + target_element).attr('left'));
        jQuery('.popup_volante').css('top', (jQuery(window).height() / 2) - (jQuery('#' + target_element).height() / 2));
        jQuery('#' + target_element).show();
<?php } ?>
};

var func_displayPOPUP = function(event) {
        var position = jQuery('#' + $(this).id).offset();

        if (jQuery('#popup-container-display-' + $(this).id).length == 0) {
                popup_counter['popup-container-display-' + $(this).id] = 1;
                jQuery('.popup_volante').append('<div id="popup-container-display-' + $(this).id + '" style="display: none"></div>');
        } else {
                popup_counter['popup-container-display-' + $(this).id] += 1;
        }
        jQuery('.popup_volante .container-load').html('<img src="img/misc/ajax-loader.gif" />');
        jQuery('.popup_volante').css('left', position.left + jQuery('#' + $(this).id).width() + 10);
        jQuery('.popup_volante').css('top', (jQuery(window).height() / 2) - (jQuery('.img_volante').height() / 2));
        jQuery('.popup_volante').show();

        var elements = $(this).id.split('-');
        var proc_popup = new Transformation();
        proc_popup.setCallback(func_popupXsltCallback);
        if (elements[0] == "host") {
                proc_popup.setXml(_addrXMLSpanHost+"?"+'&sid='+_sid+'&host_id=' + elements[1]);
                proc_popup.setXslt(_addrXSLSpanhost);
        } else {
                proc_popup.setXml(_addrXMLSpanSvc+"?"+'&sid='+_sid+'&svc_id=' + elements[1] + '_' + elements[2]);
                proc_popup.setXslt(_addrXSLSpanSvc);
        }
        proc_popup.transform('popup-container-display-' + $(this).id);
};

var func_hidePOPUP = function(event) {
        popup_counter['popup-container-display-' + $(this).id] -= 1;
        jQuery('.popup_volante .container-load').empty();
        jQuery('#popup-container-display-' + $(this).id).hide();
        jQuery('.popup_volante').hide();
        jQuery('.popup_volante').css('width', 'auto');
        jQuery('.popup_volante').css('height', 'auto');
};

function set_displayGenericInfo() {
        jQuery('.link_generic_info_volante').mouseenter(func_displayGenericInfo);
        // Same func. no need for a new one
        jQuery('.link_generic_info_volante').mouseleave(func_hidePOPUP);
}

/* Use 'id' attribute to get element */
/* Use 'name' attribute to get xml/xsl infos */
var func_displayGenericInfo = function(event) {
        var position = jQuery('#' + $(this).id).offset();

        if (jQuery('#popup-container-display-' + $(this).id).length == 0) {
                popup_counter['popup-container-display-' + $(this).id] = 1;
                jQuery('.popup_volante').append('<div id="popup-container-display-' + $(this).id + '" style="display: none"></div>');
        } else {
                popup_counter['popup-container-display-' + $(this).id] += 1;
        }
        jQuery('.popup_volante .container-load').html('<img src="img/misc/ajax-loader.gif" />');
        jQuery('.popup_volante').css('left', position.left + jQuery('#' + $(this).id).width() + 10);
        jQuery('.popup_volante').css('top', (jQuery(window).height() / 2) - (jQuery('.img_volante').height() / 2));
        jQuery('.popup_volante').show();

        var elements = $(this).name.split('|');
        var proc_popup = new Transformation();
        proc_popup.setCallback(func_popupXsltCallback);
        proc_popup.setXml(elements[0]);
        proc_popup.setXslt(elements[1]);
        proc_popup.transform('popup-container-display-' + $(this).id);
};

// Monitoring Refresh management Options

function monitoring_play()	{
	document.getElementById('JS_monitoring_play').style.display = 'none';
	document.getElementById('JS_monitoring_pause').style.display = 'block';
	document.getElementById('JS_monitoring_pause_gray').style.display = 'none';
	document.getElementById('JS_monitoring_play_gray').style.display = 'block';
	_on = 1;
	initM(<?php echo $tM?>,"<?php echo $sid?>","<?php echo $o?>");
}

function monitoring_pause()	{
	document.getElementById('JS_monitoring_play').style.display = 'block';
	document.getElementById('JS_monitoring_pause_gray').style.display = 'block';
	document.getElementById('JS_monitoring_play_gray').style.display = 'none';
	document.getElementById('JS_monitoring_pause').style.display='none';
	_on = 0;
	window.clearTimeout(_timeoutID);
}

function monitoring_refresh()	{
	_tmp_on = _on;
	_time_live = _time_reload;
	_on = 1;

	window.clearTimeout(_timeoutID);
	initM(<?php echo $tM?>,"<?php echo $sid?>",_o);
	_on = _tmp_on;
	viewDebugInfo('refresh');
}

function initM(_time_reload, _sid, _o) {
	construct_selecteList_ndo_instance('instance_selected');
	if (_hostgroup_enable == 1) {
		construct_HostGroupSelectList('hostgroups_selected');
	}
	if (!document.getElementById('debug')) {
		var _divdebug = document.createElement("div");
		_divdebug.id = 'debug';
		var _debugtable = document.createElement("table");
		_debugtable.id = 'debugtable';
		var _debugtr = document.createElement("tr");
		_debugtable.appendChild(_debugtr);
		_divdebug.appendChild(_debugtable);
		_header = document.getElementById('header');
		_header.appendChild(_divdebug);
	}

	if (_first) {
		mainLoop();
		_first = 0;
	}

	_time=<?php echo $time?>;

	if (_on) {
		goM(_time_reload,_sid,_o);
	}
}

// Windows size Management
function position(e) {
	tempX = (navigator.appName.substring(0,3) == "Net") ? e.pageX : event.x+document.body.scrollLeft;
	tempY = (navigator.appName.substring(0,3) == "Net") ? e.pageY : event.y+document.body.scrollTop;
}

// Multi Select Management
function putInSelectedElem(id) {
	_selectedElem[encodeURIComponent(id)] = encodeURIComponent(id);
}

function removeFromSelectedElem(id) {
	if (typeof(_selectedElem[encodeURIComponent(id)]) != 'undefined') {
		_selectedElem[encodeURIComponent(id)] = undefined;
	}
}

// Misc
function isset(variable) {
	if ( typeof( window[variable] ) != "undefined" ) {
		return true;
	} else {
		return false;
	}
}
