<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus

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
	
	
?>

<SCRIPT LANGUAGE="JavaScript">

_time_reload = <?=$tM?>;
_time_live = <?=$tFM?>;

function monitoring_time_msg_failled(_msg) {
	var _tmp = document.getElementById('time_live');
	_tmp.innerHTML = _msg;
	_tmp.className = 'failled_msg';
}

function monitoring_time_Del_msg()	{
		var _tmp = document.getElementById('time_live');
		_tmp.innerHTML = '';
		_tmp.className = 'cachediv';
}

function monitoring_time_msg()	{
		var _tmp = document.getElementById('time_live');
		_tmp.innerHTML = ' Loading in progress...';
		_tmp.className = 'ok_msg';
}


function monitoring_time()	{

	if(_time_live > 999 && _on)
	{
		document.getElementById('time_live').innerHTML = (_time_live / 1000);
		_time_live = _time_live - 1000;
		_timeoutID = setTimeout('monitoring_time()', 1000);	
	}
	else if(_time_live < 999)
	{
		document.getElementById('time_live').innerHTML = *;
		_time_live = 0;
		//_on = 0;
	}
	else
	{
		//_on = 0;
	}
}


function monitoring_refresh()	{	
monitoring_time_msg();
_tmp_on = _on;
_time_live = _time_reload;
_on = 1;
window.clearTimeout(_timeoutID);
//monitoring_time();
initM(<?=$tM?>,"<?=$sid?>");
_on = _tmp_on;
}

function monitoring_play()	{
monitoring_time_msg();
document.getElementById('JS_monitoring_play').style.display = 'none';
document.getElementById('JS_monitoring_pause').style.display = 'block';

document.getElementById('JS_monitoring_pause_gray').style.display = 'none';
document.getElementById('JS_monitoring_play_gray').style.display = 'block';


_on = 1;
initM(<?=$tM?>,"<?=$sid?>");
}

function monitoring_pause()	{


document.getElementById('JS_monitoring_play').style.display = 'block';
document.getElementById('JS_monitoring_pause_gray').style.display = 'block';

document.getElementById('JS_monitoring_play_gray').style.display = 'none';
document.getElementById('JS_monitoring_pause').style.display='none';

_on = 0;
window.clearTimeout(_timeoutID);
}


</SCRIPT>
	