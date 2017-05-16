<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:template match="//reponse">
	<table class="ListTable table">
		<tr class='ListHeader'>
			<xsl:attribute name="style">
				background-color:<xsl:value-of select="color"/>;
				white-space:normal;
			</xsl:attribute>
			<td class="ColPopup FormHeader" colspan="2">
				<xsl:element name="h3">
					<xsl:attribute name="style">
						color: #ffffff;
					</xsl:attribute>
					<xsl:element name="img">
						<xsl:attribute name="src"><xsl:value-of select="ico"/></xsl:attribute>
						<xsl:attribute name="class">ico-16</xsl:attribute>
						<xsl:attribute name="style">vertical-align:middle;</xsl:attribute>
					</xsl:element>
					| <xsl:value-of select="service_description"/>
					<p style="padding-left: 26px; font-size: 12px;float:right;">
						<xsl:value-of select="hostname"/>
					</p>
				</xsl:element>
			</td>
		</tr>
		<tr class="list_lvl_1">
			<td class='ListColLvl1_name' colspan="2">
				<h4><xsl:value-of select="tr6"/></h4>
			</td>
		</tr>
		<tr>
			<td class="ColPopupNoWrap" colspan="2">
				<h4><xsl:value-of select="plugin_output"/></h4>
			</td>
		</tr>
		<xsl:if test="notes != ''">
		<tr class="list_lvl_1">
			<td class='ListColLvl1_name' colspan="2">
				<h4><xsl:value-of select="tr4"/></h4>
			</td>
		</tr>		<tr class='list_one'>
			<td class="ColPopup"><xsl:value-of select="notes/@name"/></td>
			<td class="ColPopup"><xsl:value-of select="notes"/></td>
		</tr>
		</xsl:if>
		<tr class="list_lvl_1">
			<td class='ListColLvl1_name' colspan='2'>
				<h4><xsl:value-of select='tr5'></xsl:value-of></h4>
			</td>
		</tr>
		<tr class='list_two'>
			<td class="ColPopup">&#160;<xsl:value-of select="last_state_change_name"/></td>
			<td class="ColPopup">&#160;<xsl:value-of select="last_state_change"/></td>
		</tr>
		<tr class='list_one'>
			<td class="ColPopup">&#160;<xsl:value-of select="duration_name"/></td>
			<td class="ColPopup">&#160;<xsl:value-of select="duration"/></td>
		</tr>
		<tr class='list_two'>
			<td class="ColPopup">&#160;<xsl:value-of select="state_type_name"/></td>
			<td class="ColPopup">&#160;<xsl:value-of select="state_type"/></td>
		</tr>
		<tr class='list_one'>
			<td class="ColPopup">&#160;<xsl:value-of select="long_name"/></td>
			<td class="ColPopup">
			    <xsl:for-each select="long_output_data">
			        &#160;<xsl:value-of select="lo_data"/><br />
			    </xsl:for-each>
			</td>
		</tr>
		<tr class='list_two'>
			<td class="ColPopup">&#160;<xsl:value-of select="performance_data_name"/></td>
			<td class="ColPopup">
			<xsl:for-each select="performance_data">
				&#160;<xsl:value-of select="perf_data"/><br />
			</xsl:for-each>
			</td>
		</tr>
		<tr class="list_lvl_1">
			<td class='ListColLvl1_name' colspan="2">
				<h4><xsl:value-of select="tr1"/></h4>
			</td>
		</tr>
		<tr class='list_two'>
			<td class="ColPopup">&#160;<xsl:value-of select="last_check_name"/></td>
			<td class="ColPopup">&#160;<xsl:value-of select="last_check"/></td>
		</tr>
		<tr class='list_one'>
			<td class="ColPopup">&#160;<xsl:value-of select="next_check_name"/></td>
			<td class="ColPopup">&#160;<xsl:value-of select="next_check"/></td>
		</tr>
		<tr class='list_two'>
			<td class="ColPopup">&#160;<xsl:value-of select="check_latency_name"/></td>
			<td class="ColPopup">&#160;<xsl:value-of select="check_latency"/> s</td>
		</tr>
		<tr class='list_one'>
			<td class="ColPopup">&#160;<xsl:value-of select="check_execution_time_name"/></td>
			<td class="ColPopup">&#160;<xsl:value-of select="check_execution_time"/> s</td>
		</tr>
		<tr class='list_two'>
			<td class="ColPopup">&#160;<xsl:value-of select="is_downtime_name"/></td>
			<td class="ColPopup">&#160;<xsl:value-of select="is_downtime"/></td>
		</tr>
		<tr class="list_lvl_1">
			<td class='ListColLvl1_name' colspan="2">
				<h4><xsl:value-of select="tr2"/></h4>
			</td>
		</tr>
		<tr class='list_two'>
			<td class="ColPopup">&#160;<xsl:value-of select="last_notification_name"/></td>
			<td class="ColPopup">&#160;<xsl:value-of select="last_notification"/></td>
		</tr>
		<tr class="list_lvl_1">
			<td class='ListColLvl1_name' colspan="2">
				<h4><xsl:value-of select="tr3"/></h4>
			</td>
		</tr>
		<tr class='list_one'>
			<td class="ColPopup">&#160;<xsl:value-of select="last_time_name"/><xsl:value-of select="last_time_status"/></td>
			<td class="ColPopup">&#160;<xsl:value-of select="last_time"/></td>
		</tr>
	</table>
</xsl:template>
</xsl:stylesheet>
