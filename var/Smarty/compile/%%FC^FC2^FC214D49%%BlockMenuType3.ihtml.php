<?php /* Smarty version 2.6.18, created on 2014-01-06 15:58:12
         compiled from BlockMenuType3.ihtml */ ?>
<div id="contener"><!-- begin contener -->
<?php $this->assign('cpt', 0); ?>
<table id="Tcontener">
	<tr>
		<td id="Tmenu" class="TcTD">
			<div id="<?php echo $this->_tpl_vars['Menu3Color']; ?>
">
				<div class="menuLeft">
				<?php $_from = $this->_tpl_vars['elemArr3']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['curr_id']):
?>				
					<div style="margin:3px;"><?php if ($this->_tpl_vars['cpt']): ?><br /><?php endif; ?><img src='./img/icones/12x12/doublearrowsnav.gif' style='padding-bottom:2px;' alt='icon_design' />&nbsp;<?php echo $this->_tpl_vars['curr_id']['title']; ?>
</div>
					<div style="margin:0px;padding-top:6px;">
						<ul>
							<?php $_from = $this->_tpl_vars['curr_id']['tab']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['curr_id2']):
?>
								<li<?php if ($this->_tpl_vars['curr_id2']['MenuIsOnClick']): ?> onclick="<?php echo $this->_tpl_vars['curr_id2']['MenuOnClick']; ?>
()"<?php endif; ?> id="menu_<?php echo $this->_tpl_vars['curr_id2']['Menu3ID']; ?>
">
								<?php if ($this->_tpl_vars['curr_id2']['Menu3Icone']): ?><img src='<?php echo $this->_tpl_vars['curr_id2']['Menu3Icone']; ?>
' title="<?php echo $this->_tpl_vars['curr_id2']['Menu3Name']; ?>
" style="padding-left:5px;" /><?php endif; ?>
								<?php if ($this->_tpl_vars['curr_id2']['Menu3Popup']): ?>
									<a href=<?php echo $this->_tpl_vars['curr_id2']['Menu3UrlPopup']; ?>
 target="_blank" title="<?php echo $this->_tpl_vars['curr_id2']['Menu3Name']; ?>
"><?php echo $this->_tpl_vars['curr_id2']['Menu3Name']; ?>
</a>											
								<?php else: ?>
									<?php if ($this->_tpl_vars['curr_id2']['MenuIsOnClick']): ?><?php echo $this->_tpl_vars['curr_id2']['Menu3Name']; ?>
<?php else: ?><a href=<?php echo $this->_tpl_vars['curr_id2']['Menu3Url']; ?>
 title="<?php echo $this->_tpl_vars['curr_id2']['Menu3Name']; ?>
"><?php echo $this->_tpl_vars['curr_id2']['Menu3Name']; ?>
</a><?php endif; ?>
								<?php endif; ?>
								</li>
								<ul>
								<?php $_from = $this->_tpl_vars['elemArr4'][$this->_tpl_vars['curr_id2']['Menu3ID']]; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['menu4']):
?>
									<li <?php if ($this->_tpl_vars['menu4']['MenuIsOnClick']): ?>onclick="<?php echo $this->_tpl_vars['menu4']['MenuOnClick']; ?>
()"<?php endif; ?>>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;
									<?php if ($this->_tpl_vars['menu4']['Menu4Popup']): ?><?php if ($this->_tpl_vars['menu4']['MenuIsOnClick']): ?><?php echo $this->_tpl_vars['menu4']['Menu4Name']; ?>
<?php else: ?><a href=<?php echo $this->_tpl_vars['menu4']['Menu4UrlPopup']; ?>
 target="_blank" style="white-space:nowrap;" title="<?php echo $this->_tpl_vars['menu4']['Menu4Name']; ?>
"><?php echo $this->_tpl_vars['menu4']['Menu4Name']; ?>
</a><?php endif; ?>
									<?php else: ?><?php if ($this->_tpl_vars['menu4']['MenuIsOnClick']): ?><?php echo $this->_tpl_vars['menu4']['Menu4Name']; ?>
<?php else: ?><a href=<?php echo $this->_tpl_vars['menu4']['Menu4Url']; ?>
 style="white-space:nowrap;" title="<?php echo $this->_tpl_vars['menu4']['Menu4Name']; ?>
"><?php echo $this->_tpl_vars['menu4']['Menu4Name']; ?>
</a><?php endif; ?>
									<?php endif; ?>
									</li>
								<?php endforeach; endif; unset($_from); ?>
								</ul><?php if ($this->_tpl_vars['elemArr4'][$this->_tpl_vars['curr_id2']['Menu3ID']]): ?><?php endif; ?>
							<?php endforeach; endif; unset($_from); ?>
				        </ul>
					</div>
				<?php $this->assign('cpt', 1); ?>
				<?php endforeach; endif; unset($_from); ?>
				<br />		
				</div>
				<?php if ($this->_tpl_vars['amIadmin']): ?>
				<div class="menuLeft">
					<div style="margin:3px;"><img src='./img/icones/12x12/doublearrowsnav.gif' title='<?php echo $this->_tpl_vars['user']['ip']; ?>
' style='padding-bottom:2px;' alt='icon_design' />&nbsp;<?php echo $this->_tpl_vars['connected_users']; ?>
</div>
	  	            <div  style="margin:0px;padding-top:6px;">
	  	             	<ul>
		  	            <?php $_from = $this->_tpl_vars['tab_user']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['user']):
?>
		  	            	<li>
		  	             		<?php if ($this->_tpl_vars['user']['admin'] == 1): ?>
		  	             		<img src='./img/icones/16x16/guard.gif' title='<?php echo $this->_tpl_vars['user']['ip']; ?>
' style='padding:1px;' />
								<?php else: ?>
		  	             		<img src='./img/icones/16x16/user1.gif' title='<?php echo $this->_tpl_vars['user']['ip']; ?>
' style='padding:1px;' />
								<?php endif; ?>
		  	             		<?php if ($this->_tpl_vars['user']['alias'] == $this->_tpl_vars['UserName']): ?>
		 	       					<a href="<?php echo $this->_tpl_vars['UserInfoUrl']; ?>
"><?php echo $this->_tpl_vars['user']['alias']; ?>
</a>
		  	             		<?php else: ?>
		  	             			<?php echo $this->_tpl_vars['user']['alias']; ?>

		  	           			<?php endif; ?>
		  	             	</li>
		  	            <?php endforeach; endif; unset($_from); ?> 
						</ul><br />
					</div>
				</div>
				<?php endif; ?>
			</div>
		</td>
		<td id="Tmainpage" class="TcTD" valign="top">
		<img src="./img/icones/7x7/sort_left.gif" onclick="new Effect.toggle('menu_3'); xhr = new XMLHttpRequest(); xhr.open('GET','<?php echo $this->_tpl_vars['user_update_pref_menu_3']; ?>
', true);xhr.send(null);" style="position:relative;bottom:10px;left:-10px;" alt="hide_or_show_menu" /><img src="./img/icones/7x7/sort_asc.gif" onclick="new Effect.toggle('menu_2');Effect.toggle('menu1_bgcolor');<?php if ($this->_tpl_vars['PageID'] == 2 || $this->_tpl_vars['PageID'] == 4): ?>Effect.toggle('QuickSearch')<?php endif; ?>" style="float:right;position:relative;top:2px;right:3px" alt="hide_or_show_menu" />