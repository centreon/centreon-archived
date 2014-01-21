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
<div id="environment-menu" style="display: none;"></div>
{/block}

{block name="javascript-bottom"}
<script>
$(document).ready(function() {
    leftPanelHeight();
    $(window).on('resize', function() {
        leftPanelHeight();
    });
    $('.env-menu').on('click', function(e) {
        e.stopPropagation();
        e.preventDefault();
        displayEnvironmentMenu();
    });
});
</script>
{/block}
