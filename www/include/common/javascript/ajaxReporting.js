/**
Oreon is developped with Apache Licence 2.0 :
http://www.apache.org/licenses/LICENSE-2.0.txt
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
// JavaScript Document

var xhr = null; 
var _adresseRecherche = "./include/monitoring/status/readStatus.php" //l'adresse   interroger pour trouver les suggestions
	 
function getXhr(){
	if(window.XMLHttpRequest) // Firefox et autres
	   xhr = new XMLHttpRequest(); 
	else if(window.ActiveXObject){ // Internet Explorer 
	   try {
                xhr = new ActiveXObject("Msxml2.XMLHTTP");
            } catch (e) {
                xhr = new ActiveXObject("Microsoft.XMLHTTP");
            }
	}
	else { // XMLHttpRequest non supportÃ¯Â¿Âœ par le navigateur 
	   alert("Votre navigateur ne supporte pas les objets XMLHTTPRequest..."); 
	   xhr = false; 
	} 
}


    var tl;
	function onLoad() {
	var eventSource = new Timeline.DefaultEventSource();

	  var bandInfos = [
	    Timeline.createBandInfo({
        	eventSource:    eventSource,
        	date:           "July 28 2006 00:00:00 GMT", 
	        width:          "70%", 
	        intervalUnit:   Timeline.DateTime.DAY, 
	        intervalPixels: 100
	    }),
	    Timeline.createBandInfo({
	        showEventText:  false,
    	    trackHeight:    0.5,
        	trackGap:       0.2,
        	eventSource:    eventSource,
        	date:           "July 28 2006 00:00:00 GMT", 
	        width:          "30%", 
	        intervalUnit:   Timeline.DateTime.MONTH, 
	        intervalPixels: 200
	    })
	  ];
	              bandInfos[1].syncWith = 0;
            bandInfos[1].highlight = true;
	  var tl = Timeline.create(document.getElementById('my-timeline'), bandInfos, Timeline.HORIZONTAL);
/*

            var bandInfos = [
                Timeline.createBandInfo({
        	eventSource:    eventSource,
        	date:           'July 28 2006 00:00:00 GMT', 
	        width:          '70%', 
	        intervalUnit:   Timeline.DateTime.DAY, 
	        intervalPixels: 20
                }),
                Timeline.createBandInfo({
	        showEventText:  false,
    	    trackHeight:    0.5,
        	trackGap:       0.2,
        	eventSource:    eventSource,
        	date:           'July 28 2006 00:00:00 GMT', 
	        width:          '30%', 
	        intervalUnit:   Timeline.DateTime.MONTH, 
	        intervalPixels: 200
                })
            ];
            bandInfos[1].syncWith = 0;
            bandInfos[1].highlight = true;
            tl = Timeline.create(document.getElementById('my-timeline'), bandInfos, Timeline.VERTICAL);
*/


	_form=document.getElementById('AjaxBankBasic');
	_formR=document.getElementById('datareport');
	var arg = 'oreonPath=' + _formR.fileOreonConf.value +
	 		  	'&hostID=' + _formR.hostID.value +
	 		  	'&colorUP=' + _form.color_UP.value +
	 		  	'&colorDOWN=' + _form.color_DOWN.value +
	 		  	'&colorUNREACHABLE=' + _form.color_UNREACHABLE.value +
	 		  	'&colorUNKNOWN=' + _form.color_UNKNOWN.value;
	 		  	
//document.getElementById('log').innerHTML = arg;			
			
  	Timeline.loadXML('./include/reporting/GetXmlHost.php?'+arg, function(xml, url) { eventSource.loadXML(xml, url); });
	}
