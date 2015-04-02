<!DOCTYPE html>
<html>
<head>
    <title>{block name="title"}{/block} - Centreon : IT Monitoring</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{url_static url='/centreon/img/centreonFavicon.ico'}" type="image/x-icon">
    {foreach from=$cssFileList item='cssFile'}
    {$cssFile|css}
    {/foreach}
    {block name="style-head"}{/block}
</head>

<body class="bodyCntr">

<div class="mainCntr" id="mainCntr"> <!-- Global Wrapper -->

    <!-- Menu aside -->
    <nav class="navbar-default navbarSide navbar-static-side" role="navigation">
        <div class="sidebar-collapse">
            <ul class="nav" id="side-menu">
                <li class="logo">
                    <!--<a href="{get_user_homepage}" class="navbar-brand">{block name="appname"}<i class="fa fa-cube"></i> Centreon{/block}</a>-->
                    <div class="navbar-header">
                        <a class="navbar-minimalize minimalize-styl-2" href="#">
                            <img src="static/centreon/img/icons/ico-centreon.svg" alt="Centreon" />
                            <span class="nav-label">Centreon</span> </a>
                    </div>
                </li>
                    {foreach $appMenu as $menuLevel1}
                           <li>
                                <a href="#">
                                    <i class="{$menuLevel1.icon_class}"></i>
                                        <span class="nav-label">{$menuLevel1.name}</span>
                                        {if count($menuLevel1.children) > 0}
                                            <span class="fa arrow"></span>
                                        {/if}
                                </a>
                                <ul class="nav nav-second-level">
                                    {foreach $menuLevel1.children as $menuLevel2}
                                    <li>
                                        <a href="#">
                                            {$menuLevel2.name}
                                            {if count($menuLevel2.children) > 0}
                                                <span class="fa arrow"></span>
                                            {/if}
                                        </a>
                                        <ul class="nav nav-third-level">
                                            {foreach $menuLevel2.children as $menuLevel3}
                                                <li>
                                                    <a href="#">{$menuLevel3.name}</a>
                                                </li>
                                            {/foreach}
                                        </ul>
                                    </li>
                                    {/foreach}
                                </ul>
                           </li>
                    {/foreach}
                </li>
                <li class="landing_link">
                    <a href="#"><i class="fa fa-star"></i> <span class="nav-label">Bookmark</span></a>

                    {hook name='displayLeftMenu' container='<ul class="nav" id="hook-menu">[hook]</ul>'}
                    <div class="toggle-button">
                        <a href="#"><i class="fa fa-angle-double-left"></i></a>
                    </div>
                </li>
            </ul>
        </div>
    </nav>

    <div class="viewCntr" id="pageWrapper"> <!-- Page Wrapper -->

        <div class="row">
        <nav class="navbar-default navbar-static-top GlobalNavbar">

                    <ul class="userProfil nav navbar-right">
                        <li class="dropdown">
                             <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                                <img src="{url_static url='/centreon/img/default-ico-user.png'}" alt="User Avatar">
                             </a>
                             <ul class="dropdown-menu profilSubmenu" role="menu">
                                <li>
                                   <div class="media">
                                     <div class="media-left media-middle">
                                       <a href="#">
                                         <img src="{url_static url='/centreon/img/avatar.png'}" alt="User avatar" style="width: 60px; height: 60px;">
                                       </a>
                                     </div>
                                     <div class="media-body">
                                       <strong>User Name</strong>
                                       <p><small>adress@email.com</small></p>
                                        <ul class="list-unstyled list-inline ">
                                            <li>
                                                <a href="#" id="help">{t}Help{/t}</a>
                                            </li>
                                            <li>
                                                <a href="#" id="logout">{t}Logout{/t}</a>
                                            </li>
                                        </ul>

                                     </div>
                                   </div>
                                </li>
                                <li>
                                    <ul class="list-unstyled adminList">
                                        <li><a href="#">{t}Edit Profile{/t}</a></li>
                                        <li><a href="#">{t}Settings{/t}</a></li>
                                        <li>{environment_user}</li>
                                    </ul>
                                </li>
                             </ul>
                         </li>
                    </ul>
                    <ul class="timeZone nav navbar-right">
                        <li class="time">
                             <span class="clock"></span>
                         </li>
                    </ul>
                    <ul class="indicators nav navbar-right">
                        <li class="top-counter top-counter-service">
                             <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                               <i class="icon-critical-incident"></i>
                             </a>
                             <span class="label label-danger hide">42</span>
                             <ul class="dropdown-menu">
                             </ul>
                         </li>
                         <li class="top-counter top-counter-service">
                              <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                <i class="icon-incident"></i>
                              </a>
                              <span class="label label-warning hide"></span>
                              <ul class="dropdown-menu">
                              </ul>
                          </li>

                        <li class="top-counter top-counter-service">
                            <a href="#" class="dropdown-toggle drop-avatar">
                                <i class="icon-pollers"></i>
                            </a>
                        </li>

                        <li class="top-counter top-counter-bam">
                            <a href="#" class="dropdown-toggle drop-avatar" data-toggle="dropdown">
                                <i class="icon-BAM"></i>
                            </a>
                            <span class="label label-danger hide"></span>
                            <span class="label label-warning hide"></span>
                            <ul class="dropdown-menu"></ul>
                        </li>
                    </ul>
            </nav>
        </div>
    </div>
