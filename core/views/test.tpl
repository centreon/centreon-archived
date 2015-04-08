
{block name="full-content"}
<aside id="left-panel">
  <nav>
    <ul class="nav" id="menu1">
    </ul>
  </nav>
  {hook name='displayLeftMenu' container='<nav><ul class="nav" id="hook-menu">[hook]</ul></nav>'}
  <div class="toggle-button">
    <a href="#"><i class="fa fa-angle-double-left"></i></a>
  </div>
</aside>
<div class="content" id="main">
<div class="flash alert fade in" id="flash-message" style="display: none;">
  <button type="button" class="close" aria-hidden="true">&times;</button>
</div>
{block name="content"}
{/block}
</div>
{/block}

{block name="javascript-bottom" append}
<script>
{if isset($jsUrl)}
var jsUrl = {$jsUrl|json_encode};
{else}
var jsUrl = {};
{/if}
$(document).ready(function() {
    resizeContentLeftPanel();
    $(window).off('resize');
    $(window).on('resize', function() {
        resizeContentLeftPanel();
    });
    $('#main').on('resize', function() {
        resizeContentLeftPanel();
    });
    var mdata = {get_environment_id};
    loadMenu('{url_for url="/menu/getmenu/"}', mdata.envid, mdata.subid, mdata.childid);
    $('a.envmenu').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $("#hook-menu").html("");
        loadMenu('{url_for url="/menu/getmenu/"}', $(this).data('menu'), 0, 0);
    });
    $('#flash-message').on('click', 'button.close', function() {
        alertClose();
    });
    $('body').on('click', '#menu1 li', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var target = e.currentTarget;
        /* Test if extand */
        if ($(target).parents("#left-panel").hasClass('mini')) {
            var $a = $(target).find('a');
            var menuId = $a.data('menuid');
            if ($a.hasClass('accordion-toggle')) {
                var $submenu = $("#submenu_" + menuId);
                if ($submenu.length == 0) {
                    return;
                }
                /* Get pos */
                var pos = $a.offset();
                $submenu.css({
                    top: pos.top,
                    left: pos.left + $(target).width()
                }).toggleClass("show");
            }
            $('body').one('click', function() {
                $submenu.toggleClass("show");
            });
        } else {
            $(target).find('ul').collapse('toggle');
            $(target).find('i.toggle').toggleClass('fa-plus-square-o').toggleClass('fa-minus-square-o');
        }

        var targetUrl = $(target).find('a').attr('href');
        if (targetUrl !== undefined) {
            document.location.href = targetUrl;
        }
    });

    $( "#left-panel .toggle-button a" ).on( "click", function( e ) {
      e.preventDefault();
      e.stopPropagation();
      $( this ).find( "i.fa" ).toggleClass( "fa-angle-double-left" ).toggleClass( "fa-angle-double-right" );
      /* Reduce submenu if go to mini */
      if ( ! $( "aside" ).hasClass( "mini" )) {
          var $listToggle = $( "#left-panel a.accordion-toggle" );
          $listToggle.parent( "li" ).find( "ul" ).collapse( "hide" );
          $listToggle.find( "i.toggle" ).removeClass( "fa-minus-square-o" ).addClass( "fa-plus-square-o" );
      }
      $( "aside" ).toggleClass( "mini" );
      $( ".content" ).toggleClass( "mini" );
      $( ".bottombar" ).toggleClass( "mini" );
      $( "#menu1" ).find( "li span" ).toggle();
      $( "#menu1" ).find( ".toggle" ).toggle();
    });
    
    loadBookmark('{url_for url="/bookmark/list"}');

  /* Init tooltips */
  $( ".bottombar a" ).tooltip();
});
</script>
{/block}
