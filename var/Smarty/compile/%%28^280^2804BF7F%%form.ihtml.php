<?php /* Smarty version 2.6.18, created on 2014-01-06 15:54:41
         compiled from form.ihtml */ ?>
<?php echo $this->_tpl_vars['form']['javascript']; ?>

<?php echo $this->_tpl_vars['colorJS']; ?>

<form <?php echo $this->_tpl_vars['form']['attributes']; ?>
>
<table class="ListTable">
 	<tr class="ListHeader"><td class="FormHeader" colspan="2">&nbsp;<img src='./img/icones/16x16/tool.gif'>&nbsp;<?php echo $this->_tpl_vars['form']['header']['title']; ?>
</td></tr>
 	
 	<tr class="list_lvl_1"><td class="ListColLvl1_name" colspan="2">&nbsp;<img src='./img/icones/16x16/oreon.gif'>&nbsp;&nbsp;<?php echo $this->_tpl_vars['form']['header']['oreon']; ?>
</td></tr>
	<tr class="list_one"><td class="FormRowField"><img class="helpTooltip" name="tip_directory"><?php echo $this->_tpl_vars['form']['oreon_path']['label']; ?>
</td><td class="FormRowValue"><?php echo $this->_tpl_vars['form']['oreon_path']['html']; ?>
</td></tr>
	<tr class="list_two"><td class="FormRowField"><img class="helpTooltip" name="tip_centreon_web_directory"><?php echo $this->_tpl_vars['form']['oreon_web_path']['label']; ?>
</td><td class="FormRowValue"><?php echo $this->_tpl_vars['form']['oreon_web_path']['html']; ?>
</td></tr>
 	
 	<tr class="list_lvl_1"><td class="ListColLvl1_name" colspan="2">&nbsp;<img src='./img/icones/16x16/window_split_hor.gif'>&nbsp;&nbsp;<?php echo $this->_tpl_vars['genOpt_max_page_size']; ?>
</td></tr>
	<tr class="list_two"><td class="FormRowField"><img class="helpTooltip" name="tip_limit_per_page"><?php echo $this->_tpl_vars['form']['maxViewConfiguration']['label']; ?>
</td><td class="FormRowValue"><?php echo $this->_tpl_vars['form']['maxViewConfiguration']['html']; ?>
</td></tr>
	<tr class="list_one"><td class="FormRowField"><img class="helpTooltip" name="tip_limit_per_page_for_monitoring"><?php echo $this->_tpl_vars['form']['maxViewMonitoring']['label']; ?>
</td><td class="FormRowValue"><?php echo $this->_tpl_vars['form']['maxViewMonitoring']['html']; ?>
</td></tr>
	
	<tr class="list_lvl_1"><td class="ListColLvl1_name" colspan="2">&nbsp;<img src='./img/icones/16x16/stopwatch.gif'>&nbsp;&nbsp;<?php echo $this->_tpl_vars['genOpt_expiration_properties']; ?>
</td></tr>
	<tr class="list_one"><td class="FormRowField"><img class="helpTooltip" name="tip_sessions_expiration_time"><?php echo $this->_tpl_vars['form']['session_expire']['label']; ?>
</td><td class="FormRowValue"><?php echo $this->_tpl_vars['form']['session_expire']['html']; ?>
&nbsp;<?php echo $this->_tpl_vars['time_min']; ?>
</td></tr>
	
	<tr class="list_lvl_1"><td class="ListColLvl1_name" colspan="2">&nbsp;<img src='./img/icones/16x16/refresh.gif'>&nbsp;&nbsp;<?php echo $this->_tpl_vars['genOpt_refresh_properties']; ?>
</td></tr>
	<tr class="list_two"><td class="FormRowField"><img class="helpTooltip" name="tip_refresh_interval"><?php echo $this->_tpl_vars['form']['oreon_refresh']['label']; ?>
</td><td class="FormRowValue"><?php echo $this->_tpl_vars['form']['oreon_refresh']['html']; ?>
&nbsp;<?php echo $this->_tpl_vars['time_sec']; ?>
</td></tr>
	<tr class="list_two"><td class="FormRowField"><img class="helpTooltip" name="tip_refresh_interval_for_statistics"><?php echo $this->_tpl_vars['form']['AjaxTimeReloadStatistic']['label']; ?>
