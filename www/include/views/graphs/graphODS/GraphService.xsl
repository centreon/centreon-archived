<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:variable name="i" select="//i"/>
<xsl:template match="/">


<xsl:if test="host">

	    <div id="div1" class="cachediv" style="padding-bottom:10px;">
    	    <table id="ListTable">
                <tr class="ListHeader">
                	<td class="FormHeader" colspan="2">lang.advanced</td>
                </tr>
               <tr class="list_one">
               		<td>form.period.label</td><td>form.period.html</td>
               </tr>
				<tr class="list_two">
					<td>form.start.label</td>
					<td><input name="start" type="text" value="" />  <input onclick="displayDatePicker('start')" name="startD" value="Modifier" type="button" /></td>
				</tr>
				<tr class="list_one">
					<td>form.end.label</td>
					<td><input name="end" type="text" value="" />  <input onclick="displayDatePicker('end')" name="endD" value="Modifier" type="button" /></td>
				</tr>
        	</table>
    	</div>
		<div valign="top" align='center'>
			<input onclick="DisplayHidden('div1');" name="advanced" value="Options" type="button" />
			<xsl:element name="input">
				<xsl:attribute name="type">button</xsl:attribute>
				<xsl:attribute name="name">graph</xsl:attribute>
				<xsl:attribute name="value">graph</xsl:attribute>
				<xsl:attribute name="onClick">graph_4_host('<xsl:value-of select="//opid"/>', '', ''); return false;</xsl:attribute>
			</xsl:element>
		</div>
<br/>

	<div>
		<table id="ListTable">
	        <tr class="ListHeader">
	        	<td class="FormHeader" colspan="2"><img src='./img/icones/16x16/column-chart.gif'/>Host : </td>
	        </tr>
			<xsl:for-each select="//svc">
		        <tr class="list_one">
					<td class='ListColLeft' valign="top" align='center'> <b>Service : <xsl:value-of select="name"/></b></td>
	
					<td style="text-align:right;width:42px;">
	
					<a href="">
						<img src="./img/icones/16x16/text_binary_csv.gif"/>
					</a>
	
					<a href=''>
						<img src="./img/icones/16x16/text_binary_xml.gif"/>
					</a>
					</td>
				</tr>
				<tr>
	    			<td class='ListColCenter' valign="top" align='center'>
				    	<div id="imggraph">

							<xsl:element name="a">
							<xsl:attribute name="onClick">graph_4_host('HS_<xsl:value-of select="service_id"/>', '', ''); return false;</xsl:attribute>
							<xsl:attribute name="href">#</xsl:attribute>

									<xsl:element name="img">
									  	<xsl:attribute name="src">
	./include/views/graphs/graphODS/generateImages/generateODSImage.php?session_id=<xsl:value-of select="//sid"/>&amp;index=<xsl:value-of select="index"/>&amp;end=<xsl:value-of select="//end"/>&amp;start=<xsl:value-of select="//start"/>
									  	</xsl:attribute>
									</xsl:element>



							</xsl:element>


						 <br/>
						 </div> 
					</td>
				</tr>			
				
			</xsl:for-each>
	    </table>
	</div>
</xsl:if>

<xsl:if test="svc">
	<div>
		<table id="ListTable">
	        <tr class="ListHeader">
	        	<td class="FormHeader" colspan="2"><img src='./img/icones/16x16/column-chart.gif'/>Service : </td>
	        </tr>
			<xsl:for-each select="//period">
		        <tr class="list_one">
					<td class='ListColLeft' valign="top" align='center'> <xsl:value-of select="name"/></td>
	
					<td style="text-align:right;width:42px;">
	
					<a href="">
						<img src="./img/icones/16x16/text_binary_csv.gif"/>
					</a>
	
					<a href=''>
						<img src="./img/icones/16x16/text_binary_xml.gif"/>
					</a>
					</td>
				</tr>
				<tr>
	    			<td class='ListColCenter' valign="top" align='center'>
				    	<div id="imggraph">

							<xsl:element name="a">
							<xsl:attribute name="onClick">graph_4_host('SS_<xsl:value-of select="//id"/>', '', ''); return false;</xsl:attribute>
							<xsl:attribute name="href">#</xsl:attribute>



									<xsl:element name="img">
									  	<xsl:attribute name="src">
	./include/views/graphs/graphODS/generateImages/generateODSImage.php?session_id=<xsl:value-of select="//sid"/>&amp;index=<xsl:value-of select="//index"/>&amp;end=<xsl:value-of select="end"/>&amp;start=<xsl:value-of select="start"/>
									  	</xsl:attribute>
									</xsl:element>

							</xsl:element>
						 <br/>
						 </div> 
					</td>
				</tr>			
				
			</xsl:for-each>
	    </table>
	</div>
</xsl:if>

<xsl:if test="svc_zoom">
	<div>
		<table id="ListTable">
	        <tr class="ListHeader">
	        	<td class="FormHeader" colspan="2"><img src='./img/icones/16x16/column-chart.gif'/>Service : </td>
	        </tr>
		        <tr class="list_one">
					<td class='ListColLeft' valign="top" align='center'> <xsl:value-of select="name"/></td>
	
					<td style="text-align:right;width:42px;">
	
					<a href="">
						<img src="./img/icones/16x16/text_binary_csv.gif"/>
					</a>
	
					<a href=''>
						<img src="./img/icones/16x16/text_binary_xml.gif"/>
					</a>
					</td>
				</tr>
				<tr>
	    			<td class='ListColCenter' valign="top" align='center'>
				    	<div id="imggraph">


									<xsl:element name="img">
									  	<xsl:attribute name="src">

	./include/views/graphs/graphODS/generateImages/generateODSImageZoom.php?session_id=<xsl:value-of select="//sid"/>&amp;<xsl:value-of select="//metrics"/>&amp;index=<xsl:value-of select="//index"/>&amp;end=<xsl:value-of select="//end"/>&amp;start=<xsl:value-of select="//start"/>



									  	</xsl:attribute>
									</xsl:element>

						 <br/>
						 </div> 
					</td>
				</tr>				
	    </table>
	</div>
</xsl:if>




</xsl:template>
</xsl:stylesheet>