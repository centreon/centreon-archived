<?php /* Smarty version 2.6.18, created on 2014-01-06 15:58:12
         compiled from listHost.ihtml */ ?>
<script type="text/javascript" src="./include/common/javascript/tool.js"></script>
<form name='form' method='POST'>
<table class="ajaxOption">
	<tr>
		<td><?php echo $this->_tpl_vars['Hosts']; ?>
 : <input type='text' name='searchH' value="<?php echo $this->_tpl_vars['search']; ?>
" /></td>
        <td>&nbsp;&nbsp;<?php echo $this->_tpl_vars['Poller']; ?>
 : <select name='poller'><?php echo $this->_tpl_vars['poller']; ?>
</select></td>
		<td>&nbsp;&nbsp;<?php echo $this->_tpl_vars['Hostgroup']; ?>
 : <select name='hostgroup'><?php echo $this->_tpl_vars['hostgroup']; ?>
</select></td>
		<td>&nbsp;&nbsp;<?php echo $this->_tpl_vars['Template']; ?>
 : <select name='template'><?php echo $this->_tpl_vars['template']; ?>
</select></td>
		<td>&nbsp;&nbsp;<?php echo $this->_tpl_vars['headerMenu_status']; ?>
 : <select name='status' ><?php echo $this->_tpl_vars['StatusFilter']; ?>
</select></td>
        <td><input type='submit' name='SearchB' value='<?php echo $this->_tpl_vars['Search']; ?>
' /></td>
	</tr>
</table>
<br>
<table class="ToolbarTable">
	<tr class="ToolbarTR">
		<?php if ($this->_tpl_vars['mode_access'] == 'w'): ?>
		<td class="Toolbar_TDSelectAction_Top">
			<?php echo $this->_tpl_vars['msg']['options']; ?>
 <?php echo $this->_tpl_vars['form']['o1']['html']; ?>
&nbsp;&nbsp;&nbsp;<a href="<?php echo $this->_tpl_vars['msg']['addL']; ?>
"><?php echo $this->_tpl_vars['msg']['addT']; ?>
</a>
		</td>
		<?php else: ?> 
		<td>&nbsp;</td>
		<?php endif; ?>
		<input name="p" value="<?php echo $this->_tpl_vars['p']; ?>
" type="hidden">
		<?php 
		   include('./include/common/pagination.php');
		 ?>
	</tr>
</table>
<table class="ListTable">
	<tr class="ListHeader">
		<td class="ListColHeaderPicker"><input type="checkbox" name="checkall" onclick="checkUncheckAll(this);"/></td>
		<td class="ListColHeaderLeft"><?php echo $this->_tpl_vars['headerMenu_name']; ?>
</td>
		<td class="ListColHeaderLeft" style="width:20px;">&nbsp;</td>
		<td class="ListColHeaderLeft"><?php echo $this->_tpl_vars['headerMenu_desc']; ?>
</td>
		<td class="ListColHeaderCenter"><?php echo $this->_tpl_vars['headerMenu_address']; ?>
</td>
		<td class="ListColHeaderCenter"><?php echo $this->_tpl_vars['headerMenu_poller']; ?>
</td>
		<td class="ListColHeaderCenter"><?php echo $this->_tpl_vars['headerMenu_parent']; ?>
</td>
		<td class="ListColHeaderCenter"><?php echo $this->_tpl_vars['headerMenu_status']; ?>
</td>
		<td class="ListColHeaderRight"><?php echo $this->_tpl_vars['headerMenu_options']; ?>
</td>
	</tr>
	<?php $this->assign('pattern_mode', 0); ?>
	<?php unset($this->_sections['elem']);
