<?php
/**
Centreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
Developped by : Julien Mathis - Romain Le Merlus

Adapted to Pear library by Merethis company, under direction of Cedrick Facon, Romain Le Merlus, Julien Mathis

The Software is provided to you AS IS and WITH ALL FAULTS.
OREON makes no representation and gives no warranty whatsoever,
whether express or implied, and without limitation, with regard to the quality,
safety, contents, performance, merchantability, non-infringement or suitability for
any particular or intended purpose of the Software found on the OREON web site.
In no event will OREON be liable for any direct, indirect, punitive, special,
incidental or consequential damages however they may arise and even if OREON has
been previously advised of the possibility of such damages.

For information : contact@oreon-project.org
**/
	if (!isset($oreon))
		exit();


$openid = '0';
$open_id_sub = '0';
if(isset($_GET["openid"])){
$openid = $_GET["openid"];
$open_id_type = substr($openid, 0, 2);
$open_id_sub = substr($openid, 3, strlen($openid));
}

if(isset($_GET["host_id"]) && $open_id_type == "HH"){
	$_GET["host_id"] = $open_id_sub;
}
else
	$_GET["host_id"] = null;

?>
<script language='javascript' src='./include/common/javascript/tool.js'></script>
<script>
			var css_file = './include/common/javascript/codebase/dhtmlxtree.css';
		    var headID = document.getElementsByTagName("head")[0];  
		    var cssNode = document.createElement('link');
		       cssNode.type = 'text/css';
		       cssNode.rel = 'stylesheet';
		       cssNode.href = css_file;
		       cssNode.media = 'screen';headID.appendChild(cssNode);
 
 
    		tree=new dhtmlXTreeObject("menu_40211","100%","100%","1");
            tree.setImagePath("./img/icones/csh_vista/");

//            tree.setImagePath("./include/common/javascript/codebase/imgs/csh_vista/");


            //link tree to asp script
            tree.setXMLAutoLoading("./include/views/graphs/graphODS/GetODSXmlTree.php"); 
            
            //load first level of tree
            tree.loadXML("./include/views/graphs/graphODS/GetODSXmlTree.php?id=1&openid=<?php echo $openid; ?>");

			// system to reload page after link with new url
			tree.attachEvent("onClick",onNodeSelect)//set function object to call on node select 
			tree.attachEvent("onDblClick",onDblClick)//set function object to call on node select 
			//see other available event handlers in API documentation 

			tree.enableDragAndDrop(0);
			tree.enableTreeLines(false);	

			function onDblClick(nodeId)
			{
				tree.openAllItems(nodeId);
				return(false);
			}
			
			function onNodeSelect(nodeId)
			{
				var graphView4xml = document.getElementById('graphView4xml');
				graphView4xml.innerHTML="..graph.." + nodeId;

				tree.openItem(nodeId);

				graph_4_host(nodeId,'','');
			}
			
			function mk_pagination(){;}
			function set_header_title(){;}

			function graph_4_host(id, start, end)
			{
				
				tree.selectItem(id);
				
				var proc = new Transformation();
				var _addrXSL = "./include/views/graphs/graphODS/GraphService.xsl";
				var _addrXML = './include/views/graphs/graphODS/GetODSXmlGraph.php?start='+start+'&end='+end+'&id='+id+'&sid=<?php echo $sid;?>';
				proc.setXml(_addrXML)
				proc.setXslt(_addrXSL)
				proc.transform("graphView4xml");
			}


</script>
<div id="graphView4xml">..</div>
