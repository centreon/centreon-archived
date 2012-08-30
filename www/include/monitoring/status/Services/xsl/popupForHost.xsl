<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:template match="//reponse">
	<table width="350">
		<tr class="ListHeaderPopup">
			<td>
				<xsl:element name="img">
					<xsl:attribute name="src"><xsl:value-of select="ico"/></xsl:attribute>
				</xsl:element>
			</td>			
			<td class="ColPopup" style="width:100%;text-align:right;"><xsl:value-of select="hostname"/>&#160;//&#160;<xsl:value-of select="address"/>&#160;</td>
		</tr>
		<tr class='list_two'>
			<td colspan="2" class="ColPopup">
				<xsl:attribute name="style">
					background-color:<xsl:value-of select="current_state/@color"/>;
    			</xsl:attribute>				
				<b><xsl:value-of select="plugin_output"/></b>
			</td>
		</tr>
		<xsl:if test="notes != ''">
		<tr class='list_separator'>
			<td class='separator' colspan='2'><xsl:value-of select='tr4'></xsl:value-of></td>
		</tr>
		<tr class='list_one'>
			<td class="ColPopup">&#160;<xsl:value-of select="notes/@name"/></td>
			<td class="ColPopup">&#160;<xsl:value-of select="notes"/></td>
		</tr>
		</xsl:if>
		<tr class='list_separator' colspan='2'>
			<td class='separator' colspan='2'><xsl:value-of select='tr5'></xsl:value-of></td>
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
			<td class="ColPopup">&#160;<xsl:value-of select="current_state_name"/></td>
			<td class="ColPopup">
				<xsl:attribute name="style">
					background-color:<xsl:value-of select="current_state/@color"/>;
				</xsl:attribute>
				&#160;<xsl:value-of select="current_state"/>
			</td>
		</tr>
		<tr class='list_one'>
			<td class="ColPopup">&#160;<xsl:value-of select="state_type_name"/></td>
			<td class="ColPopup">&#160;<xsl:value-of select="state_type"/></td>
		</tr>
		<tr class='list_two'>
			<td class="ColPopup">&#160;<xsl:value-of select="percent_state_change_name"/></td>
			<td class="ColPopup">&#160;<xsl:value-of select="percent_state_change"/> %</td>
		</tr>
		<tr class='list_separator'>
			<td class="separator" colspan="2"><xsl:value-of select="tr1"/></td>
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
		<tr class='list_one'>
			<td class="ColPopup">&#160;<xsl:value-of select="last_update_name"/></td>
			<td class="ColPopup">&#160;<xsl:value-of select="last_update"/></td>
		</tr>
		<tr class='list_separator'>
			<td class="separator" colspan="2"><xsl:value-of select="tr2"/></td>
		</tr>
		<tr class='list_two'>
			<td class="ColPopup">&#160;<xsl:value-of select="last_notification_name"/></td>
			<td class="ColPopup">&#160;<xsl:value-of select="last_notification"/></td>
		</tr>
		<tr class='list_one'>
			<td class="ColPopup">&#160;<xsl:value-of select="next_notification_name"/></td>
			<td class="ColPopup">&#160;<xsl:value-of select="next_notification"/></td>
		</tr>
		<tr class='list_two'>
			<td class="ColPopup">&#160;<xsl:value-of select="current_notification_number_name"/></td>
			<td class="ColPopup">&#160;<xsl:value-of select="current_notification_number"/></td>
		</tr>
		<tr class='list_separator'>
			<td class="separator" colspan="2"><xsl:value-of select="tr3"/></td>
		</tr>
		<tr class='list_one'>
			<td class="ColPopup">&#160;<xsl:value-of select="last_time_up/@name"/></td>
			<td class="ColPopup">&#160;<xsl:value-of select="last_time_up"/></td>
		</tr>
		<tr class='list_two'>
			<td class="ColPopup">&#160;<xsl:value-of select="last_time_down/@name"/></td>
			<td class="ColPopup">&#160;<xsl:value-of select="last_time_down"/></td>
		</tr>
		<tr class='list_one'>
			<td class="ColPopup">&#160;<xsl:value-of select="last_time_unreachable/@name"/></td>
			<td class="ColPopup">&#160;<xsl:value-of select="last_time_unreachable"/></td>
		</tr>		
	</table>
</xsl:template>
</xsl:stylesheet>