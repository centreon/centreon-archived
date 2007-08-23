<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
Developped by : Cedrick Facon

The Software is provided to you AS IS and WITH ALL FAULTS.
OREON makes no representation and gives no warranty whatsoever,
whether express or implied, and without limitation, with regard to the quality,
safety, contents, performance, merchantability, non-infringement or suitability for
any particular or intended purpose of the Software found on the OREON web site.
In no event will OREON be liable for any direct, indirect, punitive, special,
incidental or consequential damages however they may arise and even if OREON has
been previously advised of the possibility of such damages.

For information : contact@oreon-project.org
*/

	if (!isset($oreon))
		exit();

	$tS = $oreon->optGen["AjaxTimeReloadStatistic"] * 1000;
	$tM = $oreon->optGen["AjaxTimeReloadMonitoring"] * 1000;
	$oreon->optGen["AjaxFirstTimeReloadStatistic"] == 0 ? $tFS = 10 : $tFS = $oreon->optGen["AjaxFirstTimeReloadStatistic"] * 1000;
	$oreon->optGen["AjaxFirstTimeReloadMonitoring"] == 0 ? $tFM = 10 : $tFM = $oreon->optGen["AjaxFirstTimeReloadMonitoring"] * 1000;
	$sid = session_id();
	$time = time();
	
?>
<SCRIPT LANGUAGE="JavaScript">
var _debug = 1;

var _search = '<?=$search?>';
var _sid='<?=$sid?>';
var _search_type_host='<?=$search_type_host?>';
var _search_type_service='<?=$search_type_service?>';
var _num='<?=$num?>';
var _limit='<?=$limit?>';
var _sort_type='<?=$sort_type?>';
var _order='<?=$order?>';
var _date_time_format_status='<?=$lang["date_time_format_status"]?>';
var _o='<?=$o?>';

var _addrXML = "./include/monitoring/engine/MakeXML_Ndo.php?";
var _addrXSL = "./include/monitoring/status/status-ndo/templates/service.xsl";
var _timeoutID = 0;
var _on = 1;
var _time_reload = <?=$tM?>;
var _time_live = <?=$tFM?>;
var _nb = 0;
var _oldInputFieldValue = '<?=$search?>';
var _currentInputFieldValue=""; // valeur actuelle du champ texte
var _resultCache=new Object();
var _first = 1;
var _lock = 0;

function viewDebugInfo(_str){
	if(_debug)
	{
		_nb = _nb + 1;
		var mytable=document.getElementById("debugtable")
		var newrow=mytable.insertRow(0) //add new row to end of table
		var newcell=newrow.insertCell(0) //insert new cell to row
		newcell.innerHTML='<td>line:' + _nb + ' ' + _str + '</td>';
	}
}

function change_page(page_number){
	_num = page_number;
	monitoring_refresh();
	pagination_changed();
}
function change_limit(l){
	_limit= l;
	monitoring_refresh();
	pagination_changed();
	var _sel1 = document.getElementById('sel1');

for(i=0;_sel1[i] && _sel1[i].value != l;i++)
;

	_sel1.selectedIndex = i;


	viewDebugInfo('index=>'+i)
}

var _numRows = 0;
var _limit = 10;
var _num = 0;

function removeAllLine(table)
{
	rows = table.getElementsByTagName("tr");
	while(rows && rows.length > 1)
		table.deleteRow(-1);
}

 function getVar (nomVariable)
 {
	 var infos = location.href.substring(location.href.indexOf("?")+1, location.href.length)+"&"
	 if (infos.indexOf("#")!=-1)
	 infos = infos.substring(0,infos.indexOf("#"))+"&"
	 var variable=''
	 {
	 nomVariable = nomVariable + "="
	 var taille = nomVariable.length
	 if (infos.indexOf(nomVariable)!=-1)
	 variable = infos.substring(infos.indexOf(nomVariable)+taille,infos.length).substring(0,infos.substring(infos.indexOf(nomVariable)+taille,infos.length).indexOf("&"))
	 }
	 return variable
 }
 
