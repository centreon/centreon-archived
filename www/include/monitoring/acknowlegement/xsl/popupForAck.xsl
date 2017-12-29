<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:template match="//response">
	<table class="ListTable">
		<tr class="ListHeader">
			<td class="ListColHeaderCenter" style="padding: 4px;"><xsl:value-of select="//label/author"/></td>
			<td class="ListColHeaderCenter" style="padding: 4px;"><xsl:value-of select="//label/entrytime"/></td>
			<td class="ListColHeaderCenter" style="padding: 4px;"><xsl:value-of select="//label/persistent"/></td>
			<td class="ListColHeaderCenter" style="padding: 4px;"><xsl:value-of select="//label/sticky"/></td>
			<td class="ListColHeaderCenter" style="padding: 4px;"><xsl:value-of select="//label/comment"/></td>
		</tr>
		<xsl:for-each select="//ack">
			<xsl:element name='tr'>
				<xsl:attribute name='class'><xsl:value-of select="@class"/></xsl:attribute>
				<td style="padding: 4px;"><xsl:value-of select="author"/></td>
				<td style="padding: 4px;"><xsl:value-of select="entrytime"/></td>
				<td style="padding: 4px;"><xsl:value-of select="persistent"/></td>
				<td style="padding: 4px;"><xsl:value-of select="sticky"/></td>				
				<td style="padding: 4px;"><xsl:value-of select="comment"/></td>
			</xsl:element>
		</xsl:for-each>
	</table>
</xsl:template>
</xsl:stylesheet>