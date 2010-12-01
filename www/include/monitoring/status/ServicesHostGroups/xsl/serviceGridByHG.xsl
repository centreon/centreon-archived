<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:template match="/">
	<table class="ListTable">
	<tr class='ListHeader'>
		<td colspan="2"  class="ListColHeaderLeft" style="white-space:nowrap;" id="alias" width="200"><xsl:value-of select="//i/host_name"/></td>
		<xsl:if test="//i/s = 1">
			<td class="ListColHeaderCenter" style="white-space:nowrap;" id="current_state" width="40">Status</td>
		</xsl:if>
		<td class="ListColHeaderLeft" style="white-space:nowrap;" id="service_description"><xsl:value-of select="//i/services"/></td>
	</tr>
	<xsl:for-each select="//hg">		
			<tr class='list_lvl_1'>
				<xsl:if test="//i/s = 1">
					<td colspan="4" id="hostGroup_name"><xsl:value-of select="hgn"/></td>
				</xsl:if>
				<xsl:if test="//i/s = 0">
					<td colspan="3" id="hostGroup_name"><xsl:value-of select="hgn"/></td>
				</xsl:if>
			</tr>			
			<xsl:for-each select="l">
			<tr>
				<xsl:attribute name="id">trStatus</xsl:attribute>
	  			<xsl:attribute name="class"><xsl:value-of select="@class" /></xsl:attribute>
				<td class="ListColLeft"  width="160" valign="top">
					<xsl:if test="hico != 'none'">
						<xsl:element name="img">
						  	<xsl:attribute name="src">./img/media/<xsl:value-of select="hico"/></xsl:attribute>
							<xsl:attribute name="style">padding-right:4px;</xsl:attribute>
						</xsl:element>
					</xsl:if>
					<xsl:element name="a">
					  	<xsl:attribute name="href">main.php?p=201&amp;o=hd&amp;host_name=<xsl:value-of select="hnl"/></xsl:attribute>
						<xsl:attribute name="class">infobulle</xsl:attribute>
						<xsl:attribute name="onmouseover">displayPOPUP('host', '<xsl:value-of select="hid"/>_<xsl:value-of select="hcount"/>', '<xsl:value-of select="hid"/>');</xsl:attribute>
						<xsl:attribute name="onmouseout">hiddenPOPUP('<xsl:value-of select="hid"/>_<xsl:value-of select="hcount"/>');</xsl:attribute>
						<xsl:value-of select="hn"/>
						<xsl:element name="span">
							<xsl:attribute name="id">span_<xsl:value-of select="hid"/>_<xsl:value-of select="hcount"/></xsl:attribute>
						</xsl:element>
					</xsl:element>
				</td>
				<td class="ListColLeft" width="36" valign="top">
					<xsl:element name="a">
					  	<xsl:attribute name="href">main.php?o=svc&amp;p=20201&amp;search=<xsl:value-of select="hnl"/></xsl:attribute>
							<xsl:element name="img">
							  	<xsl:attribute name="src">./img/icones/16x16/view.gif</xsl:attribute>
							</xsl:element>
					</xsl:element>
					<xsl:element name="a">
					  	<xsl:attribute name="href">main.php?p=4&amp;mode=0&amp;svc_id=<xsl:value-of select="hnl"/></xsl:attribute>
							<xsl:element name="img">
							  	<xsl:attribute name="src">./img/icones/16x16/column-chart.gif</xsl:attribute>
							</xsl:element>
					</xsl:element>
				</td>
				<xsl:if test="//i/s = 1">
				<td class="ListColCenter">
					<xsl:attribute name="style">
						background-color:<xsl:value-of select="hc"/>;
					</xsl:attribute>
					<xsl:value-of select="hs"/>
				</td>
				</xsl:if>
				<td class="ListColLeft">
					<xsl:for-each select="svc">
					<span>
						<xsl:attribute name="style">
							background-color:<xsl:value-of select="sc"/>;
						</xsl:attribute>
						<xsl:element name="a">
						  	<xsl:attribute name="href">main.php?o=svcd&amp;p=202&amp;host_name=<xsl:value-of select="../hnl"/>&amp;service_description=<xsl:value-of select="snl"/></xsl:attribute>
							<xsl:value-of select="sn"/>
						</xsl:element>
					</span>&#160;						
					</xsl:for-each>
				</td>
			</tr>
		</xsl:for-each>	
</xsl:for-each>
</table>
</xsl:template>
</xsl:stylesheet>