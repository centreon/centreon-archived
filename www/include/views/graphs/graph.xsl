<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:variable name="cellsPerRow" select="7" />
<xsl:template match="root">
<div id="openid" style="display:none">
	<xsl:value-of select="//opid"/>
</div>

<xsl:if test="svc">
	<div id="div2">
		<form name="formu2">
    	    <table class="ajaxOption">
				<tr>
					<td>
						<xsl:element name='input'>
							<xsl:attribute name="onClick">graph_4_host('<xsl:value-of select="//opid"/>',multi, '<xsl:value-of select="//target"/>' ); return false;</xsl:attribute>
							<xsl:attribute name="name">split</xsl:attribute>
							<xsl:attribute name="type">checkbox</xsl:attribute>
							<xsl:if test="//split = 1">
								<xsl:attribute name="checked">checked</xsl:attribute>
							</xsl:if>
						</xsl:element>
						<xsl:value-of select="//lang/giv_split_component"/>
					</td>
				</tr>
        	</table>
		</form>
   	</div>
        <table class="ListTable">
        <tr class="ListHeader">
            <td class="ListColHeaderPicker" valign="top" align="center">
                <xsl:value-of select="//name"/>
            </td>
            <td class="ListColRight">
            </td>
        </tr>
        <tr >
            <td colspan="2">
		<table class="ListTable">
	       <xsl:for-each select="//period">
		        <tr class="list_one">
					<td class='ListColLeft' valign="top" align='center'> <xsl:value-of select="name"/></td>
					<td class='ListColRight'>
						<xsl:element name="a">
							<xsl:attribute name="id">zoom_<xsl:value-of select="//zoom_type"/><xsl:value-of select="//id"/>__P:<xsl:value-of select="name"/></xsl:attribute>
							<xsl:attribute name="onClick">switchZoomGraph('<xsl:value-of select="//zoom_type"/><xsl:value-of select="//id"/>__P:<xsl:value-of select="name"/>', '<xsl:value-of select="//target"/>'); return false;</xsl:attribute>
							<xsl:attribute name="style">cursor: pointer;</xsl:attribute>
							<img src="./img/icons/target.png" class="ico-16 margin_right"/>
						</xsl:element>
						<xsl:element name="a">
							<xsl:attribute name="href">./include/views/graphs/generateGraphs/generateImage.php?index=<xsl:value-of select="//index"/>&amp;end=<xsl:value-of select="end"/>&amp;start=<xsl:value-of select="start"/></xsl:attribute>
							<img src="./img/icons/picture.png" class="ico-16  margin_right" />
						</xsl:element>
						<xsl:element name="a">
							<xsl:attribute name="href">./include/views/graphs/exportData/ExportCSVServiceData.php?index=<xsl:value-of select="//index"/>&amp;end=<xsl:value-of select="end"/>&amp;start=<xsl:value-of select="start"/></xsl:attribute>
							<img src="./img/icons/csv.png" class="ico-16 margin_right"/>
						</xsl:element>
					</td>
				</tr>
				<tr>
	    			<td class='ListColCenter' valign="top" align='center'>
				    	<div id="imggraph">
							<xsl:variable name= "mstart">
								<xsl:value-of select="start"/>
							</xsl:variable>
							<xsl:if test="//split = 0">
								<xsl:element name="a">
									<xsl:attribute name="onClick">graph_4_host('<xsl:value-of select="//zoom_type"/><xsl:value-of select="//id"/>', '','<xsl:value-of select="//target"/>', 0, '<xsl:value-of select="start"/>', '<xsl:value-of select="end"/>'); return false;</xsl:attribute>
									<xsl:attribute name="href">#</xsl:attribute>
										<xsl:element name="img">
											<xsl:attribute name="id"><xsl:value-of select="//zoom_type"/><xsl:value-of select="//id"/>__P:<xsl:value-of select="name"/></xsl:attribute>
										  	<xsl:attribute name="src">./include/views/graphs/generateGraphs/generateImage.php?index=<xsl:value-of select="//index"/>&amp;end=<xsl:value-of select="end"/>&amp;start=<xsl:value-of select="start"/></xsl:attribute>
										</xsl:element>
								</xsl:element>
							</xsl:if>
							<xsl:if test="//split = 1">
								<table style="width: 100%">
								<xsl:for-each select="metric">
									<tr>
										<td style="vertical-align: top">
										<xsl:element name="a">
											<xsl:attribute name="onClick">graph_4_host('<xsl:value-of select="//zoom_type"/><xsl:value-of select="//id"/>', '','<xsl:value-of select="//target"/>', 0,'<xsl:value-of select="../start"/>', '<xsl:value-of select="../end"/>'); return false;</xsl:attribute>
											<xsl:attribute name="href">#</xsl:attribute>
											<xsl:element name="img">
												<xsl:attribute name="id"><xsl:value-of select="//zoom_type"/><xsl:value-of select="//id"/>__P:<xsl:value-of select="../name"/>__M:<xsl:value-of select="metric_id"/></xsl:attribute>
												<xsl:attribute name="src">./include/views/graphs/generateGraphs/generateMetricImage.php?cpt=1&amp;index=<xsl:value-of select="//index"/>&amp;metric=<xsl:value-of select="metric_id"/>&amp;end=<xsl:value-of select="//end"/>&amp;start=<xsl:value-of select="$mstart"/></xsl:attribute>
											</xsl:element>
										</xsl:element>
										</td>
										<td style="vertical-align: top">
										<xsl:element name="a">
											<xsl:attribute name="style">float: right;</xsl:attribute>
											<xsl:attribute name="href">./include/views/graphs/generateGraphs/generateMetricImage.php?cpt=1&amp;index=<xsl:value-of select="//index"/>&amp;metric=<xsl:value-of select="metric_id"/>&amp;end=<xsl:value-of select="//end"/>&amp;start=<xsl:value-of select="$mstart"/></xsl:attribute>
											<img src="./img/icones/16x16/save.gif" style="margin-right:5px;vertical-align:top;" />
										</xsl:element>
										</td>
									</tr>
								</xsl:for-each>
								</table>
							</xsl:if>
						 <br/>
					 </div> 
					</td>
				</tr>			
			</xsl:for-each>
	    </table>
            </td>
        </tr>
            </table>
