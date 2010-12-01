<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:template match="/">
	<table class="ListTable">
	<tr class='ListHeader'>
		<td colspan="2" class="ListColHeaderLeft" style="white-space:nowrap;" id="host_name" width="150"></td>
		<xsl:if test="//i/s = 1">
			<td class="ListColHeaderCenter" style="white-space:nowrap;" id="host_state" width="40"></td>
		</xsl:if>
		<td class="ListColHeaderCenter" style="white-space:nowrap;" id="services"></td>
	</tr>
	<xsl:for-each select="//sg">		
			<tr class='list_lvl_1'>
				<xsl:if test="//i/s = 1">
					<td colspan="4"><xsl:value-of select="sgn"/></td>
				</xsl:if>
				<xsl:if test="//i/s = 0">
					<td colspan="3"><xsl:value-of select="sgn"/></td>
				</xsl:if>
			</tr>			
			<xsl:for-each select="h">
			<tr>
				<xsl:attribute name="id">trStatus</xsl:attribute>
		  		<xsl:attribute name="class"><xsl:value-of select="@class" /></xsl:attribute>
				<td class="ListColLeft" width="160">
					<xsl:if test="hico != 'none'">
						<xsl:element name="img">
						  	<xsl:attribute name="src">./img/media/<xsl:value-of select="hico"/></xsl:attribute>
							<xsl:attribute name="style">padding-right:4px;</xsl:attribute>
						</xsl:element>
					</xsl:if>
					<xsl:element name="a">
					  	<xsl:attribute name="href">main.php?p=201&amp;o=hd&amp;host_name=<xsl:value-of select="hnl"/></xsl:attribute>
						<xsl:attribute name="class">infobulle</xsl:attribute>
						<xsl:attribute name="onmouseover">displayPOPUP('host', '<xsl:value-of select="hcount"/>_<xsl:value-of select="hid"/>', '<xsl:value-of select="hid"/>');</xsl:attribute>
						<xsl:attribute name="onmouseout">hiddenPOPUP('<xsl:value-of select="hcount"/>_<xsl:value-of select="hid"/>');</xsl:attribute>
						<xsl:value-of select="hn"/>
						<xsl:element name="span">
							<xsl:attribute name="id">span_<xsl:value-of select="hcount"/>_<xsl:value-of select="hid"/></xsl:attribute>
						</xsl:element>
					</xsl:element>
				</td>
				<td class="ListColLeft" width="40">
					<xsl:element name="a">
					  	<xsl:attribute name="href">main.php?o=svc&amp;p=20201&amp;search=<xsl:value-of select="hnl"/></xsl:attribute>
							<xsl:element name="img">
							  	<xsl:attribute name="src">./img/icones/16x16/view.gif</xsl:attribute>
							</xsl:element>
					</xsl:element>
					<xsl:element name="a">
					  	<xsl:attribute name="href">main.php?p=40201&amp;mode=0&amp;svc_id=<xsl:value-of select="hnl"/></xsl:attribute>
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