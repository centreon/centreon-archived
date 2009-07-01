<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:variable name="i" select="//i"/>
<xsl:template match="/">
<table class="ListTable">
	<tr class='ListHeader'>
		<td class="ListColHeaderPicker"><input type="checkbox" name="checkall" onclick="checkUncheckAll(this);"/></td>
		<td colspan="2"  class="ListColHeaderCenter" style="white-space:nowrap;" id="host_name"></td>
		<td class="ListColHeaderCenter" style="white-space:nowrap;" id="current_state"></td>
		<td class="ListColHeaderCenter" style="white-space:nowrap;" id="ip"></td>
		<td class="ListColHeaderCenter" style="white-space:nowrap;" id="last_check"></td>
		<td class="ListColHeaderCenter" style="white-space:nowrap;" id="last_state_change"></td>
		<td class="ListColHeaderCenter" style="white-space:nowrap;" id="current_check_attempt"></td>
		<td class="ListColHeaderCenter" style="white-space:nowrap;" id="plugin_output"></td>	
	</tr>
	<xsl:for-each select="//l">
	<tr>
		<xsl:attribute name="id">trStatus</xsl:attribute>
  		<xsl:attribute name="class"><xsl:value-of select="@class" /></xsl:attribute>
		<td class="ListColPicker">
		<xsl:element name="input">
			<xsl:attribute name="type">checkbox</xsl:attribute>
			<xsl:attribute name="value">1</xsl:attribute>
			<xsl:attribute name="id"><xsl:value-of select="hn"/></xsl:attribute>
			<xsl:attribute name="name">select[<xsl:value-of select="hn"/>]</xsl:attribute>
			<xsl:attribute name="onclick">
            	if (this.checked) {
                	putInSelectedElem('<xsl:value-of select="hn"/>');
                }
                else {
                	removeFromSelectedElem('<xsl:value-of select="hn"/>');
                }
            </xsl:attribute>
		</xsl:element>
		</td>
		<td class="ListColLeft">
			<xsl:if test="ico != ''">
				<xsl:element name="img">
				  	<xsl:attribute name="src">./img/media/<xsl:value-of select="ico"/></xsl:attribute>
				  	<xsl:attribute name="width">16</xsl:attribute>
					<xsl:attribute name="height">16</xsl:attribute>
				</xsl:element>
			</xsl:if>
			<xsl:element name="a">
			  	<xsl:attribute name="href">main.php?p=201&amp;o=hd&amp;host_name=<xsl:value-of select="hn"/></xsl:attribute>
				<xsl:attribute name="class">pop</xsl:attribute>
				<xsl:value-of select="hn"/>
			</xsl:element>
		</td>
		<td class="ListColLeft">
			<xsl:if test="hdtm = 1">
					<xsl:element name="img">
					  	<xsl:attribute name="src">./img/icones/16x16/warning.gif</xsl:attribute>
					</xsl:element>
			</xsl:if>
			<xsl:if test="ha = 1">
					<xsl:element name="img">
					  	<xsl:attribute name="src">./img/icones/16x16/worker.gif</xsl:attribute>
					</xsl:element>
			</xsl:if>
			<xsl:if test="hae = 0 and hpe = 1">
					<xsl:element name="img">
					  	<xsl:attribute name="src">./img/icones/14x14/gears_pause.gif</xsl:attribute>
					</xsl:element>
			</xsl:if>
			<xsl:if test="hae = 0 and hpe = 0">
					<xsl:element name="img">
					  	<xsl:attribute name="src">./img/icones/14x14/gears_stop.gif</xsl:attribute>
					</xsl:element>
			</xsl:if>
			<xsl:if test="ne = 0">
					<xsl:element name="img">
					  	<xsl:attribute name="src">./img/icones/14x14/noloudspeaker.gif</xsl:attribute>
					</xsl:element>
			</xsl:if>
		</td>
		<td class="ListColCenter">
			<xsl:attribute name="style">
				background-color:<xsl:value-of select="hc"/>;
			</xsl:attribute>
			<xsl:value-of select="cs"/>
		</td>
		<td class="ListColRight"><xsl:value-of select="a"/></td>
	    <td class="ListColRight"><xsl:value-of select="lc"/></td>
	    <td class="ListColRight"><xsl:value-of select="lsc"/></td>
	    <td class="ListColCenter"><xsl:value-of select="tr"/></td>
		<td class="ListColNoWrap"><xsl:value-of select="ou"/></td>
	</tr>
</xsl:for-each>
</table>
</xsl:template>
</xsl:stylesheet>