{extends file="file:[Core]viewLayout.tpl"}
{block name="title"}
  {t}Host Details{/t} : {$hostname}
{/block}

{block name="content"}
<div class="container-fluid">
  <div class="row row-detail">
    <div class="col-xs-12 col-sm-7 detail-info" id="general">
      <div class="container-fluid">
        <div class="row">
          <div class="col-xs-8">
            <h4>{$hostname} :  {$ipaddress}</h4>
          </div>
          <div class="col-xs-8">
            <span class="longoutput"></span>
          </div>
          <div class="col-xs-12 reporting">
            <div id="month_reporting"></div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xs-12 col-sm-5 detail-info" id="network">
      <div class="container-fluid">
        <div class="row">
          <div class="col-xs-12">
            <h4>{t}Network{/t}</h4>
          </div>
          <div class="col-xs-12 listing">
            <table>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <div class="row row-detail">
    <div class="col-xs-12 col-sm-6 detail-info" id="system">
      <div class="container-fluid">
        <div class="row">
          <div class="col-xs-12">
            <h4>{t}System{/t}</h4>
          </div>
          <div class="col-xs-4" id="cpu">
          </div>
          <div class="col-xs-4" id="memory">
          </div>
          <div class="col-xs-4" id="swap">
          </div>
        </div>
      </div>
    </div>
    <div class="col-xs-12 col-sm-6 detail-info" id="filesystem">
    </div>
  </div>
  
  <div class="row row-detail">
    <div class="col-xs-12">
     <h4>{t}Applications{/t}</h4>
    </div>
    {foreach $applications as $application}
    <div class="col-xs-6 col-sm-4" id="app_{$application.id}">
      <div class="container-fluid">
        <div class="row">
          <div class="col-xs-12">
            <h4>{$application.name}</h4>
          </div>
          <div class="col-xs-12 listing">
            <table>
            </table>
          </div>
        </div>
      </div>
    </div>
    {/foreach}
  </div>
  
  <div class="row">
    <div class="col-xs-12 detail-info" id="eventlogs">
      <div class="container-fluid">
        <div class="row">
          <div class="col-xs-12">
            <h4>{t}Eventlogs{/t}</h4>
          </div>
          <div class="col-xs-12">
            <table>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
{/block}

{block name="javascript-bottom" append}
<script>
$(function() {
  var hostData,
      eData = new $.Event('centreon.host_detail'),
      hostReporting = new CalHeatMap();
  hostReporting.init({
    itemSelector: '#month_reporting',
    dataType: 'json',
    domain: 'month',
    subDomain: 'day',
    range: 12,
    start: moment().subtract(11, 'month').toDate(),
    highlight: "now",
    loadOnInit: false
  });

  function loadData() {
    $.ajax({
      url: "{url_for url='/realtime/host/[i:id]/data' params=$routeParams}",
      type: 'get',
      dataType: 'json',
      success: function(data, textStatus, jqXHR) {
        if (data.success) {
          hostData = data.values;
          $(document).trigger(eData);
        }
      }
    });
  }

  loadData();
});
</script>
{/block}
