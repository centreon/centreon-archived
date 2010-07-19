<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:template match="root">
<div id="openid" style="display:none">
	<xsl:value-of select="//opid"/>
</div>
<xsl:if test="host">
	<div>
		<table class="ListTable">
			<xsl:for-each select="//svc">
		        <tr class="list_one">
					<td class='ListColLeft' valign="top" align='center'> <b>Service : <xsl:value-of select="name"/></b></td>
					<td style="text-align:right;width:42px;">
						<xsl:element name="a">
							<xsl:attribute name="href">./include/views/graphs/exportData/ExportCSVServiceData.php?index=<xsl:value-of select="//index"/>&amp;sid=<xsl:value-of select="//sid"/>&amp;end=<xsl:value-of select="//end"/>&amp;start=<xsl:value-of select="//start"/></xsl:attribute>
							<img src="./img/icones/16x16/text_binary_csv.gif"/>
						</xsl:element>
					</td>
				</tr>
				<tr>
	    			<td class='ListColCenter' valign="top" align='center'>
				    	<div id="imggraph">
							<xsl:if test="splitvalue = 0">
								<xsl:element name="a">
									<xsl:attribute name="onClick">graph_4_host('HS_<xsl:value-of select="service_id"/>', ''); return false;</xsl:attribute>
									<xsl:attribute name="href">#</xsl:attribute>
									<xsl:element name="img">
									  	<xsl:attribute name="src">./include/views/graphs/generateGraphs/generateImage.php?session_id=<xsl:value-of select="//sid"/>&amp;index=<xsl:value-of select="index"/>&amp;end=<xsl:value-of select="//end"/>&amp;start=<xsl:value-of select="//start"/></xsl:attribute>
									</xsl:element>
								</xsl:element>
							</xsl:if>
							<xsl:if test="splitvalue = 1">
								<xsl:for-each select="//metric">
									<xsl:element name="a">
										<xsl:attribute name="onClick">graph_4_host('HS_<xsl:value-of select="//service_id"/>', ''); return false;</xsl:attribute>
										<xsl:attribute name="href">#</xsl:attribute>
											<xsl:element name="img">
										  		<xsl:attribute name="src">./include/views/graphs/generateGraphs/generateMetricImage.php?session_id=<xsl:value-of select="//sid"/>&amp;cpt=1&amp;metric=<xsl:value-of select="metric_id"/>&amp;end=<xsl:value-of select="//end"/>&amp;start=<xsl:value-of select="//start"/></xsl:attribute>
											</xsl:element>
										</xsl:element>
									<br/>
								</xsl:for-each>
							</xsl:if>
						 <br/>
						 </div> 
					</td>
				</tr>			
			</xsl:for-each>
	    </table>
	</div>
