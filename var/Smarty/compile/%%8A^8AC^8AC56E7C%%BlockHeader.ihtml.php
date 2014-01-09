<?php /* Smarty version 2.6.18, created on 2014-01-06 15:58:12
         compiled from BlockHeader.ihtml */ ?>
<div><img src="./img/icones/7x7/sort_asc.gif" onclick="toggleHeader(); xhr = new XMLHttpRequest(); xhr.open('GET','<?php echo $this->_tpl_vars['user_update_pref_header']; ?>
', true);xhr.send(null);" style="position:absolute;top:1px;right:7px;" alt="hide_or_show_menu" /></div>
<div id="header">
	<img src='<?php echo $this->_tpl_vars['urlLogo']; ?>
' title='Centreon Logo' id="logo" />	
	<!-- informations bank for ajax -->
	<form id="AjaxBankBasic" action="#" method="post">
		<div>
			<input type="hidden" name="color_OK" value="<?php echo $this->_tpl_vars['color']['OK']; ?>
"/>
			<input type="hidden" name="color_CRITICAL" value="<?php echo $this->_tpl_vars['color']['CRITICAL']; ?>
" />
			<input type="hidden" name="color_WARNING" value="<?php echo $this->_tpl_vars['color']['WARNING']; ?>
" />
			<input type="hidden" name="color_PENDING" value="<?php echo $this->_tpl_vars['color']['PENDING']; ?>
" />
			<input type="hidden" name="color_UNKNOWN" value="<?php echo $this->_tpl_vars['color']['UNKNOWN']; ?>
" />	        
			<input type="hidden" name="color_UP" value="<?php echo $this->_tpl_vars['color']['UP']; ?>
"/>
			<input type="hidden" name="color_DOWN" value="<?php echo $this->_tpl_vars['color']['DOWN']; ?>
" />
			<input type="hidden" name="color_UNREACHABLE" value="<?php echo $this->_tpl_vars['color']['UNREACHABLE']; ?>
" />
			<input type="hidden" name="icone_is_flapping" value="./img/icones/16x16/flapping.gif" />
			<input type="hidden" name="icone_problem_has_been_acknowledged" value="./img/icones/16x16/worker.gif" />
			<input type="hidden" name="icone_accept_passive_check0" value="./img/icones/14x14/gears_pause.gif" />
			<input type="hidden" name="icone_accept_passive_check1" value="./img/icones/14x14/gears_stop.gif" />
			<input type="hidden" name="icone_notifications_enabled" value="./img/icones/14x14/noloudspeaker.gif" />
			<input type="hidden" name="icone_undo" value="./img/icones/14x14/undo.gif" />
			<input type="hidden" name="icone_graph" value="./img/icones/16x16/column-chart.gif"/>
			<input type="hidden" name="icone_host_has_been_acknowledged" value="./img/icones/16x16/worker.gif"/>
			<input type="hidden" name="icone_notifications_disabled" value="./img/icones/14x14/noloudspeaker.gif" />
			<input type="hidden" name="icon_downtime" value="./img/icones/14x14/breakpoint.gif" />
			<input type="hidden" name="icon_comment" value="./img/icones/14x14/about.gif" />
			<input type="hidden" name="version" value="<?php echo $this->_tpl_vars['version']; ?>
"/>
			<input type="hidden" name="date_time_format_status" value="<?php echo $this->_tpl_vars['date_time_format_status']; ?>
"/>			
		</div>
	</form>	
	<!-- stat -->
	<div id="resume_light">
		<?php if ($this->_tpl_vars['displayTopCounter'] == 1 || $this->_tpl_vars['displayPollerStats'] == 1): ?>
		<table cellpadding="0" cellspacing="0" border="0">
		<tr>
			<?php if ($this->_tpl_vars['displayPollerStats'] == 1): ?>
			<td>
				<table class='Resume_light_table'>
					<tr class='Resume_light_header' style="white-space:nowrap;">
						<td colspan="3"><?php echo $this->_tpl_vars['ndoState']; ?>
</td>
					</tr>
					<tr>
						<td id='latency'><div style='text-align:center'><img src='./img/icones/16x16/clock.gif' width=14 id="img_latency" /></div></td>
						<td id="pollingState"><div style='text-align:center;'><img src='./img/icones/16x16/gear.gif' width=14 id="img_pollingState"/></div></td>
						<td id="activity"><div style='text-align:center'><img src='./img/icones/16x16/data_into.gif' width=14 id="img_activity" /></div></td>
					</tr>
				</table>
			</td>
			<td>&nbsp;</td>
			<?php endif; ?>
			<?php if ($this->_tpl_vars['displayTopCounter'] == 1): ?>
			<td>
				<table class='Resume_light_table'>
					<tr class='Resume_light_header' style="white-space:nowrap;">
						<td><?php echo $this->_tpl_vars['Hosts']; ?>
</td>
						<td><?php echo $this->_tpl_vars['Up']; ?>