</div>
<!-- Wrapper -->
<!--
<div id="wrapper">

  <div class="bottombar">
       <div class="label-button pull-right">
         Centreon &copy; 2005-2014 <a href="#" id="footer-button"><i class="fa fa-chevron-circle-up"></i></a>
       </div>
       <div class="footer-extended">
         <div>
             <ul class="center-block list-inline">
                 <li><a href="http://documentation.centreon.com/">{t}Documentation{/t}</a></li>
                 <li><a href="http://forge.centreon.com/projects/centreon/issues/new">{t}Found a bug ?{/t}</a></li>
                 <li><a href="http://forge.centreon.com">{t}Forge{/t}</a></li>
             </ul>
         </div>
         <div>
             <ul class="center-block list-inline">
                 <li><a href="http://www.centreon.com/">Centreon</a></li>
                 <li><a href="http://www.centreon.com/">Merethis</a></li>
             </ul>
         </div>
         <div>
             <ul class="center-block list-inline">
                 <li><a href="https://twitter.com/Centreon"><i class="fa fa-twitter-square"></i></a></li>
                 <li><a href="https://plus.google.com/u/0/s/centreon"><i class="fa fa-google-plus-square"></i></a></li>
                 <li><a href="https://www.facebook.com/groups/6316094758/?fref=ts"><i class="fa fa-facebook-square"></i></a></li>
             </ul>
         </div>
      </div>
   </div> -->
{foreach from=$jsBottomFileList item='jsFile'}
{$jsFile|js}
{/foreach}
<script>
    $(document).ready(function() {
        var statusInterval, statusData,
                eStatus = new $.Event('centreon.refresh_status');
        resizeContent();
        $('.btn-light').on('click', function() {
            switchTheme('light');
        });
        $('.btn-dark').on('click', function() {
            switchTheme('dark');
        });

        $('#logout').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $.ajax({
                url: "{url_for url='/logout'}",
                type: "GET",
                success: function(data, textStatus, jqXHR) {
                    if (data.status) {
                        window.location.href = "{url_for url='/login'}";
                    }
                }
            });
        });
        $(window).on('resize', function() {
            resizeContent();
        });

        function loadStatusData() {
            $.ajax({
                url: "{url_for url='/status'}",
                type: 'GET',
                success: function(data, textStatus, jqXHR) {
                    statusData = data;
                    $(document).trigger(eStatus);
                }
            });
        }

        /* Timer */
        topClock();

        {hook name='displayJsStatus' container='[hook]'}

        statusInterval = setInterval(function() {
            loadStatusData();
        }, 5000);
        loadStatusData();
    });

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
{block name="javascript-bottom"}
<script>{get_custom_js}</script>
{/block}
</body>
</html>