</td><td class="FormRowValue"><?php echo $this->_tpl_vars['form']['AjaxTimeReloadStatistic']['html']; ?>
&nbsp;<?php echo $this->_tpl_vars['time_sec']; ?>
</td></tr>
	<tr class="list_one"><td class="FormRowField"><img class="helpTooltip" name="tip_refresh_interval_for_monitoring"><?php echo $this->_tpl_vars['form']['AjaxTimeReloadMonitoring']['label']; ?>
</td><td class="FormRowValue"><?php echo $this->_tpl_vars['form']['AjaxTimeReloadMonitoring']['html']; ?>
&nbsp;<?php echo $this->_tpl_vars['time_sec']; ?>
</td></tr>
 	<tr class="list_two"><td class="FormRowField"><img class="helpTooltip" name="tip_first_refresh_delay_for_statistics"><?php echo $this->_tpl_vars['form']['AjaxFirstTimeReloadStatistic']['label']; ?>
</td><td class="FormRowValue"><?php echo $this->_tpl_vars['form']['AjaxFirstTimeReloadStatistic']['html']; ?>
&nbsp;<?php echo $this->_tpl_vars['time_sec']; ?>
</td></tr>
	<tr class="list_one"><td class="FormRowField"><img class="helpTooltip" name="tip_first_refresh_delay_for_monitoring"><?php echo $this->_tpl_vars['form']['AjaxFirstTimeReloadMonitoring']['label']; ?>
</td><td class="FormRowValue"><?php echo $this->_tpl_vars['form']['AjaxFirstTimeReloadMonitoring']['html']; ?>
&nbsp;<?php echo $this->_tpl_vars['time_sec']; ?>
</td></tr> 	
 	
 	<tr class="list_lvl_1"><td class="ListColLvl1_name" colspan="2">&nbsp;<img src='./img/icones/16x16/text_rich_colored.gif'>&nbsp;&nbsp;<?php echo $this->_tpl_vars['genOpt_display_options']; ?>
</td></tr>
 	<tr class="list_two"><td class="FormRowField"><img class="helpTooltip" name="tip_display_template"><?php echo $this->_tpl_vars['form']['template']['label']; ?>
</td><td class="FormRowValue"><?php echo $this->_tpl_vars['form']['template']['html']; ?>
</td></tr>
 	
	<tr class="list_lvl_1"><td class="ListColLvl1_name" colspan="2">&nbsp;<img src='./img/icones/16x16/row_delete.gif'>&nbsp;&nbsp;<?php echo $this->_tpl_vars['genOpt_problem_display']; ?>
</td></tr>
 	<tr class="list_one"><td class="FormRowField"><img class="helpTooltip" name="tip_sort_problems_by"><?php echo $this->_tpl_vars['form']['problem_sort_type']['label']; ?>
</td><td class="FormRowValue"><?php echo $this->_tpl_vars['form']['problem_sort_type']['html']; ?>
</td></tr>
	<tr class="list_two"><td class="FormRowField"><img class="helpTooltip" name="tip_order_sort_problems"><?php echo $this->_tpl_vars['form']['problem_sort_order']['label']; ?>
</td><td class="FormRowValue"><?php echo $this->_tpl_vars['form']['problem_sort_order']['html']; ?>
</td></tr>
	
	<tr class="list_lvl_1"><td class="ListColLvl1_name" colspan="2">&nbsp;<img src='./img/icones/16x16/lock.gif'>&nbsp;&nbsp;<?php echo $this->_tpl_vars['genOpt_auth']; ?>
</td></tr>
	<tr class="list_one"><td class="FormRowField"><img class="helpTooltip" name="tip_enable_autologin"><?php echo $this->_tpl_vars['form']['enable_autologin']['label']; ?>
</td><td class="FormRowValue"><?php echo $this->_tpl_vars['form']['enable_autologin']['html']; ?>
</td></tr>
	<tr class="list_two"><td class="FormRowField"><img class="helpTooltip" name="tip_display_autologin_shortcut"><?php echo $this->_tpl_vars['form']['display_autologin_shortcut']['label']; ?>
</td><td class="FormRowValue"><?php echo $this->_tpl_vars['form']['display_autologin_shortcut']['html']; ?>
</td></tr>
    <tr class="list_one"><td class="FormRowField"><img class="helpTooltip" name="sso_enable"><?php echo $this->_tpl_vars['form']['sso_enable']['label']; ?>