</xsl:if>
<xsl:if test="svc_zoom">

	<div id="div3" valign="top" align='left'>
		<table class="ajaxOption table">
			<tr>
				<form name="formu2">
					<td>
						<xsl:element name='input'>
							<xsl:attribute name="onClick">graph_4_host('<xsl:value-of select="//opid"/>','','<xsl:value-of select="//target"/>'); return false;</xsl:attribute>
							<xsl:attribute name="name">split</xsl:attribute>
							<xsl:attribute name="type">checkbox</xsl:attribute>
							<xsl:if test="//split = 1">
								<xsl:attribute name="checked">checked</xsl:attribute>
							</xsl:if>
						</xsl:element>
						<xsl:value-of select="//lang/giv_split_component"/>
					</td>
				</form>
					<td class='ListColRight'>
						<form name="formu3">
							<table>
								<tr>
								<xsl:for-each select="//metrics[position() mod $cellsPerRow = 1]">
									<xsl:apply-templates select=".|following-sibling::metrics[position() &lt; $cellsPerRow]"/>
								</xsl:for-each>
								</tr>
							</table>
						</form>
					</td>
			</tr>
		</table>

	</div>
	<div>
		<table class="ListTable">
			<tr class="ListHeader">
				<td class='ListColHeaderPicker' valign="top" align='center'><xsl:value-of select="//name"/></td>
				<td class='ListColRight'>
					<xsl:element name="a">
						<xsl:attribute name="id">zoom_<xsl:value-of select="//opid"/></xsl:attribute>
						<xsl:attribute name="onClick">switchZoomGraph('<xsl:value-of select="//opid"/>', '<xsl:value-of select="//target"/>');</xsl:attribute>
						<xsl:attribute name="style">cursor: pointer;</xsl:attribute>
						<img src="./img/icons/target.png" class="ico-16 margin_right"/>
					</xsl:element>
					<xsl:element name="a">
						<xsl:attribute name="href">./include/views/graphs/generateGraphs/generateImageZoom.php?<xsl:value-of select="//metricsTab"/>&amp;index=<xsl:value-of select="//index"/>&amp;end=<xsl:value-of select="//end"/>&amp;start=<xsl:value-of select="//start"/>&amp;warn=<xsl:value-of select="//warning"/>&amp;crit=<xsl:value-of select="//critical"/></xsl:attribute>
						<img src="./img/icons/picture.png" class="ico-16" />
					</xsl:element>
					<xsl:element name="a">
						<xsl:attribute name="href">./include/views/graphs/exportData/ExportCSVServiceData.php?index=<xsl:value-of select="//index"/>&amp;end=<xsl:value-of select="//end"/>&amp;start=<xsl:value-of select="//start"/></xsl:attribute>
						<img src="./img/icons/csv.png" class="ico-16 margin_right"/>
					</xsl:element>
				</td>
			</tr>
			<tr>
	    		<td class='ListColCenter' valign="top" align='center' colspan="2" style="position:relative;">
						<xsl:element name='input'>
							<xsl:attribute name="onClick">prevPeriod();</xsl:attribute>
							<xsl:attribute name="type">button</xsl:attribute>
							<xsl:attribute name="name">prev</xsl:attribute>
							<xsl:attribute name="value">&lt;&lt;</xsl:attribute>
							<xsl:attribute name="class">bt_action</xsl:attribute>
							<xsl:attribute name="style">position:absolute;top:7em;left:3em;</xsl:attribute>
						</xsl:element>
			    	<div id="imggraph">
						<xsl:if test="//split = 0">
							<xsl:if test="//debug = 1">
								<xsl:element name="a">
									<xsl:attribute name="href">./include/views/graphs/generateGraphs/generateImageZoom.php?<xsl:value-of select="//metricsTab"/>&amp;index=<xsl:value-of select="//index"/>&amp;end=<xsl:value-of select="//end"/>&amp;start=<xsl:value-of select="//start"/>&amp;warn=<xsl:value-of select="//warning"/>&amp;crit=<xsl:value-of select="//critical"/></xsl:attribute>
								</xsl:element>
							</xsl:if>
							<xsl:element name="a">
								<xsl:attribute name="href">#</xsl:attribute>
								<xsl:attribute name="onclick">var host_search = '<xsl:value-of select="//opid"/>'.sub('SS', 'HS'); graph_4_host(host_search,'','<xsl:value-of select="//target"/>'); return false;</xsl:attribute>
								<xsl:element name="img">
									<xsl:attribute name="id"><xsl:value-of select="//opid"/></xsl:attribute>
							  		<xsl:attribute name="src">./include/views/graphs/generateGraphs/generateImageZoom.php?<xsl:value-of select="//metricsTab"/>&amp;index=<xsl:value-of select="//index"/>&amp;end=<xsl:value-of select="//end"/>&amp;start=<xsl:value-of select="//start"/>&amp;warn=<xsl:value-of select="//warning"/>&amp;crit=<xsl:value-of select="//critical"/></xsl:attribute>
								</xsl:element>
							</xsl:element>
						</xsl:if>
						<xsl:if test="//split = 1">
							<table style="width: 100%">
							<xsl:for-each select="//metrics">
								<xsl:if test="select = 1">
								<tr>
									<td style="vertical-align: top">
										<xsl:element name="a">
											<xsl:attribute name="href">#</xsl:attribute>
											<xsl:attribute name="onclick">var host_search = '<xsl:value-of select="//opid"/>'.sub('SS', 'HS'); graph_4_host(host_search,'','<xsl:value-of select="//target"/>'); return false;</xsl:attribute>
											<xsl:element name="img">
												<xsl:attribute name="id"><xsl:value-of select="//opid"/>__M:<xsl:value-of select="metric_id"/></xsl:attribute>
											  	<xsl:attribute name="src">./include/views/graphs/generateGraphs/generateMetricImage.php?template_id=<xsl:value-of select="//tpl"/>&amp;index=<xsl:value-of select="//index"/>&amp;cpt=1&amp;metric=<xsl:value-of select="metric_id"/>&amp;end=<xsl:value-of select="//end"/>&amp;start=<xsl:value-of select="//start"/></xsl:attribute>
											</xsl:element>
										</xsl:element>
									</td>
									<td style="vertical-align: top">
										<xsl:element name="a">
											<xsl:attribute name="style">float: right;</xsl:attribute>
											<xsl:attribute name="href">./include/views/graphs/generateGraphs/generateMetricImage.php?template_id=<xsl:value-of select="//tpl"/>&amp;index=<xsl:value-of select="//index"/>&amp;cpt=1&amp;metric=<xsl:value-of select="metric_id"/>&amp;end=<xsl:value-of select="//end"/>&amp;start=<xsl:value-of select="//start"/></xsl:attribute>
											<img src="./img/icones/16x16/save.gif" style="margin-right:5px;vertical-align:top;" />
										</xsl:element>
									</td>
								</tr>
								</xsl:if>
							</xsl:for-each>
							</table>
						</xsl:if>
					<br/>
					</div>
						<xsl:element name='input'>
							<xsl:attribute name="onClick">nextPeriod();</xsl:attribute>
							<xsl:attribute name="type">button</xsl:attribute>
							<xsl:attribute name="name">next</xsl:attribute>
							<xsl:attribute name="value">&gt;&gt;</xsl:attribute>
							<xsl:attribute name="class">bt_action</xsl:attribute>
							<xsl:attribute name="style">position:absolute;top:7em;right:3em;</xsl:attribute>
						</xsl:element>
				</td>
			</tr>
	    </table>
	</div>
