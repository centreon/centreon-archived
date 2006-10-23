<?
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

?>
<SCRIPT LANGUAGE="JavaScript">


    var tl;
    
function initTimeline() {
	var eventSource = new Timeline.DefaultEventSource();

	  var bandInfos = [
	    Timeline.createBandInfo({
        	eventSource:    eventSource,
	        width:          "70%", 
	        intervalUnit:   Timeline.DateTime.DAY, 
	        intervalPixels: 300,

            trackHeight:    2.0,
            trackGap:       0.1,
	    }),
	    Timeline.createBandInfo({
	        showEventText:  false,
    	    trackHeight:    0.5,
        	trackGap:       0.2,
        	eventSource:    eventSource,
	        width:          "30%", 
	        intervalUnit:   Timeline.DateTime.MONTH, 
	        intervalPixels: 300
	    })
	  ];
	        bandInfos[0].syncWith = 1;
	        bandInfos[0].multiple = 2;
            bandInfos[0].highlight = false;
	        bandInfos[1].syncWith = 1;
            bandInfos[1].highlight = false;
	        bandInfos[1].multiple = 2;
	  var tl = Timeline.create(document.getElementById('my-timeline'), bandInfos, Timeline.HORIZONTAL);

 



	_form=document.getElementById('AjaxBankBasic');
	_formR=document.getElementById('datareport');

	var color = _formR.color_UP.value + ':' +
	_formR.color_DOWN.value  + ':' +
	_formR.color_UNREACHABLE.value + ':' +
	_formR.color_UNKNOWN.value;

	var arg = 'oreonPath=' + _form.fileOreonConf.value +
	 		  	'&hostID=' + _formR.hostID.value +
	 		  	'&color=' + color;
	 		  	
	if(_formR.hostID.value)
  		Timeline.loadXML('./include/reporting/dashboard/GetXmlHost.php?'+arg, function(xml, url) { eventSource.loadXML(xml, url); });  	
	}
</SCRIPT>