</td><td class="FormRowValue"><?php echo $this->_tpl_vars['form']['sso_enable']['html']; ?>
</td></tr>
    <tr class="list_two"><td class="FormRowField"><img class="helpTooltip" name="sso_mode"><?php echo $this->_tpl_vars['form']['sso_mode']['label']; ?>
</td><td class="FormRowValue"><?php echo $this->_tpl_vars['form']['sso_mode']['html']; ?>
</td></tr>
    <tr class="list_one"><td class="FormRowField"><img class="helpTooltip" name="sso_trusted_clients"><?php echo $this->_tpl_vars['form']['sso_trusted_clients']['label']; ?>
</td><td class="FormRowValue"><?php echo $this->_tpl_vars['form']['sso_trusted_clients']['html']; ?>
</td></tr>
    <tr class="list_two"><td class="FormRowField"><img class="helpTooltip" name="sso_header_username"><?php echo $this->_tpl_vars['form']['sso_header_username']['label']; ?>
</td><td class="FormRowValue"><?php echo $this->_tpl_vars['form']['sso_header_username']['html']; ?>
</td></tr>

	<tr class="list_lvl_1"><td class="ListColLvl1_name" colspan="2">&nbsp;<img src='./img/icones/16x16/stopwatch.gif'>&nbsp;&nbsp;<?php echo $this->_tpl_vars['genOpt_time_zone']; ?>
</td></tr>
 	<tr class="list_one"><td class="FormRowField"><img class="helpTooltip" name="tip_enable_timezone_management"><?php echo $this->_tpl_vars['form']['enable_gmt']['label']; ?>
</td><td class="FormRowValue"><?php echo $this->_tpl_vars['form']['enable_gmt']['html']; ?>
</td></tr>
 	<tr class="list_two"><td class="FormRowField"><img class="helpTooltip" name="tip_default_timezone"><?php echo $this->_tpl_vars['form']['gmt']['label']; ?>
</td><td class="FormRowValue"><?php echo $this->_tpl_vars['form']['gmt']['html']; ?>
</td></tr>
 	
  	<tr class="list_lvl_1"><td class="ListColLvl1_name" colspan="2">&nbsp;<img src='./img/icones/16x16/stopwatch.gif'>&nbsp;&nbsp;<?php echo $this->_tpl_vars['configBehavior']; ?>
</td></tr>
 	<tr class="list_one"><td class="FormRowField"><img class="helpTooltip" name="strict_hostParent_poller_management"><?php echo $this->_tpl_vars['form']['strict_hostParent_poller_management']['label']; ?>
</td><td class="FormRowValue"><?php echo $this->_tpl_vars['form']['strict_hostParent_poller_management']['html']; ?>
</td></tr>

  	<tr class="list_lvl_1"><td class="ListColLvl1_name" colspan="2">&nbsp;<img src='./img/icones/16x16/stopwatch.gif'>&nbsp;&nbsp;<?php echo $this->_tpl_vars['support']; ?>
</td></tr>
 	<tr class="list_one"><td class="FormRowField"><img class="helpTooltip" name="tip_centreon_support_email"><?php echo $this->_tpl_vars['form']['centreon_support_email']['label']; ?>
</td><td class="FormRowValue"><?php echo $this->_tpl_vars['form']['centreon_support_email']['html']; ?>
</td></tr>

 	<tr class="list_lvl_2"><td class="ListColLvl2_name" colspan="2"><?php echo $this->_tpl_vars['form']['required']['_note']; ?>
</td></tr>
 </table>
<?php if (! $this->_tpl_vars['valid']): ?>
	<div id="validForm" class="oreonbutton">
		<p><?php echo $this->_tpl_vars['form']['submitC']['html']; ?>
&nbsp;&nbsp;&nbsp;<?php echo $this->_tpl_vars['form']['reset']['html']; ?>
</p>
	</div>
<?php else: ?>
	<div id="validForm" class="oreonbutton">
		<p><?php echo $this->_tpl_vars['form']['change']['html']; ?>
</p>
	</div>
<?php endif; ?>
<?php echo $this->_tpl_vars['form']['hidden']; ?>

</form>
<?php echo $this->_tpl_vars['helptext']; ?>
