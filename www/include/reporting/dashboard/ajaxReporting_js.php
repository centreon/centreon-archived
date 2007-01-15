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
        intervalPixels: 300
    }),
    Timeline.createBandInfo({
        showEventText:  false,
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


	var arg = 'oreonPath=<?=$oreon->optGen["oreon_path"]?>&hostID=<?=$host_id?>&color=<?=substr($oreon->optGen["color_up"],1)?>:<?=substr($oreon->optGen["color_down"],1)?>:<?=substr($oreon->optGen["color_unreachable"],1)?>:<?=substr($oreon->optGen["color_unknown"],1)?>';
	 		  	
  tl = Timeline.create(document.getElementById("my-timeline"), bandInfos);

  Timeline.loadXML('./include/reporting/dashboard/GetXmlHost.php?'+arg, function(xml, url) { eventSource.loadXML(xml, url); });
}

</SCRIPT>