</xsl:if>
<xsl:if test="svc">
	<div id="div2" valign="top" align='left'>
		<form name="formu2">
    	    <table class="ajaxOption">
				<tr>
           		<td>
					<xsl:element name='input'>
						<xsl:attribute name="onClick">graph_4_host('<xsl:value-of select="//opid"/>',multi); return false;</xsl:attribute>
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
	<div>
		<table class="ListTable">
	       <xsl:for-each select="//period">
		        <tr class="list_one">
					<td class='ListColLeft' valign="top" align='center'> <xsl:value-of select="name"/></td>
					<td style="text-align:right;width:42px;">
						<xsl:if test="//split = 0">
							<xsl:element name="a">
								<xsl:attribute name="href">./include/views/graphs/generateGraphs/generateImage.php?session_id=<xsl:value-of select="//sid"/>&amp;index=<xsl:value-of select="//index"/>&amp;end=<xsl:value-of select="end"/>&amp;start=<xsl:value-of select="start"/></xsl:attribute>
								<img src="./img/icones/16x16/save.gif" style="margin-right:5px;"/>
							</xsl:element>
						</xsl:if>
						<xsl:element name="a">
							<xsl:attribute name="href">./include/views/graphs/exportData/ExportCSVServiceData.php?index=<xsl:value-of select="//index"/>&amp;sid=<xsl:value-of select="//sid"/>&amp;end=<xsl:value-of select="//end"/>&amp;start=<xsl:value-of select="//start"/></xsl:attribute>
							<img src="./img/icones/16x16/text_binary_csv.gif"/>
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
									<xsl:attribute name="onClick">graph_4_host('<xsl:value-of select="//zoom_type"/><xsl:value-of select="//id"/>', '', '<xsl:value-of select="start"/>', '<xsl:value-of select="end"/>'); return false;</xsl:attribute>
									<xsl:attribute name="href">#</xsl:attribute>
										<xsl:element name="img">
										  	<xsl:attribute name="src">./include/views/graphs/generateGraphs/generateImage.php?session_id=<xsl:value-of select="//sid"/>&amp;index=<xsl:value-of select="//index"/>&amp;end=<xsl:value-of select="end"/>&amp;start=<xsl:value-of select="start"/></xsl:attribute>
										</xsl:element>
								</xsl:element>
								<xsl:element name="a">
									<xsl:attribute name="href">./include/views/graphs/generateGraphs/generateImage.php?session_id=<xsl:value-of select="//sid"/>&amp;index=<xsl:value-of select="//index"/>&amp;end=<xsl:value-of select="end"/>&amp;start=<xsl:value-of select="start"/></xsl:attribute>
									<img src="./img/icones/16x16/save.gif" style="margin-right:5px;vertical-align:top;" />
								</xsl:element>
							</xsl:if>
							<xsl:if test="//split = 1">
								<xsl:for-each select="metric">
									<xsl:element name="a">
										<xsl:attribute name="onClick">graph_4_host('<xsl:value-of select="//zoom_type"/><xsl:value-of select="//id"/>', ''); return false;</xsl:attribute>
										<xsl:attribute name="href">#</xsl:attribute>
										<xsl:element name="img">
											<xsl:attribute name="src">./include/views/graphs/generateGraphs/generateMetricImage.php?session_id=<xsl:value-of select="//sid"/>&amp;cpt=1&amp;metric=<xsl:value-of select="metric_id"/>&amp;end=<xsl:value-of select="//end"/>&amp;start=<xsl:value-of select="$mstart"/></xsl:attribute>
										</xsl:element>
									</xsl:element>
									<xsl:element name="a">
										<xsl:attribute name="href">./include/views/graphs/generateGraphs/generateMetricImage.php?session_id=<xsl:value-of select="//sid"/>&amp;cpt=1&amp;metric=<xsl:value-of select="metric_id"/>&amp;end=<xsl:value-of select="//end"/>&amp;start=<xsl:value-of select="$mstart"/></xsl:attribute>
										<img src="./img/icones/16x16/save.gif" style="margin-right:5px;vertical-align:top;" />
									</xsl:element>
									<br/>
								</xsl:for-each>
							</xsl:if>
						 <br/>
					 </div> 
					</td>
				</tr>			
			</xsl:for-each>
	    </table>
	</div>
