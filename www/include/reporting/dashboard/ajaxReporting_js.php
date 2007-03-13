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

  bandInfos[1].syncWith = 0;
  bandInfos[1].highlight = true;
  bandInfos[1].eventPainter.setLayout(bandInfos[0].eventPainter.getLayout());


	var arg = 'oreonPath=<?=$oreon->optGen["oreon_path"]?>&hostID=<?=$host_id?>&color=<?=$color.$today_var?>';
	 		  	
  tl = Timeline.create(document.getElementById("my-timeline"), bandInfos);

  Timeline.loadXML('./include/reporting/dashboard/GetXml<?=$type?>.php?'+arg, function(xml, url) { eventSource.loadXML(xml, url); });
}

</SCRIPT>