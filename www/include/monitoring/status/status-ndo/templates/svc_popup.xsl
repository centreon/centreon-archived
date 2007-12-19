<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:template match="//reponse">



	<table>
	<tr class="ListHeaderPopup">

		<td class="ColPopup">
			<xsl:value-of select="hostname"/>
		</td>
		<td class="ColPopup">
			<xsl:value-of select="service_description"/>
		</td>
	</tr>
	
		<tr class='list_separator'>
			<td class="separator" colspan="2">Status</td>
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
			<td class="ColPopup"><xsl:value-of select="duration"/></td>
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
			<td class="separator" colspan="2">Ckeck information</td>
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
			<td class="ColPopup"><xsl:value-of select="check_latency"/></td>
		</tr>
		<tr class='list_one'>
			<td class="ColPopup"><xsl:value-of select="check_execution_time_name"/></td>
			<td class="ColPopup"><xsl:value-of select="check_execution_time"/></td>
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
			<td class="separator" colspan="2">Notification</td>
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
			<td class="separator" colspan="2">Last time status</td>
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