</td>
						<td><?php echo $this->_tpl_vars['Down']; ?>
</td>
						<td><?php echo $this->_tpl_vars['Unreachable']; ?>
</td>
						<td><?php echo $this->_tpl_vars['Pending']; ?>
</td>
					</tr>
					<tr>
						<td><div id="hosts" style='background:white;text-align:center'>?</div></td>
						<td	style='background:<?php echo $this->_tpl_vars['color']['UP']; ?>
'><div id="host_up" style='background:white;text-align:center'>?</div></td>
						<td	style='background:<?php echo $this->_tpl_vars['color']['DOWN']; ?>
'><div id="host_down" style='background:white;text-align:center'>?</div></td>
						<td	style='background:<?php echo $this->_tpl_vars['color']['UNREACHABLE']; ?>
'><div id="host_unreachable" style='background:white;text-align:center'>?</div></td>
						<td	style='background:<?php echo $this->_tpl_vars['color']['PENDING']; ?>
'><div id="host_pending" style='background:white;text-align:center'>?</div></td>
					</tr>
				</table>
			</td>
			<td>&nbsp;</td>
			<td>
				<table class='Resume_light_table'>
					<tr class='Resume_light_header' style="white-space:nowrap;">
						<td><?php echo $this->_tpl_vars['Services']; ?>
</td>
						<td><?php echo $this->_tpl_vars['Ok']; ?>
</td>
						<td><?php echo $this->_tpl_vars['Warning']; ?>
</td>
						<td><?php echo $this->_tpl_vars['Critical']; ?>
</td>
						<td><?php echo $this->_tpl_vars['Unknown']; ?>
</td>		
						<td><?php echo $this->_tpl_vars['Pending']; ?>
</td>
					</tr>
					<tr>
						<td><div id="service_total" style='background:white;text-align:center'>?</div></td>
						<td style='background:<?php echo $this->_tpl_vars['color']['OK']; ?>
'><div id="service_ok" style='background:white;text-align:center'>?</div></td>
						<td	style='background:<?php echo $this->_tpl_vars['color']['WARNING']; ?>
'><div id="service_warning" style='background:white;text-align:center'>?</div></td>
						<td	style='background:<?php echo $this->_tpl_vars['color']['CRITICAL']; ?>
'><div id="service_critical" style='background:white;text-align:center'>?</div></td>
						<td	style='background:<?php echo $this->_tpl_vars['color']['UNKNOWN']; ?>
'><div id="service_unknown" style='background:white;text-align:center'>?</div></td>
						<td	style='background:<?php echo $this->_tpl_vars['color']['PENDING']; ?>
'><div id="service_pending" style='background:white;text-align:center'>?</div></td>
					</tr>
				</table>
			</td>
			<?php endif; ?>
		</tr>
		</table>
		<?php endif; ?>
	</div>	
	<span id="linkBar"></span>
	<span id="logli">
		<img src='./img/icones/16x16/help.gif' alt="<?php echo $this->_tpl_vars['Documentation']; ?>
" />&nbsp;<a href='./main.php?p=<?php echo $this->_tpl_vars['p']; ?>
&amp;doc=1&amp;page=toc.html' title='<?php echo $this->_tpl_vars['Documentation']; ?>
'><?php echo $this->_tpl_vars['Documentation']; ?>
</a>&nbsp;-&nbsp;<?php echo $this->_tpl_vars['loggedlabel']; ?>
&nbsp;<?php if ($this->_tpl_vars['topology'][50104]): ?><a href="./main.php?p=50104&o=c"><?php endif; ?><?php echo $this->_tpl_vars['user_login']; ?>
<?php if ($this->_tpl_vars['topology'][50104]): ?></a><?php endif; ?>&nbsp;<?php if ($this->_tpl_vars['autoLoginEnable']): ?><a onClick='return false;' href='<?php echo $this->_tpl_vars['autoLoginUrl']; ?>
' title='Centreon - IT and Network Monitoring'><img src='./img/icones/16x16/lock_preferences.gif' title='<?php echo $this->_tpl_vars['CentreonAutologin']; ?>
'></a>&nbsp;<?php endif; ?><a href="<?php echo $this->_tpl_vars['LogOutUrl']; ?>
"><img src="./img/icones/16x16/logout.gif" alt="<?php echo $this->_tpl_vars['Logout']; ?>
"/></a>&nbsp;<a href="<?php echo $this->_tpl_vars['LogOutUrl']; ?>
"><?php echo $this->_tpl_vars['Logout']; ?>
</a>&nbsp;
	</span>	
	<div id="date"><?php echo $this->_tpl_vars['Date']; ?>
</div>
	<div id="centreonMsg"></div>
</div>
<?php echo '
<script type=\'text/javascript\'>
/**
 * Toggle Header
 */
function toggleHeader()
{
	new Effect.toggle(\'header\', \'appear\', { afterFinish: function() {
															setQuickSearchPosition();
														  }
										  });
}
</script>
'; ?>