<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:variable name="i" select="//i"/>
<xsl:template match="/">
<table id="ListTable">
	<tr class='ListHeader'>
		<td class="ListColHeaderCenter" style="white-space:nowrap;" id="hostGroup_name"></td>
		<td class="ListColHeaderCenter" style="white-space:nowrap;" id="host_status"></td>
		<td class="ListColHeaderCenter" style="white-space:nowrap;" id="service_status"></td>
	</tr>
	<xsl:for-each select="//l">
	<tr>
		<xsl:attribute name="id">trStatus</xsl:attribute>
  		<xsl:attribute name="class"><xsl:value-of select="@class" /></xsl:attribute>

				<td class="ListColLeft">
						<xsl:element name="a">
						  	<xsl:attribute name="href">oreon.php?p=201&amp;o=hd&amp;host_name=<xsl:value-of select="hn"/></xsl:attribute>
							<xsl:attribute name="class">pop</xsl:attribute>
  							<xsl:value-of select="hn"/>
						</xsl:element>
				</td>
				<td class="ListColCenter">
					<xsl:value-of select="hu"/>
				</td>
				<td class="ListColRight">
					<xsl:value-of select="sok"/>
				</td>
	</tr>
</xsl:for-each>
</table>
</xsl:template>
</xsl:stylesheet>
