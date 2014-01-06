<?php /* Smarty version 2.6.18, created on 2014-01-06 15:58:13
         compiled from pagination.ihtml */ ?>
<td class="ToolbarPagination">
	<?php if ($this->_tpl_vars['firstPage']): ?>&nbsp;<a href="<?php echo $this->_tpl_vars['firstPage']; ?>
<?php if ($this->_tpl_vars['host_name']): ?>&host_name=<?php echo $this->_tpl_vars['host_name']; ?>
<?php endif; ?>"><img src="./img/icones/16x16/arrow_left_blue_double.gif" title='<?php echo $this->_tpl_vars['first']; ?>
'></a><?php endif; ?>
	<?php if ($this->_tpl_vars['pagePrev']): ?>&nbsp;<a href="<?php echo $this->_tpl_vars['pagePrev']; ?>
<?php if ($this->_tpl_vars['host_name']): ?>&host_name=<?php echo $this->_tpl_vars['host_name']; ?>
<?php endif; ?>"><img src="./img/icones/16x16/arrow_left_blue.gif" title='<?php echo $this->_tpl_vars['previous']; ?>
'></a><?php endif; ?>
	<?php $_from = $this->_tpl_vars['pageArr']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['key'] => $this->_tpl_vars['item']):
?>
		<?php if ($this->_tpl_vars['pageArr'][$this->_tpl_vars['key']]['num'] != $this->_tpl_vars['num']): ?>
			&nbsp;<a href="<?php echo $this->_tpl_vars['pageArr'][$this->_tpl_vars['key']]['url_page']; ?>
<?php if ($this->_tpl_vars['host_name']): ?>&host_name=<?php echo $this->_tpl_vars['host_name']; ?>
<?php endif; ?>" class="otherPageNumber"><?php echo $this->_tpl_vars['pageArr'][$this->_tpl_vars['key']]['label_page']; ?>
</a>
		<?php else: ?>
			&nbsp;<b class="currentPageNumber"><?php echo $this->_tpl_vars['pageArr'][$this->_tpl_vars['key']]['label_page']; ?>
</b>
		<?php endif; ?>
	<?php endforeach; endif; unset($_from); ?>
	<?php if ($this->_tpl_vars['pageNext']): ?>&nbsp;<a href="<?php echo $this->_tpl_vars['pageNext']; ?>
<?php if ($this->_tpl_vars['host_name']): ?>&host_name=<?php echo $this->_tpl_vars['host_name']; ?>
<?php endif; ?>"><img src="./img/icones/16x16/arrow_right_blue.gif" title='<?php echo $this->_tpl_vars['next']; ?>
'></a><?php endif; ?>	
	<?php if ($this->_tpl_vars['lastPage']): ?>&nbsp;<a href="<?php echo $this->_tpl_vars['lastPage']; ?>
<?php if ($this->_tpl_vars['host_name']): ?>&host_name=<?php echo $this->_tpl_vars['host_name']; ?>
<?php endif; ?>"><img src="./img/icones/16x16/arrow_right_blue_double.gif" title='<?php echo $this->_tpl_vars['last']; ?>
'></a><?php endif; ?>	
</td>
<td class="Toolbar_pagelimit"><?php echo $this->_tpl_vars['form']['l']['label']; ?>
</b>&nbsp;<?php echo $this->_tpl_vars['form']['l']['html']; ?>
&nbsp;&nbsp;<?php echo $this->_tpl_vars['pagin_page']; ?>
&nbsp;<?php echo $this->_tpl_vars['pageNumber']; ?>
</td>
<?php echo $this->_tpl_vars['form']['hidden']; ?>