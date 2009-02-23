<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:template match="//reponse">
	<table style="padding:5px;margin:5px;z-index:15;">
		<tr class="ListHeaderPopup">
			<td rowspan="2">
				<xsl:element name="img">
					<xsl:attribute name="src"><xsl:value-of select="ico"/></xsl:attribute>
				</xsl:element>
			</td>
			<td class="ColPopup"><xsl:value-of select="hostname"/></td>
		</tr>
		<tr class="ListHeaderPopup">
			<td class="ColPopup" style="width:100%;text-aling:right;"><xsl:value-of select="service_description"/></td>
		</tr>
		<tr class='list_two'>
			<td colspan="2" class="ColPopup">
				<xsl:attribute name="style">
					background-color:<xsl:value-of select="current_state/@color"/>;
    			</xsl:attribute>
				<xsl:value-of select="plugin_output"/>
			</td>
		</tr>
		<tr class='list_two'>
			<td class="ColPopup"><xsl:value-of select="last_state_change_name"/></td>
			<td class="ColPopup"><xsl:value-of select="last_state_change"/></td>
		</tr>
		<tr class='list_one'>
			<td class="ColPopup"><xsl:value-of select="duration_name"/></td>
			<td class="ColPopup"><xsl:value-of select="duration"/> s</td>
		</tr>
		<tr class='list_one'>
			<td class="ColPopup"><xsl:value-of select="state_type_name"/></td>
			<td class="ColPopup"><xsl:value-of select="state_type"/></td>
		</tr>
		<tr class='list_two'>
			<td class="ColPopup"><xsl:value-of select="percent_state_change_name"/></td>
			<td class="ColPopup"><xsl:value-of select="percent_state_change"/> %</td>
		</tr>
		<tr class='list_one'>
			<td class="ColPopup"><xsl:value-of select="performance_data_name"/></td>
			<td class="ColPopup"><xsl:value-of select="performance_data"/></td>
		</tr>
		<tr class='list_separator'>
			<td class="separator" colspan="2"><xsl:value-of select="tr1"/></td>
		</tr>
		<tr class='list_two'>
			<td class="ColPopup"><xsl:value-of select="last_check_name"/></td>
			<td class="ColPopup"><xsl:value-of select="last_check"/></td>
		</tr>
		<tr class='list_one'>
			<td class="ColPopup"><xsl:value-of select="next_check_name"/></td>
			<td class="ColPopup"><xsl:value-of select="next_check"/></td>
		</tr>
		<tr class='list_two'>
			<td class="ColPopup"><xsl:value-of select="check_latency_name"/></td>
			<td class="ColPopup"><xsl:value-of select="check_latency"/> s</td>
		</tr>
		<tr class='list_one'>
			<td class="ColPopup"><xsl:value-of select="check_execution_time_name"/></td>
			<td class="ColPopup"><xsl:value-of select="check_execution_time"/> s</td>
		</tr>
		<tr class='list_one'>
			<td class="ColPopup"><xsl:value-of select="is_downtime_name"/></td>
			<td class="ColPopup"><xsl:value-of select="is_downtime"/></td>
		</tr>
		<tr class='list_two'>
			<td class="ColPopup"><xsl:value-of select="last_update_name"/></td>
			<td class="ColPopup"><xsl:value-of select="last_update"/></td>
		</tr>
		<tr class='list_separator'>
			<td class="separator" colspan="2"><xsl:value-of select="tr2"/></td>
		</tr>
		<tr class='list_two'>
			<td class="ColPopup"><xsl:value-of select="last_notification_name"/></td>
			<td class="ColPopup"><xsl:value-of select="last_notification"/></td>
		</tr>
		<tr class='list_one'>
			<td class="ColPopup"><xsl:value-of select="next_notification_name"/></td>
			<td class="ColPopup"><xsl:value-of select="next_notification"/></td>
		</tr>
		<tr class='list_two'>
			<td class="ColPopup"><xsl:value-of select="current_notification_number_name"/></td>
			<td class="ColPopup"><xsl:value-of select="current_notification_number"/></td>
		</tr>
		<tr class='list_separator'>
			<td class="separator" colspan="2"><xsl:value-of select="tr3"/></td>
		</tr>
		<tr class='list_one'>
			<td class="ColPopup"><xsl:value-of select="last_time_ok/@name"/></td>
			<td class="ColPopup"><xsl:value-of select="last_time_ok"/></td>
		</tr>
		<tr class='list_two'>
			<td class="ColPopup"><xsl:value-of select="last_time_critical/@name"/></td>
			<td class="ColPopup"><xsl:value-of select="last_time_critical"/></td>
		</tr>
		<tr class='list_one'>
			<td class="ColPopup"><xsl:value-of select="last_time_warning/@name"/></td>
			<td class="ColPopup"><xsl:value-of select="last_time_warning"/></td>
		</tr>
		<tr class='list_one'>
			<td class="ColPopup"><xsl:value-of select="last_time_unknown/@name"/></td>
			<td class="ColPopup"><xsl:value-of select="last_time_unknown"/></td>
		</tr>
	</table>
</xsl:template>
</xsl:stylesheet>