</xsl:if>
<xsl:if test="svc_zoom">
	    <div id="div2"   valign="top" align='left'>
		<form name="formu2">
    	    <table class="ajaxOption">
				<tr>
				<!--
				<td>
					<xsl:value-of select="//lang/giv_gg_tpl"/>
           		</td>
           		<td>
           			<xsl:element name="select">
						<xsl:attribute name="name">template_select</xsl:attribute>
						<xsl:attribute name="onChange">graph_4_host('<xsl:value-of select="//opid"/>'); return false;</xsl:attribute>
						<xsl:for-each select="//tpl">
							<xsl:element name='option'>
								<xsl:attribute name="value"><xsl:value-of select="tpl_id"/></xsl:attribute>
								<xsl:if test="//tpl = tpl_id">
									<xsl:attribute name="selected">selected</xsl:attribute>
								</xsl:if>
								<xsl:value-of select="tpl_name"/>
							</xsl:element>
						</xsl:for-each>

					</xsl:element>
           		</td>
           		-->
           		<td>
					<xsl:element name='input'>
						<xsl:attribute name="onClick">graph_4_host('<xsl:value-of select="//opid"/>'); return false;</xsl:attribute>
						<xsl:attribute name="name">split</xsl:attribute>
						<xsl:attribute name="type">checkbox</xsl:attribute>
						<xsl:if test="//split = 1">
							<xsl:attribute name="checked">checked</xsl:attribute>
						</xsl:if>
					</xsl:element>
					<xsl:value-of select="//lang/giv_split_component"/>
           		</td>
				<xsl:for-each select="//metrics">
           		<td style="padding-left:10px;">
					<xsl:element name='input'>
						<xsl:attribute name="onClick">graph_4_host('<xsl:value-of select="//opid"/>'); return false;</xsl:attribute>
						<xsl:attribute name="type">checkbox</xsl:attribute>
						<xsl:attribute name="name">metric</xsl:attribute>
						<xsl:attribute name="value"><xsl:value-of select="metric_id"/></xsl:attribute>
						<xsl:if test="select = 1">
							<xsl:attribute name="checked">checked</xsl:attribute>
						</xsl:if>
					</xsl:element>
           			<xsl:value-of select="metric_name"/>
           		</td>
				</xsl:for-each>
				</tr>
        	</table>
		</form>
   	</div>
	<div>
		<table class="ListTable">
			<tr class="list_one">
				<td class='ListColLeft' valign="top" align='center' colspan="3"><xsl:value-of select="name"/></td>	
				<td style="text-align:right;width:42px;">
					<xsl:element name="a">
						<xsl:attribute name="href">./include/views/graphs/generateGraphs/generateImageZoom.php?session_id=<xsl:value-of select="//sid"/>&amp;<xsl:value-of select="//metricsTab"/>&amp;index=<xsl:value-of select="//index"/>&amp;end=<xsl:value-of select="//end"/>&amp;start=<xsl:value-of select="//start"/>&amp;warn=<xsl:value-of select="//warning"/>&amp;crit=<xsl:value-of select="//critical"/></xsl:attribute>
						<img src="./img/icones/16x16/save.gif" style="margin-right:5px;"/>
					</xsl:element>
					<xsl:element name="a">
						<xsl:attribute name="href">./include/views/graphs/exportData/ExportCSVServiceData.php?index=<xsl:value-of select="//index"/>&amp;sid=<xsl:value-of select="//sid"/>&amp;end=<xsl:value-of select="//end"/>&amp;start=<xsl:value-of select="//start"/></xsl:attribute>
						<img src="./img/icones/16x16/text_binary_csv.gif"/>
					</xsl:element>
				</td>
			</tr>
			<tr>
			<td class='ListColRight' style="text-align:right;">
				<xsl:element name='input'>
					<xsl:attribute name="onClick">prevPeriod();</xsl:attribute>
					<xsl:attribute name="type">button</xsl:attribute>
					<xsl:attribute name="name">prev</xsl:attribute>
					<xsl:attribute name="value">&lt;&lt;</xsl:attribute>
					<xsl:attribute name="style">height:100px;</xsl:attribute>
				</xsl:element>
			</td>
	    		<td class='ListColCenter' valign="top" align='center'>
			    	<div id="imggraph">
						<xsl:if test="//split = 0">
							<xsl:if test="//debug = 1">
								<xsl:element name="a">
									<xsl:attribute name="href">./include/views/graphs/generateGraphs/generateImageZoom.php?session_id=<xsl:value-of select="//sid"/>&amp;<xsl:value-of select="//metricsTab"/>&amp;index=<xsl:value-of select="//index"/>&amp;end=<xsl:value-of select="//end"/>&amp;start=<xsl:value-of select="//start"/>&amp;warn=<xsl:value-of select="//warning"/>&amp;crit=<xsl:value-of select="//critical"/></xsl:attribute>
								</xsl:element>
							</xsl:if>
							<xsl:element name="img">
						  		<xsl:attribute name="src">./include/views/graphs/generateGraphs/generateImageZoom.php?session_id=<xsl:value-of select="//sid"/>&amp;<xsl:value-of select="//metricsTab"/>&amp;index=<xsl:value-of select="//index"/>&amp;end=<xsl:value-of select="//end"/>&amp;start=<xsl:value-of select="//start"/>&amp;warn=<xsl:value-of select="//warning"/>&amp;crit=<xsl:value-of select="//critical"/></xsl:attribute>
							</xsl:element>
							<xsl:element name="a">
								<xsl:attribute name="href">./include/views/graphs/generateGraphs/generateImageZoom.php?session_id=<xsl:value-of select="//sid"/>&amp;<xsl:value-of select="//metricsTab"/>&amp;index=<xsl:value-of select="//index"/>&amp;end=<xsl:value-of select="//end"/>&amp;start=<xsl:value-of select="//start"/>&amp;warn=<xsl:value-of select="//warning"/>&amp;crit=<xsl:value-of select="//critical"/></xsl:attribute>
								<img src="./img/icones/16x16/save.gif" style="margin-right:5px;vertical-align:top;" />
							</xsl:element>
						</xsl:if>
						<xsl:if test="//split = 1">
							<xsl:for-each select="//metrics">
								<xsl:if test="select = 1">
									<xsl:element name="img">
									  	<xsl:attribute name="src">./include/views/graphs/generateGraphs/generateMetricImage.php?template_id=<xsl:value-of select="//tpl"/>&amp;session_id=<xsl:value-of select="//sid"/>&amp;cpt=1&amp;metric=<xsl:value-of select="metric_id"/>&amp;end=<xsl:value-of select="//end"/>&amp;start=<xsl:value-of select="//start"/></xsl:attribute>
									</xsl:element>
									<xsl:element name="a">
										<xsl:attribute name="href">./include/views/graphs/generateGraphs/generateMetricImage.php?template_id=<xsl:value-of select="//tpl"/>&amp;session_id=<xsl:value-of select="//sid"/>&amp;cpt=1&amp;metric=<xsl:value-of select="metric_id"/>&amp;end=<xsl:value-of select="//end"/>&amp;start=<xsl:value-of select="//start"/></xsl:attribute>
										<img src="./img/icones/16x16/save.gif" style="margin-right:5px;vertical-align:top;" />
									</xsl:element>
									<br/>
								</xsl:if>
							</xsl:for-each>
						</xsl:if>
					<br/>
					</div>
				</td>
			<td class='ListColLeft' style="text-align:left;">
				<xsl:element name='input'>
					<xsl:attribute name="onClick">nextPeriod();</xsl:attribute>
					<xsl:attribute name="type">button</xsl:attribute>
					<xsl:attribute name="name">next</xsl:attribute>
					<xsl:attribute name="value">&gt;&gt;</xsl:attribute>
					<xsl:attribute name="style">height:100px;</xsl:attribute>
				</xsl:element>
			</td>
			</tr>
	    </table>
	</div>
