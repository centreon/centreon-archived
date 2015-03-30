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
    <div class="container-fluid">
      <div class="navbar-header">
        <a href="{get_user_homepage}" class="navbar-brand">{block name="appname"}<i class="fa fa-cube"></i> Centreon{/block}</a>
      </div>
      {environment}
      <div class="navbar-right">
        <ul class="nav navbar-nav navbar-left">
            <li class="bookmark">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                    <i class="fa fa-star"></i>
                </a>
                <ul id="myBookmark" class="dropdown-menu"></ul>
            </li>
          <li class="top-counter top-counter-host">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <i class="fa fa-desktop"></i>
            </a>
            <span class="label label-danger hide"></span>
            <span class="label label-warning hide"></span>
            <ul class="dropdown-menu">
            </ul>
          </li>
          <li class="top-counter top-counter-service">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <i class="fa fa-rss"></i>
            </a>
            <span class="label label-danger hide"></span>
            <span class="label label-warning hide"></span>
            <ul class="dropdown-menu">
            </ul>
          </li>
          <li class="top-counter top-counter-poller">
            <a href="#" class="dropdown-toggle drop-avatar" data-toggle="dropdown">
              <i class="fa fa-gears"></i>
            </a>
            <span class="label label-danger hide"></span>
            <span class="label label-warning hide"></span>
            <ul class="dropdown-menu"></ul>
          </li>
          <li class="notif">
            <a href="#" class="dropdown-toggle drop-avatar" data-toggle="dropdown">
              <i class="fa fa-envelope"></i>
            </a>
          </li>
          <li class="time">
              <a class="account dropdown-toggle" data-toggle="dropdown" href="#"><span class="clock"></span></a>

            <ul class="dropdown-menu" id="list_timezone">
                <li >
                    <a href="#" id="modalAdd_timezone">{t}Add horloge{/t}<i class="fa fa-clock-o"></i></a>
                </li>
            </ul>
          </li>
          <li>
              <a href="#" id="undotimezone" onclick="changeTimezone()"><span class="fa fa-undo"></span></a>
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
    </div>
  </nav>
  {block name="full-content"}
  <div id="main">
  </div>
  {/block}
  
  <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="wizard" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
      </div>
    </div>
  </div>

  <!-- <div class="bottombar">
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
                <li><a href="http://www.centreon.com/">Centreon</a></li>
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
</div>
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
  

    $("body").on("click", "#modalAdd_timezone", function(e) {
        $("#modal").removeData("bs.modal");
        $("#modal").removeData("centreonWizard");
        $("#modal .modal-content").text("");
        $("#modal").one("loaded.bs.modal", function(e) {
            $(this).centreonWizard();
        });
        $("#modal").modal({
            "remote": "/centreon-administration/timezone/addtouser"
        });
    });
    
    
     $(document).on('click', '.modalDelete', function(e) {
        e.preventDefault();
        $('#modal .modal-content').text('');
        var id = $(this).data('id');

        /* Delete modal header */
        var $deleteHeader = $('<div></div>').addClass('modal-header');
        $('<button></button>')
            .attr('type', 'button')
            .attr('aria-hidden', 'true')
            .attr('data-dismiss', 'modal')
            .addClass('close')
            .html('&times;')
            .appendTo($deleteHeader);
        $('<h4></h4>').addClass('modal-title').text("{t}Delete{/t}").appendTo($deleteHeader);
        $deleteHeader.appendTo('#modal .modal-content');

        /* Delete modal body */
        var $deleteBody = $('<div></div>').addClass('modal-body');
        $('<span></span>').text('{t}Are you sure to delete ?{/t}').appendTo($deleteBody);
        var $listElement = $('<ul></ul>').addClass('list-unstyled');
        $('table[id^="datatable"] tbody tr[class*="selected"]').each(function(k, v) {
            $('<li></li>').html($(v).data('name')).appendTo($listElement);
        });
        $listElement.appendTo($deleteBody);
        $deleteBody.appendTo('#modal .modal-content');

        var $deleteFooter = $('<div></div>').addClass('modal-footer');
        $('<a></a>')
            .attr('aria-hidden', 'true')
            .attr('data-dismiss', 'modal')
            .addClass('btn').addClass('btn-default')
            .text('{t}Cancel{/t}')
            .appendTo($deleteFooter);
        var $deleteBtn = $('<button></button>')
            .attr('type', 'button')
            .addClass('btn')
            .addClass('btn-danger')
            .text('{t}Delete{/t}')
            .appendTo($deleteFooter);
        $deleteFooter.appendTo('#modal .modal-content');
        
        var objectDeleteUrl =  "/centreon-administration/user/deletetimezone";

        $deleteBtn.on('click', function(e) {
            $.ajax({
                url: objectDeleteUrl,
                type: 'POST',
                data: {
                    'id': id
                },
                dataType: 'json',
                success: function(data, textStatus, jqXHR) {
                    $('#modal').modal('hide');
                    alertClose();
                    if (data.success) {
                        oTable.fnDraw();
                        alertMessage('{t}The objects have been successfully deleted{/t}', 'alert-success', 3);
                    } else {
                        alertMessage(data.errorMessage, 'alert-danger');
                    }
                }
            });
        });

         $('#modal')
            .removeData('bs.modal')
            .modal();
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

</script>
{block name="javascript-bottom"}
<script>{get_custom_js}</script>
{/block}
</body>
</html>