$this->_sections['elem']['name'] = 'elem';
$this->_sections['elem']['loop'] = is_array($_loop=$this->_tpl_vars['elemArr']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['elem']['show'] = true;
$this->_sections['elem']['max'] = $this->_sections['elem']['loop'];
$this->_sections['elem']['step'] = 1;
$this->_sections['elem']['start'] = $this->_sections['elem']['step'] > 0 ? 0 : $this->_sections['elem']['loop']-1;
if ($this->_sections['elem']['show']) {
    $this->_sections['elem']['total'] = $this->_sections['elem']['loop'];
    if ($this->_sections['elem']['total'] == 0)
        $this->_sections['elem']['show'] = false;
} else
    $this->_sections['elem']['total'] = 0;
if ($this->_sections['elem']['show']):

            for ($this->_sections['elem']['index'] = $this->_sections['elem']['start'], $this->_sections['elem']['iteration'] = 1;
                 $this->_sections['elem']['iteration'] <= $this->_sections['elem']['total'];
                 $this->_sections['elem']['index'] += $this->_sections['elem']['step'], $this->_sections['elem']['iteration']++):
$this->_sections['elem']['rownum'] = $this->_sections['elem']['iteration'];
$this->_sections['elem']['index_prev'] = $this->_sections['elem']['index'] - $this->_sections['elem']['step'];
$this->_sections['elem']['index_next'] = $this->_sections['elem']['index'] + $this->_sections['elem']['step'];
$this->_sections['elem']['first']      = ($this->_sections['elem']['iteration'] == 1);
$this->_sections['elem']['last']       = ($this->_sections['elem']['iteration'] == $this->_sections['elem']['total']);
?>
	<?php if ($this->_tpl_vars['elemArr'][$this->_sections['elem']['index']]['pattern'] != $this->_tpl_vars['pattern_value']): ?>
		<?php $this->assign('pattern_mode', 0); ?>
	<?php endif; ?>
	<?php if ($this->_tpl_vars['elemArr'][$this->_sections['elem']['index']]['pattern'] && $this->_tpl_vars['pattern_mode'] == 0): ?>
		<tr class="list_lvl_1"><td class="ListColLeft" colspan="9"><b><?php echo $this->_tpl_vars['elemArr'][$this->_sections['elem']['index']]['pattern']; ?>
</b></td></tr>
		<?php $this->assign('pattern_mode', 1); ?>
		<?php $this->assign('pattern_value', $this->_tpl_vars['elemArr'][$this->_sections['elem']['index']]['pattern']); ?>
	<?php endif; ?>
	<tr class="<?php echo $this->_tpl_vars['elemArr'][$this->_sections['elem']['index']]['MenuClass']; ?>
">
		<td class="ListColPicker"><?php echo $this->_tpl_vars['elemArr'][$this->_sections['elem']['index']]['RowMenu_select']; ?>
</td>
		<td class="ListColLeft">
			<?php if ($this->_tpl_vars['elemArr'][$this->_sections['elem']['index']]['pattern']): ?>&nbsp;&nbsp;&nbsp;&nbsp;<?php endif; ?>
			<img src="<?php echo $this->_tpl_vars['elemArr'][$this->_sections['elem']['index']]['RowMenu_icone']; ?>
" style='width:16px;height:16px;' />&nbsp;<a href="<?php echo $this->_tpl_vars['elemArr'][$this->_sections['elem']['index']]['RowMenu_link']; ?>
"><?php echo $this->_tpl_vars['elemArr'][$this->_sections['elem']['index']]['RowMenu_name']; ?>
</a>
		</td>
		<td class="ListColCenter"><a href='./main.php?p=602&search_h=<?php echo $this->_tpl_vars['elemArr'][$this->_sections['elem']['index']]['RowMenu_name']; ?>
'><img src="./img/icones/16x16/gear_view.gif" title='<?php echo $this->_tpl_vars['HelpServices']; ?>
'></a></td>
		<td class="ListColLeft"><a href="<?php echo $this->_tpl_vars['elemArr'][$this->_sections['elem']['index']]['RowMenu_link']; ?>
"><?php echo $this->_tpl_vars['elemArr'][$this->_sections['elem']['index']]['RowMenu_desc']; ?>
</a></td>
		<td class="ListColCenter"><?php echo $this->_tpl_vars['elemArr'][$this->_sections['elem']['index']]['RowMenu_address']; ?>
</td>
		<td class="ListColCenter"><?php echo $this->_tpl_vars['elemArr'][$this->_sections['elem']['index']]['RowMenu_poller']; ?>
</td>
		<td class="ListColRight"><?php echo $this->_tpl_vars['elemArr'][$this->_sections['elem']['index']]['RowMenu_parent']; ?>
</td>
		<td class="ListColCenter"><?php echo $this->_tpl_vars['elemArr'][$this->_sections['elem']['index']]['RowMenu_status']; ?>
</td>
		<td class="ListColRight"><?php if ($this->_tpl_vars['mode_access'] == 'w'): ?><?php echo $this->_tpl_vars['elemArr'][$this->_sections['elem']['index']]['RowMenu_options']; ?>
<?php else: ?>&nbsp;<?php endif; ?></td>
	</tr>
	<?php endfor; endif; ?>
</table>
<table class="ToolbarTable">
	<tr>
		<?php if ($this->_tpl_vars['mode_access'] == 'w'): ?>
		<td class="Toolbar_TDSelectAction_Bottom">
			<?php echo $this->_tpl_vars['msg']['options']; ?>
 <?php echo $this->_tpl_vars['form']['o2']['html']; ?>
&nbsp;&nbsp;&nbsp;<a href="<?php echo $this->_tpl_vars['msg']['addL']; ?>
"><?php echo $this->_tpl_vars['msg']['addT']; ?>
</a>
		</td>
		<?php else: ?> 
		<td>&nbsp;</td>
		<?php endif; ?>
		<input name="p" value="<?php echo $this->_tpl_vars['p']; ?>
" type="hidden">
		<?php 
		   include('./include/common/pagination.php');
		 ?>
	</tr>
</table>
<input type='hidden' name='o' id='o' value='42'>
<input type='hidden' id='limit' name='limit' value='<?php echo $this->_tpl_vars['limit']; ?>
'>	
<?php echo $this->_tpl_vars['form']['hidden']; ?>

</form>
<?php echo '
<script type=\'text/javascript\'>
	setDisabledRowStyle();
</script>
'; ?>