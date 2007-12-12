<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:template match="//reponse">



	<table>
	<tr class="ListHeaderPopup">
		<td class="ColPopup">
			<xsl:value-of select="hostname"/>
		</td>
		<td class="ColPopup">
			<xsl:value-of select="address"/>
		</td>
	</tr>
		<tr class="list_one">
			<td class="ColPopup"><xsl:value-of select="current_state_name"/></td>
			<td class="ColPopup"><xsl:value-of select="current_state"/></td>
		</tr>
		<tr class='list_two'>
			<td class="ColPopup"><xsl:value-of select="plugin_output_name"/></td>
			<td class="ColPopup"><xsl:value-of select="plugin_output"/></td>
		</tr>


		<tr class='list_one'>
			<td class="ColPopup"><xsl:value-of select="performance_data_name"/></td>
			<td class="ColPopup"><xsl:value-of select="performance_data"/></td>
		</tr>
		<tr class='list_two'>
			<td class="ColPopup"><xsl:value-of select="current_attempt_name"/></td>
			<td class="ColPopup"><xsl:value-of select="current_attempt"/></td>
		</tr>
		<tr class='list_one'>
			<td class="ColPopup"><xsl:value-of select="state_type_name"/></td>
			<td class="ColPopup"><xsl:value-of select="state_type"/></td>
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
		<tr class='list_two'>
			<td class="ColPopup"><xsl:value-of select="last_state_change_name"/></td>
			<td class="ColPopup"><xsl:value-of select="last_state_change"/></td>
		</tr>
		<tr class='list_one'>
			<td class="ColPopup"><xsl:value-of select="duration_name"/></td>
			<td class="ColPopup"><xsl:value-of select="duration"/></td>
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
		<tr class='list_one'>
			<td class="ColPopup"><xsl:value-of select="is_flapping_name"/></td>
			<td class="ColPopup"><xsl:value-of select="is_flapping"/></td>
		</tr>
		<tr class='list_two'>
			<td class="ColPopup"><xsl:value-of select="percent_state_change_name"/></td>
			<td class="ColPopup"><xsl:value-of select="percent_state_change"/></td>
		</tr>
		<tr class='list_one'>
			<td class="ColPopup"><xsl:value-of select="is_downtime_name"/></td>
			<td class="ColPopup"><xsl:value-of select="is_downtime"/></td>
		</tr>
		<tr class='list_two'>
			<td class="ColPopup"><xsl:value-of select="last_update_name"/></td>
			<td class="ColPopup"><xsl:value-of select="last_update"/></td>
		</tr>
		<tr class='list_one'>
			<td class="ColPopup">flap</td>
			<td class="ColPopup"><xsl:value-of select="flap_detection_enabled"/></td>
		</tr>
		<tr class='list_two'>
			<td class="ColPopup">passive_checks_enabled</td>
			<td class="ColPopup"><xsl:value-of select="passive_checks_enabled"/></td>
		</tr>
		<tr class='list_one'>
			<td class="ColPopup">active_checks_enabled</td>
			<td class="ColPopup"><xsl:value-of select="active_checks_enabled"/></td>
		</tr>
		<tr class='list_two'>
			<td class="ColPopup">obsess_over_host</td>
			<td class="ColPopup"><xsl:value-of select="obsess_over_host"/></td>
		</tr>
		<tr class='list_one'>
			<td class="ColPopup">notifications_enabled</td>
			<td class="ColPopup"><xsl:value-of select="notifications_enabled"/></td>
		</tr>
		<tr class='list_two'>
			<td class="ColPopup">event_handler_enabled</td>
			<td class="ColPopup"><xsl:value-of select="event_handler_enabled"/></td>
		</tr>


</table>


</xsl:template>
</xsl:stylesheet>