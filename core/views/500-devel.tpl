<html>
<head>
    <title>{t}Internal Server Error{/t} - Centreon : IT Monitoring</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{url_static url='/centreon/img/favicon_centreon.ico'}" type="image/x-icon">
    {foreach from=$cssFileList item='cssFile'}
    {$cssFile|css}
    {/foreach}
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-sm-8 col-sm-offset-2">
            <h2>Internal server error</h2>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-8 col-sm-offset-2 alert alert-warning">
           <div class="pull-right">
               <a href="#" id="expand"><i class="fa fa-plus-square-o"></i></a>
           </div>
           {$exceptionMessage}
        </div>
        <div class="col-sm-8 col-sm-offset-2" style="overflow-y: hidden; height: 0; transition: height 1s;">
           <div class="well"><pre>{$strace}</pre></div>
        </div>
    </div>
</div>
{foreach from=$jsBottomFileList item='jsFile'}
{$jsFile|js}
{/foreach}
<script>
$(function() {
  $("#expand").on("click", function (e) {
    e.preventDefault();
    if ($(".strace").height() == 0) {
      $(".strace").height($(".strace > .well").height());
      $("#expand > i.fa").removeClass("fa-plus-square-o").addClass("fa-minus-square-o");
    } else {
      $(".strace").height(0);
      $("#expand > i.fa").addClass("fa-plus-square-o").removeClass("fa-minus-square-o");
    }
  });
});
</script>
</body>
</html>

