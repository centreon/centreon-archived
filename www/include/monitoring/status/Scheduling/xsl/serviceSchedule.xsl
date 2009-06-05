<?xml version="1.0" encoding="ISO-8859-1" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:variable name="i" select="//i"/>
<xsl:template match="/">
<table class="ListTable">
	<tr class='ListHeader'>
		<td class="ListColHeaderPicker"><input type="checkbox" name="checkall" onclick="checkUncheckAll(this);"/></td>
		<td colspan="2"  class="ListColHeaderCenter" style="white-space:nowrap;" id="host_name"></td>
		<td colspan="3" class="ListColHeaderCenter" style="white-space:nowrap;" id="service_description"></td>
		<td class="ListColHeaderCenter" style="white-space:nowrap;" id="last_check"></td>
		<td class="ListColHeaderCenter" style="white-space:nowrap;" id="next_check">next check</td>
		<td class="ListColHeaderCenter" style="white-space:nowrap;" id="active_check">active_check</td>
	</tr>
	<xsl:for-each select="//l">
	<tr>
		<xsl:attribute name="id">trStatus</xsl:attribute>
  		<xsl:attribute name="class"><xsl:value-of select="@class" /></xsl:attribute>
		<td class="ListColPicker">
			<xsl:element name="input">
				<xsl:attribute name="type">checkbox</xsl:attribute>
				<xsl:attribute name="value">1</xsl:attribute>
				<xsl:attribute name="name">select[<xsl:value-of select="hn"/>;<xsl:value-of select="sd"/>]</xsl:attribute>
			</xsl:element>
		</td>
		<td class="ListColLeft">
			<xsl:if test="hn/@none = 0">
				<xsl:element name="a">
				  	<xsl:attribute name="href">main.php?p=201&amp;o=hd&amp;host_name=<xsl:value-of select="hn"/></xsl:attribute>
					<xsl:attribute name="class">pop</xsl:attribute>
					<xsl:value-of select="hn"/>
				</xsl:element>
			</xsl:if>
		</td>
		<td class="ListColLeft">
			<xsl:if test="hn/@none = 0">
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
			</xsl:if>
		</td>
		<td class="ListColLeft">
			<xsl:element name="a">
			  	<xsl:attribute name="href">main.php?p=202&amp;o=svcd&amp;host_name=<xsl:value-of select="hn"/>&amp;service_description=<xsl:value-of select="sd"/></xsl:attribute>
				<xsl:value-of select="sd"/>
			</xsl:element>
			<xsl:if test="pa = 1">
					<xsl:element name="img">
					  	<xsl:attribute name="src">./img/icones/16x16/worker.gif</xsl:attribute>
					</xsl:element>
			</xsl:if>
			<xsl:if test="ac = 0 and pc = 1">
					<xsl:element name="img">
					  	<xsl:attribute name="src">./img/icones/14x14/gears_pause.gif</xsl:attribute>
					</xsl:element>
			</xsl:if>
			<xsl:if test="ac = 0 and pc = 0">
					<xsl:element name="img">
					  	<xsl:attribute name="src">./img/icones/14x14/gears_stop.gif</xsl:attribute>
					</xsl:element>
			</xsl:if>
		</td>
		<td class="ListColRight">
			<xsl:if test="is = 1">
					<xsl:element name="img">
					  	<xsl:attribute name="src">./img/icones/16x16/flapping.gif</xsl:attribute>
					</xsl:element>
			</xsl:if>
			<xsl:if test="ne = 0">
					<xsl:element name="img">
					  	<xsl:attribute name="src">./img/icones/14x14/noloudspeaker.gif</xsl:attribute>
					</xsl:element>
			</xsl:if>
		</td>
		<td class="ListColRight">
			<xsl:element name="a">
			  	<xsl:attribute name="href">main.php?p=4&amp;mode=0&amp;svc_id=<xsl:value-of select="hn"/>;<xsl:value-of select="sd"/></xsl:attribute>
					<xsl:element name="img">
					  	<xsl:attribute name="src">./img/icones/16x16/column-chart.gif</xsl:attribute>
					</xsl:element>
			</xsl:element>
		</td>
        <td class="ListColCenter">
        	<xsl:value-of select="lc"/>
        </td>
		<td class="ListColCenter" style="white-space:nowrap;">
			<xsl:value-of select="nc"/>
		</td>
		<td class="ListColCenter" style="white-space:nowrap;">
			<xsl:value-of select="ac"/>
		</td>
	</tr>
</xsl:for-each>
</table>
</xsl:template>
</xsl:stylesheet>