function mk_img(_src, _alt)
{
	var _img = document.createElement("img");
  	_img.src = _src;
  	_img.alt = _alt;
  	_img.title = _alt;
	return _img;
}

function mk_pagination(resXML){
	var flag = 0;
	var infos = resXML.getElementsByTagName("i");
	var _nr = infos[0].getElementsByTagName("numrows")[0].firstChild.nodeValue;
	var _nl = infos[0].getElementsByTagName("limit")[0].firstChild.nodeValue;
	var _nn = infos[0].getElementsByTagName("num")[0].firstChild.nodeValue;

	if(_numRows != _nr){
		_numRows = _nr;
		flag = 1;
	}
	if(_num != _nn){
		_num = _nn;
		flag = 1;
	}
	if(_limit != _nl){
		_limit = _nl;
		flag = 1;
	}

	if(flag == 1){
	pagination_changed();	
	}
}

function pagination_changed(){

	var p = getVar('p');
	var o = getVar('o');
	var search = '' + getVar('search');
	var _numnext = _num + 1;
	var _numprev = _num - 1;
	var _img_previous = mk_img("./img/icones/16x16/arrow_left_blue.gif", "previous");
	var _img_next = mk_img("./img/icones/16x16/arrow_right_blue.gif", "next");

	var _linkaction_right = document.createElement("a");
	_linkaction_right.href = '#' ;
	_linkaction_right.indice = _numnext;
	_linkaction_right.onclick=function(){change_page(this.indice)}
	_linkaction_right.appendChild(_img_next);

	var _linkaction_left = document.createElement("a");
	_linkaction_left.href = '#' ;
	_linkaction_left.indice = _numprev;
	_linkaction_left.onclick=function(){change_page(this.indice)}
	_linkaction_left.appendChild(_img_previous);

	var _pagination1 = document.getElementById('pagination1');
	var _pagination2 = document.getElementById('pagination2');


	_pagination1.innerHTML ='';
	_pagination1.appendChild(_linkaction_left);

	var page_max =  Math.round( (_numRows / _limit) + 0.5);
	if (_num > page_max && _numRows)
		_num = page_max;
	var istart = 0;
	for(i = 5, istart = _num; istart && i > 0 && istart > 0; i--)
	istart--;
	for(i2 = 0, iend = _num; ( iend <  (_numRows / _limit -1)) && ( i2 < (5 + i)); i2++)
		iend++;
	for (i = istart; i <= iend; i++){
		var _linkaction_num = document.createElement("a");
//	  		_linkaction_num.href = './oreon.php?p='+p+'&o='+o+'&search='+search+'&num='+i+'&limit=' + _limit ;
  		_linkaction_num.href = '#' ;
  		_linkaction_num.indice = i;
  		_linkaction_num.onclick=function(){change_page(this.indice)}
		_linkaction_num.innerHTML = parseInt(i + 1);
		_linkaction_num.className = "otherPageNumber";
		if(i == _num)
		_linkaction_num.className = "currentPageNumber";
		_pagination1.appendChild(_linkaction_num);
		_pagination1.appendChild(_linkaction_right);
	}
	
	

	var _sel1 = document.getElementById('sel1');
	_sel1.innerHTML ='';
	
	var sel = document.createElement('select');
	sel.name = 'l';
	sel.onchange = function() { change_limit(this.value) };
	_sel1.appendChild(sel);

	for(i = 10; i <= 100 ;i += 10){
		var k = document.createElement('option');
		k.value= i;
		sel.appendChild(k);
		var l = document.createTextNode(i);
		k.appendChild(l);
	}


	
}