</xsl:if>
<xsl:if test="//multi_svc">
	<div id="div2"   valign="top" align='left'>
		<form name="formu2">
    	    <table class="ajaxOption">
				<tr>
	           		<td>
						<xsl:element name='input'>
							<xsl:attribute name="onClick">graph_4_host('<xsl:value-of select="//opid"/>', multi); return false;</xsl:attribute>
							<xsl:attribute name="name">split</xsl:attribute>
							<xsl:attribute name="type">checkbox</xsl:attribute>
							<xsl:if test="//splitvalue = 1">
								<xsl:attribute name="checked">checked</xsl:attribute>
							</xsl:if>
						</xsl:element>
						<xsl:value-of select="//lang/giv_split_component"/>
	           		</td>
	           		<td>
						<xsl:element name='input'>
							<xsl:attribute name="onClick">graph_4_host('<xsl:value-of select="//opid"/>', multi); return false;</xsl:attribute>
							<xsl:attribute name="name">status</xsl:attribute>
							<xsl:attribute name="type">checkbox</xsl:attribute>
							<xsl:if test="//status = 1">
								<xsl:attribute name="checked">checked</xsl:attribute>
							</xsl:if>
						</xsl:element>
						<xsl:value-of select="//lang/status"/>
	           		</td>
				</tr>
        	</table>
		</form>
    	</div>
		<div>
			<table class="ListTable">
			<xsl:for-each select="//multi_svc">
		        <tr class="list_one">
					<td style="text-align:right;width:42px;" colspan="3">
						<xsl:element name="a">
							<xsl:attribute name="href">./include/views/graphs/generateGraphs/generateImage.php?session_id=<xsl:value-of select="sid"/>&amp;index=<xsl:value-of select="index"/>&amp;end=<xsl:value-of select="end"/>&amp;start=<xsl:value-of select="start"/></xsl:attribute>
							<img src="./img/icones/16x16/save.gif" style="margin-right:5px;"/>
						</xsl:element>
						<xsl:element name="a">
							<xsl:attribute name="href">./include/views/graphs/exportData/ExportCSVServiceData.php?index=<xsl:value-of select="index"/>&amp;sid=<xsl:value-of select="//sid"/>&amp;end=<xsl:value-of select="end"/>&amp;start=<xsl:value-of select="start"/></xsl:attribute><img src="./img/icones/16x16/text_binary_csv.gif"/>
						</xsl:element>
					</td>
				</tr>
				<tr>
			<td class='ListColRight' style="text-align:right;">
				<xsl:element name='input'>
					<xsl:attribute name="onClick">prevPeriod();</xsl:attribute>
					<xsl:attribute name="type">button</xsl:attribute>
					<xsl:attribute name="name">prev</xsl:attribute>
					<xsl:attribute name="value">&lt;&lt;</xsl:attribute>
					<xsl:attribute name="style">height:100px;</xsl:attribute>
				</xsl:element>
			</td>
	    			<td class='ListColCenter' valign="top" align='center'>
			    	<div id="imggraph">
						<xsl:if test="split = 0">
							<xsl:element name="a">
								<xsl:attribute name="onClick">multi=0;graph_4_host('<xsl:value-of select="opid"/>', ''); return false;</xsl:attribute>
								<xsl:attribute name="href">#</xsl:attribute>
								<xsl:element name="img">
								  	<xsl:attribute name="src">./include/views/graphs/generateGraphs/generateImage.php?session_id=<xsl:value-of select="sid"/>&amp;index=<xsl:value-of select="index"/>&amp;end=<xsl:value-of select="end"/>&amp;start=<xsl:value-of select="start"/></xsl:attribute>
								</xsl:element>
							</xsl:element>
							<xsl:element name="a">
								<xsl:attribute name="href">./include/views/graphs/generateGraphs/generateImage.php?session_id=<xsl:value-of select="sid"/>&amp;index=<xsl:value-of select="index"/>&amp;end=<xsl:value-of select="end"/>&amp;start=<xsl:value-of select="start"/></xsl:attribute>
								<img src="./img/icones/16x16/save.gif" style="margin-right:5px;vertical-align:top;" />
							</xsl:element>
							<xsl:element name="a">
								<xsl:attribute name="href">./include/views/graphs/generateGraphs/generateImage.php?session_id=<xsl:value-of select="sid"/>&amp;index=<xsl:value-of select="index"/>&amp;end=<xsl:value-of select="end"/>&amp;start=<xsl:value-of select="start"/></xsl:attribute>
								<img src="./img/icones/16x16/view.gif" style="margin-right:5px;vertical-align:top;" />
							</xsl:element>
						</xsl:if>
						<xsl:if test="split = 1">
							<xsl:for-each select="metrics">
								<xsl:if test="select = 1">
									<xsl:element name="a">
										<xsl:attribute name="onClick">graph_4_host('<xsl:value-of select="//opid"/>', ''); return false;</xsl:attribute>
										<xsl:attribute name="href">#</xsl:attribute>
										<xsl:element name="img">
									  		<xsl:attribute name="src">./include/views/graphs/generateGraphs/generateMetricImage.php?session_id=<xsl:value-of select="//sid"/>&amp;cpt=1&amp;metric=<xsl:value-of select="metric_id"/>&amp;end=<xsl:value-of select="//end"/>&amp;start=<xsl:value-of select="//start"/></xsl:attribute>
										</xsl:element>
									</xsl:element>
									<xsl:element name="a">
										<xsl:attribute name="href">./include/views/graphs/generateGraphs/generateMetricImage.php?session_id=<xsl:value-of select="//sid"/>&amp;cpt=1&amp;metric=<xsl:value-of select="metric_id"/>&amp;end=<xsl:value-of select="//end"/>&amp;start=<xsl:value-of select="//start"/></xsl:attribute>
										<img src="./img/icones/16x16/save.gif" style="margin-right:5px;vertical-align:top;" />
									</xsl:element>
								<br/>
								</xsl:if>
							</xsl:for-each>
						</xsl:if>
						<xsl:if test="status = 1">
							<br/>
							<xsl:element name="img">
							  	<xsl:attribute name="src">./include/views/graphs/graphStatus/displayServiceStatus.php?session_id=<xsl:value-of select="sid"/>&amp;index=<xsl:value-of select="index"/>&amp;end=<xsl:value-of select="end"/>&amp;start=<xsl:value-of select="start"/></xsl:attribute>
							</xsl:element>
						</xsl:if>
					</div> 
				</td>
			<td class='ListColLeft' style="text-align:left;">
				<xsl:element name='input'>
					<xsl:attribute name="onClick">nextPeriod();</xsl:attribute>
					<xsl:attribute name="type">button</xsl:attribute>
					<xsl:attribute name="name">next</xsl:attribute>
					<xsl:attribute name="value">&gt;&gt;</xsl:attribute>
					<xsl:attribute name="style">height:100px;</xsl:attribute>
				</xsl:element>
			</td>
			</tr>
		</xsl:for-each>
	</table>
</div>
</xsl:if>
</xsl:template>
</xsl:stylesheet>