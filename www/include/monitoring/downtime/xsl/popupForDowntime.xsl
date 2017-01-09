<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:template match="//response">
	<table class= "ListTable">
		<tr class="ListHeader">
			<th class="ListColHeaderCenter"><xsl:value-of select="//label/author"/></th>
			<th class="ListColHeaderCenter"><xsl:value-of select="//label/fixed"/></th>
			<th class="ListColHeaderCenter"><xsl:value-of select="//label/start"/></th>
			<th class="ListColHeaderCenter"><xsl:value-of select="//label/end"/></th>
			<th class="ListColHeaderCenter"><xsl:value-of select="//label/comment"/></th>
		</tr>
		<xsl:for-each select="//dwt">
			<xsl:element name='tr'>
				<!--<xsl:attribute name='class'><xsl:value-of select="@class"/></xsl:attribute>-->
				<td style="padding: 4px;"><xsl:value-of select="author"/></td>
				<td style="padding: 4px;"><xsl:value-of select="fixed"/></td>
				<td style="padding: 4px;"><xsl:value-of select="start"/></td>
				<td style="padding: 4px;"><xsl:value-of select="end"/></td>
				<td style="padding: 4px;"><xsl:value-of select="comment"/></td>
			</xsl:element>
		</xsl:for-each>
	</table>
</xsl:template>
</xsl:stylesheet>