function monitoring_refresh()	{
	_tmp_on = _on;
	_time_live = _time_reload;
	_on = 1;
	window.clearTimeout(_timeoutID);
	initM(<?=$tM?>,"<?=$sid?>","<?=$o?>");
	_on = _tmp_on;
	//viewDebugInfo('refresh');
}

function monitoring_play()	{
	document.getElementById('JS_monitoring_play').style.display = 'none';
	document.getElementById('JS_monitoring_pause').style.display = 'block';	
	document.getElementById('JS_monitoring_pause_gray').style.display = 'none';
	document.getElementById('JS_monitoring_play_gray').style.display = 'block';
	_on = 1;
	initM(<?=$tM?>,"<?=$sid?>","<?=$o?>");
}

function monitoring_pause()	{
	document.getElementById('JS_monitoring_play').style.display = 'block';
	document.getElementById('JS_monitoring_pause_gray').style.display = 'block';
	document.getElementById('JS_monitoring_play_gray').style.display = 'none';
	document.getElementById('JS_monitoring_pause').style.display='none';
	_on = 0;
	window.clearTimeout(_timeoutID);
}



function escapeURI(La){
  if(encodeURIComponent) {
    return encodeURIComponent(La);
  }
  if(escape) {
    return escape(La)
  }
}

function mainLoop(){
  _currentInputFieldValue = document.getElementById('input_search').value;
  if( (_currentInputFieldValue.length >= 3 || _currentInputFieldValue.length == 0) && _oldInputFieldValue!=_currentInputFieldValue){
    var valeur=escapeURI(_currentInputFieldValue);
	//viewDebugInfo(valeur);
	//viewDebugInfo(_lock);
	_search = valeur;
	 _addrXML = "./include/monitoring/engine/MakeXML_Ndo.php?"+'&sid='+_sid+'&search='+_search+'&search_type_host='+_search_type_host+'&search_type_service='+_search_type_service+'&num='+_num+'&limit='+_limit+'&sort_type='+_sort_type+'&order='+_order+'&date_time_format_status='+_date_time_format_status+'&o='+_o;
	if(!_lock)
		monitoring_refresh();    
  }
  _oldInputFieldValue=_currentInputFieldValue;
  setTimeout("mainLoop()",222);
}


function initM(_time_reload,_sid,_o){

	if(_first){
	mainLoop();
	_first = 0;	
	}

//document.getElementById('input_search').addEventListener("blur" , Isearch , false);


	_time=<?=$time?>;
	if(document.getElementById('debug'))
	{
		//viewDebugInfo('--RESTART--');
		//viewDebugInfo('');
	}
	else{
		var _divdebug = document.createElement("div");
		_divdebug.id = 'debug';
		var _debugtable = document.createElement("table");
		_debugtable.id = 'debugtable';
		var _debugtr = document.createElement("tr");
		_debugtable.appendChild(_debugtr);
		_divdebug.appendChild(_debugtable);
		_header = document.getElementById('header');
		_header.appendChild(_divdebug);
		//viewDebugInfo('--INIT--');
	}
	
	if(_on)
	goM(_time_reload,_sid,_o);
}


function goM(_time_reload,_sid,_o){
	_lock = 1;
	//viewDebugInfo('goM start');
	var proc = new Transformation();

	 _addrXML = "./include/monitoring/engine/MakeXML_Ndo.php?"+'&sid='+_sid+'&search='+_search+'&search_type_host='+_search_type_host+'&search_type_service='+_search_type_service+'&num='+_num+'&limit='+_limit+'&sort_type='+_sort_type+'&order='+_order+'&date_time_format_status='+_date_time_format_status+'&o='+_o;

	proc.setXml(_addrXML)
	proc.setXslt(_addrXSL)
	proc.transform("forAjax");

	_lock = 0;
	_timeoutID = setTimeout('goM("'+ _time_reload +'","'+ _sid +'","'+_o+'")', _time_reload);
	_time_live = _time_reload;
	_on = 1;	
//	viewDebugInfo('goM stop');
}
</SCRIPT>