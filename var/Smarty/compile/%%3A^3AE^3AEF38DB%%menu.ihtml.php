<?php /* Smarty version 2.6.18, created on 2014-01-06 15:58:12
         compiled from menu.ihtml */ ?>
<div id="forMenuAjax">
	<div id="<?php echo $this->_tpl_vars['Menu1ID']; ?>
">
		    <div id="<?php echo $this->_tpl_vars['Menu1Color']; ?>
">
		        <ul>
		        <?php unset($this->_sections['elem1']);
$this->_sections['elem1']['name'] = 'elem1';
$this->_sections['elem1']['loop'] = is_array($_loop=$this->_tpl_vars['elemArr1']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['elem1']['show'] = true;
$this->_sections['elem1']['max'] = $this->_sections['elem1']['loop'];
$this->_sections['elem1']['step'] = 1;
$this->_sections['elem1']['start'] = $this->_sections['elem1']['step'] > 0 ? 0 : $this->_sections['elem1']['loop']-1;
if ($this->_sections['elem1']['show']) {
    $this->_sections['elem1']['total'] = $this->_sections['elem1']['loop'];
    if ($this->_sections['elem1']['total'] == 0)
        $this->_sections['elem1']['show'] = false;
} else
    $this->_sections['elem1']['total'] = 0;
if ($this->_sections['elem1']['show']):

            for ($this->_sections['elem1']['index'] = $this->_sections['elem1']['start'], $this->_sections['elem1']['iteration'] = 1;
                 $this->_sections['elem1']['iteration'] <= $this->_sections['elem1']['total'];
                 $this->_sections['elem1']['index'] += $this->_sections['elem1']['step'], $this->_sections['elem1']['iteration']++):
$this->_sections['elem1']['rownum'] = $this->_sections['elem1']['iteration'];
$this->_sections['elem1']['index_prev'] = $this->_sections['elem1']['index'] - $this->_sections['elem1']['step'];
$this->_sections['elem1']['index_next'] = $this->_sections['elem1']['index'] + $this->_sections['elem1']['step'];
$this->_sections['elem1']['first']      = ($this->_sections['elem1']['iteration'] == 1);
$this->_sections['elem1']['last']       = ($this->_sections['elem1']['iteration'] == $this->_sections['elem1']['total']);
?>
		      	<?php if ($this->_tpl_vars['elemArr1'][$this->_sections['elem1']['index']]['Menu1UrlPopup']): ?>
		      	<li><div id="<?php echo $this->_tpl_vars['elemArr1'][$this->_sections['elem1']['index']]['Menu1ClassImg']; ?>
"><a href='<?php echo $this->_tpl_vars['elemArr1'][$this->_sections['elem1']['index']]['Menu1UrlPopupOpen']; ?>
' target="_blank"><?php echo $this->_tpl_vars['elemArr1'][$this->_sections['elem1']['index']]['Menu1Name']; ?>
</a></div></li>
		      	<?php else: ?>
		      	<li><div id="<?php echo $this->_tpl_vars['elemArr1'][$this->_sections['elem1']['index']]['Menu1ClassImg']; ?>
"><a href='#' onClick="loadAjax(<?php echo $this->_tpl_vars['elemArr1'][$this->_sections['elem1']['index']]['Menu1Page']; ?>
); return false;"><?php echo $this->_tpl_vars['elemArr1'][$this->_sections['elem1']['index']]['Menu1Name']; ?>
</a></div></li>
		      	<?php endif; ?>
				<?php endfor; endif; ?>
		        </ul>
		    </div>
	</div>
	<div id="<?php echo $this->_tpl_vars['Menu2Color']; ?>
">
	    <div id="<?php echo $this->_tpl_vars['Menu2ID']; ?>
">
	        <?php unset($this->_sections['elem2']);
$this->_sections['elem2']['name'] = 'elem2';
$this->_sections['elem2']['loop'] = is_array($_loop=$this->_tpl_vars['elemArr2']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['elem2']['show'] = true;
$this->_sections['elem2']['max'] = $this->_sections['elem2']['loop'];
$this->_sections['elem2']['step'] = 1;
$this->_sections['elem2']['start'] = $this->_sections['elem2']['step'] > 0 ? 0 : $this->_sections['elem2']['loop']-1;
if ($this->_sections['elem2']['show']) {
    $this->_sections['elem2']['total'] = $this->_sections['elem2']['loop'];
    if ($this->_sections['elem2']['total'] == 0)
        $this->_sections['elem2']['show'] = false;
} else
    $this->_sections['elem2']['total'] = 0;
if ($this->_sections['elem2']['show']):

            for ($this->_sections['elem2']['index'] = $this->_sections['elem2']['start'], $this->_sections['elem2']['iteration'] = 1;
                 $this->_sections['elem2']['iteration'] <= $this->_sections['elem2']['total'];
                 $this->_sections['elem2']['index'] += $this->_sections['elem2']['step'], $this->_sections['elem2']['iteration']++):
$this->_sections['elem2']['rownum'] = $this->_sections['elem2']['iteration'];
$this->_sections['elem2']['index_prev'] = $this->_sections['elem2']['index'] - $this->_sections['elem2']['step'];
$this->_sections['elem2']['index_next'] = $this->_sections['elem2']['index'] + $this->_sections['elem2']['step'];
$this->_sections['elem2']['first']      = ($this->_sections['elem2']['iteration'] == 1);
$this->_sections['elem2']['last']       = ($this->_sections['elem2']['iteration'] == $this->_sections['elem2']['total']);
?>
	            <span class="separator_menu2"><?php echo $this->_tpl_vars['elemArr2'][$this->_sections['elem2']['index']]['Menu2Sep']; ?>
</span>
	            <span class="span2"><?php if ($this->_tpl_vars['elemArr2'][$this->_sections['elem2']['index']]['Menu2UrlPopup']): ?><a href='<?php echo $this->_tpl_vars['elemArr2'][$this->_sections['elem2']['index']]['Menu2UrlPopupOpen']; ?>
' target="_blank" style="white-space:nowrap;"><?php echo $this->_tpl_vars['elemArr2'][$this->_sections['elem2']['index']]['Menu2Name']; ?>
</a><?php else: ?><a href='<?php echo $this->_tpl_vars['elemArr2'][$this->_sections['elem2']['index']]['Menu2Url']; ?>
' style="white-space:nowrap;"><?php echo $this->_tpl_vars['elemArr2'][$this->_sections['elem2']['index']]['Menu2Name']; ?>
</a><?php endif; ?></span>
			<?php endfor; endif; ?>
	    </div>
	</div>
</div>