</xsl:if>
<xsl:if test="//multi_svc">
	<div>
		<table class="ListTable">
			<xsl:for-each select="//multi_svc">
		        <tr class="ListHeader">
					<td class='ListColHeaderLeft' valign="top" align='center'><xsl:value-of select="name"/></td>
					<td  class='ListColRight'>
						<div id="div2">
						<form name="formu2">
						<xsl:element name='input'>
							<xsl:attribute name="onClick">graph_4_host('<xsl:value-of select="//opid"/>', multi,'<xsl:value-of select="//target"/>'); return false;</xsl:attribute>
							<xsl:attribute name="name">split</xsl:attribute>
							<xsl:attribute name="type">checkbox</xsl:attribute>
							<xsl:if test="//splitvalue = 1">
								<xsl:attribute name="checked">checked</xsl:attribute>
							</xsl:if>
						</xsl:element>
						Split Components
						<xsl:element name="a">
							<xsl:attribute name="title">Select interval</xsl:attribute>
							<xsl:attribute name="id">zoom_<xsl:value-of select="opid"/></xsl:attribute>
							<xsl:attribute name="onClick">switchZoomGraph('<xsl:value-of select="opid"/>', '<xsl:value-of select="//target"/>');</xsl:attribute>
							<xsl:attribute name="style">cursor: pointer;</xsl:attribute>
							<img src="./img/icons/target.png" class="ico-16 margin_right" />
						</xsl:element>
						<xsl:element name="a">
							<xsl:attribute name="title">Export graph</xsl:attribute>
							<xsl:attribute name="href">./include/views/graphs/generateGraphs/generateImage.php?index=<xsl:value-of select="index"/>&amp;end=<xsl:value-of select="end"/>&amp;start=<xsl:value-of select="start"/></xsl:attribute>
							<img src="./img/icons/picture.png" class="ico-16 margin_right"/>
						</xsl:element>
						<xsl:element name="a">
							<xsl:attribute name="title">Export CSV</xsl:attribute>
							<xsl:attribute name="href">./include/views/graphs/exportData/ExportCSVServiceData.php?index=<xsl:value-of select="index"/>&amp;end=<xsl:value-of select="end"/>&amp;start=<xsl:value-of select="start"/></xsl:attribute>
							<img src="./img/icons/csv.png" class="ico-16"/>
						</xsl:element>		
					</td>
				</tr>
				<tr>
	    			<td class='ListColCenter' valign="top" align='center' colspan="2" style="position:relative;" >
							<xsl:element name='input'>
								<xsl:attribute name="onClick">prevPeriod();</xsl:attribute>
								<xsl:attribute name="type">button</xsl:attribute>
								<xsl:attribute name="name">prev</xsl:attribute>
								<xsl:attribute name="value">&lt;&lt;</xsl:attribute>
								<xsl:attribute name="class">bt_action</xsl:attribute>
								<xsl:attribute name="style">position:absolute;top:7em;left:3em;</xsl:attribute>
							</xsl:element>
						<div id="imggraph">
							<ul style="list-style-type: none;">
								<li style="list-style-type: none;">
									<xsl:if test="split = 0">
										<xsl:element name="a">
											<xsl:attribute name="onClick">multi=0;graph_4_host('<xsl:value-of select="opid"/>','', '<xsl:value-of select="//target"/>'); return false;</xsl:attribute>
											<xsl:attribute name="href">#</xsl:attribute>
											<xsl:element name="img">
												<xsl:attribute name="id"><xsl:value-of select="opid"/></xsl:attribute>
												<xsl:attribute name="src">./include/views/graphs/generateGraphs/generateImage.php?index=<xsl:value-of select="index"/>&amp;end=<xsl:value-of select="end"/>&amp;start=<xsl:value-of select="start"/></xsl:attribute>
											</xsl:element>
										</xsl:element>
									</xsl:if>
									<xsl:if test="split = 1">
										<table style="width: 100%">
										<xsl:for-each select="metrics">
											<tr>
											<xsl:if test="select = 1">
												<td style="vertical-align: top">
													<xsl:element name="a">
														<xsl:attribute name="onClick">graph_4_host('<xsl:value-of select="../opid"/>', '', '<xsl:value-of select="//target"/>'); return false;</xsl:attribute>
														<xsl:attribute name="href">#</xsl:attribute>
														<xsl:element name="img">
															<xsl:attribute name="id"><xsl:value-of select="../opid"/>__M:<xsl:value-of select="metric_id"/></xsl:attribute>
															<xsl:attribute name="src">./include/views/graphs/generateGraphs/generateMetricImage.php?cpt=1&amp;index=<xsl:value-of select="../index"/>&amp;metric=<xsl:value-of select="metric_id"/>&amp;end=<xsl:value-of select="//end"/>&amp;start=<xsl:value-of select="//start"/></xsl:attribute>
														</xsl:element>
													</xsl:element>
												</td>
												<td style="vertical-align: top;width: 16px;">
													<xsl:element name="a">
														<xsl:attribute name="title">Export graph</xsl:attribute>
														<xsl:attribute name="href">./include/views/graphs/generateGraphs/generateMetricImage.php?cpt=1&amp;index=<xsl:value-of select="../index"/>&amp;metric=<xsl:value-of select="metric_id"/>&amp;end=<xsl:value-of select="//end"/>&amp;start=<xsl:value-of select="//start"/></xsl:attribute>
														<img src="./img/icons/picture.png" class="ico-16 margin_right" />
													</xsl:element>
												</td>
												<td style="vertical-align: top;width: 16px;">
													<xsl:element name="a">
														<xsl:attribute name="title">Select interval</xsl:attribute>
														<xsl:attribute name="style">cursor: pointer;</xsl:attribute>
														<xsl:attribute name="id">zoom_<xsl:value-of select="../opid"/>__M:<xsl:value-of select="metric_id"/></xsl:attribute>
														<xsl:attribute name="onClick">switchZoomGraph("<xsl:value-of select="../opid"/>__M:<xsl:value-of select="metric_id"/>", '<xsl:value-of select="//target"/>');</xsl:attribute>
														<img src="./img/icons/target.png" class="ico-16" />
													</xsl:element>
												</td>
											</xsl:if>
											</tr>
										</xsl:for-each>
										</table>
									</xsl:if>
								</li>
								<xsl:if test="status = 1">
								<li style="list-style-type: none; margin-top: 15px;">
									<table style="width: 100%">
										<tr>
											<td style="vertical-align: top">
												<xsl:element name="div">
													<xsl:element name="img">
														<xsl:attribute name="src">./include/views/graphs/graphStatus/displayServiceStatus.php?index=<xsl:value-of select="index"/>&amp;end=<xsl:value-of select="end"/>&amp;start=<xsl:value-of select="start"/></xsl:attribute>
													</xsl:element>
												</xsl:element>
											</td>
											<xsl:if test="split = 1">
												<td style="vertical-align: top;width: 16px;">
													<img src="./img/icones/1x1/blank.gif" style="vertical-align:top;" /></td>
												<td style="vertical-align: top;width: 16px;">
													<img src="./img/icones/1x1/blank.gif" style="vertical-align:top;" /></td>
											</xsl:if>
										</tr>
									</table>
								</li>
								</xsl:if>
							</ul>
						</div>
							<xsl:element name='input'>
								<xsl:attribute name="onClick">nextPeriod();</xsl:attribute>
								<xsl:attribute name="type">button</xsl:attribute>
								<xsl:attribute name="name">next</xsl:attribute>
								<xsl:attribute name="value">&gt;&gt;</xsl:attribute>
								<xsl:attribute name="class">bt_action</xsl:attribute>
								<xsl:attribute name="style">position:absolute;top:7em;right:3em;</xsl:attribute>
							</xsl:element>
					</td>
				</tr>
			</xsl:for-each>
			</table>
		</div>
</xsl:if>
</xsl:template>
<xsl:template match="metrics">
	<td  class='ListColLeft'>
		<xsl:element name='input'>
			<xsl:attribute name="style">margin-left: 4px;</xsl:attribute>
			<xsl:attribute name="onClick">graph_4_host('<xsl:value-of select="//opid"/>', '', '<xsl:value-of select="//target"/>',1); return false;</xsl:attribute>
			<xsl:attribute name="type">checkbox</xsl:attribute>
			<xsl:attribute name="name">metric</xsl:attribute>
			<xsl:attribute name="value"><xsl:value-of select="metric_id"/></xsl:attribute>
			<xsl:if test="select = 1">
				<xsl:attribute name="checked">checked</xsl:attribute>
			</xsl:if>
			</xsl:element>
			<xsl:value-of select="metric_name"/>
	</td>
</xsl:template>
</xsl:stylesheet>
