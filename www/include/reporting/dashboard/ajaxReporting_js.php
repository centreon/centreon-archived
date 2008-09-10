<?php
/*
 * Centreon is developped with GPL Licence 2.0 :
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 * Developped by : Julien Mathis - Romain Le Merlus 
 * 
 * The Software is provided to you AS IS and WITH ALL FAULTS.
 * Centreon makes no representation and gives no warranty whatsoever,
 * whether express or implied, and without limitation, with regard to the quality,
 * any particular or intended purpose of the Software found on the Centreon web site.
 * In no event will Centreon be liable for any direct, indirect, punitive, special,
 * incidental or consequential damages however they may arise and even if Centreon has
 * been previously advised of the possibility of such damages.
 * 
 * For information : contact@centreon.com
 */
	
	require_once("@CENTREON_ETC@/centreon.conf.php");
	$arg = "";
	if ($type == "Service") {
		$arg = "id=".$service_id."&host_id=".$host_id;
	}else {
		$arg = "id=".$id;
	}
	$arg .= "&color[UP]=".$oreon->optGen["color_up"]."&color[UNDETERMINED]=".$oreon->optGen["color_undetermined"].
				"&color[DOWN]=".$oreon->optGen["color_down"]."&color[UNREACHABLE]=".$oreon->optGen["color_unreachable"].
				"&color[OK]=".$oreon->optGen["color_ok"]."&color[WARNING]=".$oreon->optGen["color_warning"].
				"&color[CRITICAL]=".$oreon->optGen["color_critical"]."&color[UNKNOWN]=".$oreon->optGen["color_unknown"];
	$arg = str_replace("#", "%23", $arg);
	$url = "./include/reporting/dashboard/xmlInformations/GetXml".$type.".php?".$arg;
?>
<script type="text/javascript">

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
	 		  	
	tl = Timeline.create(document.getElementById("my-timeline"), bandInfos);
	
	Timeline.loadXML('<?php echo $url ?>', function(xml, url) { eventSource.loadXML(xml, url); });
}

</script>