{extends file="baseLayout.tpl"}
{block name="full-content"}
<aside id="left-panel">
  <div class="environment">
    <span>
      <a href="#"><i class="fa fa-dashboard"></i></a>
      <a href="#" class="env-menu">{t}Environment{/t} <i class="fa fa-chevron-right"></i></a>
    </span>
  </div>
  <nav>
    <ul class="nav" id="menu1">
    </ul>
  </nav>
</aside>
<div class="content" id="main">
</div>
<div id="environment-menu" style="display: none;">
<ul class="list-inline">
{foreach $envmenu as $menu}
<li class="envmenu"{if $menu.bgcolor} style="background-color: {$menu.bgcolor};"{/if} data-menu="{$menu.short_name}">
<div class="icon">
{if $menu.icon_class}
<i class="{$menu.icon_class}"></i>
{elseif $menu.icon_img}
<img src="{$menu.icon_img}" class="">
{/if}
</div>
<div class="name">
{$menu.name}
</div>
</li>
{/foreach}
</ul>
</div>
{/block}

{block name="javascript-bottom"}
<script>
$(document).ready(function() {
    leftPanelHeight();
    $(window).on('resize', function() {
        leftPanelHeight();
    });
    loadMenu('configuration');
    $('.env-menu').on('click', function(e) {
        e.stopPropagation();
        e.preventDefault();
        displayEnvironmentMenu();
    });
    $('body').on('click', '#menu1 li', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var target = e.currentTarget;
        $(target).find('ul').collapse('toggle');
        if ($(target).find('i.toggle').hasClass('fa-plus-square-o')) {
            $(target).find('i.toggle').removeClass('fa-plus-square-o');
            $(target).find('i.toggle').addClass('fa-minus-square-o');
        } else {
            $(target).find('i.toggle').removeClass('fa-minus-square-o');
            $(target).find('i.toggle').addClass('fa-plus-square-o');
        }
    });
});
</script>
{/block}
