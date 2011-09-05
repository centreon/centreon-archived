var selectedPoller;
var debugOption;
var commentOption;
var generateOption;
var moveOption;
var restartOption;
var restartMode;
var exportBtn;

/**
 * Initializes generation options
 */
function initEnvironment()
{
	selectedPoller = document.getElementById('host').value;
	commentOption  = document.getElementById('comment').checked;
	debugOption = document.getElementById('ndebug').checked;
	generateOption = document.getElementById('gen').checked;
	moveOption = document.getElementById('move').checked;
	restartOption = document.getElementById('restart').checked;
	restartMode = document.getElementById('restart_mode').value;
	exportBtn = document.getElementById('exportBtn');	
	exportBtn.disabled = true;	
	if (selectedPoller == "-1") {
		$('consoleContent').insert("<b><font color='red'>NOK</font></b> ("+ msgTab['noPoller'] +")<br/>");
		abortProgress();
		return null;
	}
	$('consoleContent').insert("<b><font color='green'>OK</font></b><br/>");
}

/**
 * Generate files
 */
function generateFiles()
{	
	$('consoleContent').insert(msgTab['gen'] + "... ");
	new Ajax.Request('./include/configuration/configGenerate/xml/generateFiles.php', {
		method: 'post',
		parameters: {
						sid: session_id,
						poller: selectedPoller,
						comment: commentOption,
						debug: debugOption						
					},
		onSuccess: function (response) {
						displayStatusMessage(response.responseXML);
						displayDetails(response.responseXML);
						if (isError(response.responseXML) == "1") {
							abortProgress();
							return null;
						}
						if (moveOption) {
							updateProgress(33);							
							moveFiles();
						} else if (restartOption) {
							updateProgress(50);
							restartPollers();
						} else {
							updateProgress(100);
							exportBtn.disabled = false;
						}						
		}
	});
}

/**
 * Move files
 */
function moveFiles()
{
	$('consoleContent').insert(msgTab['move'] + "... ");
	new Ajax.Request('./include/configuration/configGenerate/xml/moveFiles.php', {
		method: 'post',
		parameters: {
						sid: session_id,				
						poller: selectedPoller
					},
		onSuccess: function (response) {
						displayStatusMessage(response.responseXML);
						displayDetails(response.responseXML);
						if (restartOption) {
							updateProgress(67);
							restartPollers();
						} else {
							updateProgress(100);
							exportBtn.disabled = false;
						}
		}
	});
}

/**
 * Restart Pollers
 */
function restartPollers()
{
	$('consoleContent').insert(msgTab['restart'] + "... ");
	new Ajax.Request('./include/configuration/configGenerate/xml/restartPollers.php', {
		method: 'post',
		parameters: {
						sid: session_id,
						poller: selectedPoller,						
						mode: restartMode
					},
		onSuccess: function (response) {						
						displayStatusMessage(response.responseXML);
						displayDetails(response.responseXML);
						updateProgress(100);
						exportBtn.disabled = false;
		}
	});
}

/**
 * Display status message
 */
function displayStatusMessage(responseXML)
{
	var status = responseXML.getElementsByTagName("status");
	var error = responseXML.getElementsByTagName("error");
	var str;
	str = status.item(0).firstChild.data;
	if (error.length && error.item(0).firstChild.data) {
		str += " (" + error.item(0).firstChild.data + ")";
	}
	str += "<br/>";
	$('consoleContent').insert(str);	
}

/**
 * Display details
 */
function displayDetails(responseXML)
{
	var debug = responseXML.getElementsByTagName("debug");
	var str;	
	
	str = "";
	if (debug.length && debug.item(0).firstChild.data) {
		str = debug.item(0).firstChild.data;
	}
	str += "<br/>";
	$('consoleDetails').insert(str);
}

/**
 * Returns 1 if is error
 * Returns 0 otherwise
 */
function isError(responseXML)
{
	var statuscode = responseXML.getElementsByTagName("statuscode");	
	if (statuscode.length) {		
		return statuscode.item(0).firstChild.data;
	}
	return 0;
}

/**
 * Updates progress
 */
function updateProgress(val)
{
	progressBar.setPercentage(val);
	$('progressPct').update(val + "%");
}

/**
 * Toggle debug
 */
function toggleDebug(pollerId)
{		
	if (pollerId) {
		Effect.toggle('debug_' + pollerId, 'blind');
	}
	$('togglerp_' + pollerId, 'togglerm_' + pollerId).invoke('toggle');
}

function abortProgress()
{
	$('consoleContent').insert(msgTab['abort']);
	exportBtn.disabled = false;
}