<!DOCTYPE html>
<html>
<head>
    <title>{block name="title"}{/block} - Centreon : IT Monitoring</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{url_static url='/centreon/img/favicon_centreon.ico'}" type="image/x-icon">
    {foreach from=$cssFileList item='cssFile'}
    {$cssFile|css}
    {/foreach}
</head>
<body>
<nav class="navbar navbar-default navbar-static-top topbar" role="navigation">
  <div class="navbar-header">
    <a href="/" class="navbar-brand">{block name="appname"}Centreon{/block}</a>
  </div>
  <div class="navbar-right">
    <ul class="nav navbar-nav navbar-left">
      <li class="notif">
        <a href="#" class="dropdown-toggle drop-avatar" data-toggle="dropdown">
          <i class="fa fa-envelope"></i>
        </a>
      </li>
      <li class="time">
        <span class=""></span>
      </li>
      <li class="user">
        <a href="#" class="dropdown-toggle drop-avatar" data-toggle="dropdown">
          <i class="fa fa-user"></i>
        </a>
        <ul class="dropdown-menu">
          <li>
            <div class="btn-group btn-group-sm">
              <button type="button" class="btn btn-light">Light</button>
              <button type="button" class="btn btn-dark">Dark</button>
            </div>
          </li>
        </ul>
      </li>
    </ul>
  </div>
</nav>
{block name="full-content"}
<div id="main">
</div>
{/block}
<nav class="navbar navbar-default navbar-static-bottom bottombar">
<div class="footer">
    <div class="pull-left">Centreon - Copyright &copy; 2005 - 2014 Merethis</div>
    <div class="pull-right">
        <a href="#" id="footer-button"><i class="fa fa-chevron-circle-up"></i></a>
    </div>
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
</nav>
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
    $(window).on('resize', function() {
        resizeContent();
    });
});
</script>
{block name="javascript-bottom"}{/block}
</body>
</html>
