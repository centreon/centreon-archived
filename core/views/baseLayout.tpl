<!DOCTYPE html>
<html>
<head>
    <title>{block name="title"}{/block} - Centreon : IT Monitoring</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{url_static url='/centreon/img/favicon_centreon.ico'}" type="image/x-icon">
    {foreach from=$cssFileList item='cssFile'}
    {$cssFile|css}
    {/foreach}
    {block name="style-head"}{/block}
</head>
<body>
<!-- Wrapper -->
<div id="wrapper">
  <nav class="navbar navbar-default navbar-static-top topbar" role="navigation">
    <div class="navbar-header">
      <a href="{get_user_homepage}" class="navbar-brand">{block name="appname"}Centreon{/block}</a>
    </div>
    {environment}
    <div class="navbar-right">
      <ul class="nav navbar-nav navbar-left">
        <li class="infraInfo">
          <a href="#" class="dropdown-toggle drop-avatar" data-toggle="dropdown">
            <i class="fa fa-gears"></i>
          </a>
        </li>
        <li class="notif">
          <a href="#" class="dropdown-toggle drop-avatar" data-toggle="dropdown">
            <i class="fa fa-envelope"></i>
          </a>
        </li>
        <li class="time">
          <span class="clock"></span>
        </li>
        <li class="user">
          <a class="account dropdown-toggle" data-toggle="dropdown" href="#">
            <div class="avatar">
              <img src="http://www.gravatar.com/avatar/{$md5Email}/?rating=PG&size=18&default=" alt="Avatar" class="img-circle">
            </div>
          </a>
          <ul class="dropdown-menu">
            {environment_user}
            <li class="divider"></li>
            <li><a href="#"><i class="fa fa-user"></i> {t}Profile{/t}</a></li>
            <li><a href="#"><i class="fa fa-cog"></i> {t}Settings{/t}</a></li>
            <li><a href="#"><i class="fa fa-envelope"></i> {t}Messages{/t}</a></li>
            <li class='divider'></li>
            <!-- <li>
              <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-light">Light</button>
                <button type="button" class="btn btn-dark">Dark</button>
              </div>
            </li>
            <li class='divider'></li> -->
            <li><a href="#" id="logout"><i class="glyphicon glyphicon-off"></i> {t}Logout{/t}</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </nav>
  {block name="full-content"}
  <div id="main">
  </div>
  {/block}
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
                <li><a href="http://www.merethis.com/">Merethis</a></li>
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
  </div>
</div>
{foreach from=$jsBottomFileList item='jsFile'}
{$jsFile|js}
{/foreach}
<script>
$(document).ready(function() {
    resizeContent();
    $('.btn-light').on('click', function() {
        switchTheme('light');
    });
    $('.btn-dark').on('click', function() {
        switchTheme('dark');
    });
    $('#footer-button').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        toggleFooter();
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
    /* Timer */
    topClock();
});
</script>
{block name="javascript-bottom"}
<script>{$customJs}</script>
{/block}
</body>
</html>
