<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:variable name="i" select="//i"/>
<xsl:template match="/">
<xsl:if test="host">
	<div id="div1" class="cachediv" style="padding-bottom:10px;">
		<form name="formu">
			<xsl:if test="host">
			<table class="ListTable">
				<tr class="ListHeader">
	            	<td class="FormHeader" ><xsl:value-of select="//lang/optionAdvanced"/></td>
	            </tr>
				<tr class="list_one">
	            	<td><xsl:value-of select="//lang/period"/><xsl:text> </xsl:text></td>
				</tr>
				<tr class="list_lvl_1">
					<td><xsl:value-of select="//lang/start"/>
						<xsl:text> </xsl:text> 
						<input id="StartDate" name="StartDate" type="text" value="" onclick="displayDatePicker('StartDate', this)" size="8" />
						<xsl:text> </xsl:text> 
						<input id="StartTime" name="StartTime" type="text" value="" onclick="displayTimePicker('StartTime', this)" size="4" />  
						<xsl:text> </xsl:text> 
						<xsl:value-of select="//lang/end"/>
						<xsl:text> </xsl:text> 
						<input id="EndDate" name="EndDate" type="text" value="" onclick="displayDatePicker('EndDate', this)" size="8" />
						<xsl:text> </xsl:text> 
						<input id="EndTime" name="EndTime" type="text" value="" onclick="displayTimePicker('EndTime', this)" size="4" />  
					</td>
				</tr>
	        </table>
		</form>
	</div>
	<div valign="top" align='center'>
		<input onclick="DisplayHidden('div1');" name="advanced" value="Options" type="button" />
		<xsl:text> </xsl:text> 
		<xsl:element name="input">
			<xsl:attribute name="type">button</xsl:attribute>
			<xsl:attribute name="name">graph</xsl:attribute>
			<xsl:attribute name="value">graph</xsl:attribute>
			<xsl:attribute name="onClick">graph_4_host('<xsl:value-of select="//opid"/>', this.form); return false;</xsl:attribute>
		</xsl:element>
	</div>
	<br/>
	<div>
		<table class="ListTable">
	        <tr class="ListHeader">
	        	<td class="FormHeader" colspan="2"><img src='./img/icones/16x16/column-chart.gif'/>Host : </td>
	        </tr>
			<xsl:for-each select="//svc">
	        <tr class="list_one">
				<td class='ListColLeft' valign="top" align='center'> <b>Service : <xsl:value-of select="name"/></b></td>
				<td style="text-align:right;width:42px;">
					<a href=""><img src="./img/icones/16x16/text_binary_csv.gif"/></a>
					<a href=''><img src="./img/icones/16x16/text_binary_xml.gif"/></a>
				</td>
			</tr>
			<tr>
				<td class='ListColCenter' valign="top" align='center'>
			   	<div id="imggraph">
				<xsl:element name="a">
					<xsl:attribute name="onClick">graph_4_host('HS_<xsl:value-of select="service_id"/>', ''); return false;</xsl:attribute>
					<xsl:attribute name="href">#</xsl:attribute>
						<xsl:element name="img">
						  	<xsl:attribute name="src">./include/views/graphs/graphODS/generateImages/generateODSImage.php?session_id=<xsl:value-of select="//sid"/>&amp;index=<xsl:value-of select="index"/>&amp;end=<xsl:value-of select="//end"/>&amp;start=<xsl:value-of select="//start"/></xsl:attribute>
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
		<table class="ListTable">
	        <tr class="ListHeader">
	        	<td class="FormHeader" colspan="2"><img src='./img/icones/16x16/column-chart.gif'/>Service : </td>
	        </tr>
			<xsl:for-each select="//period">
	        <tr class="list_one">
				<td class='ListColLeft' valign="top" align='center'> <xsl:value-of select="name"/></td>
				<td style="text-align:right;width:42px;">
					<a href=""><img src="./img/icones/16x16/text_binary_csv.gif"/></a>
					<a href=''><img src="./img/icones/16x16/text_binary_xml.gif"/></a>
				</td>
			</tr>
			<tr>
	    		<td class='ListColCenter' valign="top" align='center'>
			    	<div id="imggraph">
						<xsl:element name="a">
							<xsl:attribute name="onClick">graph_4_host('SS_<xsl:value-of select="//id"/>', ''); return false;</xsl:attribute>
							<xsl:attribute name="href">#</xsl:attribute>
							<xsl:element name="img">
							  	<xsl:attribute name="src">./include/views/graphs/graphODS/generateImages/generateODSImage.php?session_id=<xsl:value-of select="//sid"/>&amp;index=<xsl:value-of select="//index"/>&amp;end=<xsl:value-of select="end"/>&amp;start=<xsl:value-of select="start"/></xsl:attribute>
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
	<table class="ListTable">
        <tr class="ListHeader">
        	<td class="FormHeader" colspan="2"><img src='./img/icones/16x16/column-chart.gif'/>Service : </td>
        </tr>
        <tr class="list_one">
			<td class='ListColLeft' valign="top" align='center'> <xsl:value-of select="name"/></td>
			<td style="text-align:right;width:42px;">
				<a href=""><img src="./img/icones/16x16/text_binary_csv.gif"/></a>
				<a href=''><img src="./img/icones/16x16/text_binary_xml.gif"/></a>
			</td>
		</tr>
		<tr>
	    	<td class='ListColCenter' valign="top" align='center'>
				<div id="imggraph">
				<xsl:element name="img">
				  	<xsl:attribute name="src">./include/views/graphs/graphODS/generateImages/generateODSImageZoom.php?session_id=<xsl:value-of select="//sid"/>&amp;<xsl:value-of select="//metrics"/>&amp;index=<xsl:value-of select="//index"/>&amp;end=<xsl:value-of select="//end"/>&amp;start=<xsl:value-of select="//start"/></xsl:attribute>
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