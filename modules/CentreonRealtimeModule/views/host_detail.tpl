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
          <div class="col-xs-12">
            <h4>{$host_icon} {$hostname}</h4>
          </div>
          <div class="col-xs-12">
            <div class="row">
              <div class="col-xs-2 title">{t}Address{/t}</div>
              <div class="col-xs-10">{$address}</div>
              <div class="col-xs-2 title">{t}Status{/t}</div>
              <div class="col-xs-2">
                <span class="label" id="status"></span>
              </div>
              <div class="col-xs-8">
                {t}since{/t} <span id="since_status"></span>
              </div>
            </div>
          </div>
          <div class="col-xs-12 title">
            <div class="output">{t}Output{/t}</div>
          </div>
          <div class="col-xs-12">
            <div class="longoutput"></div>
          </div>
        </div>
      </div>
      <div class="host-status">
      </div>
    </div>
    <div class="col-xs-12 col-sm-5 detail-info" id="network">
      <div class="container-fluid">
        <div class="row">
          <div class="col-xs-12">
            <h4>{t}Network{/t}</h4>
          </div>
          <div class="col-xs-6 listing">
            <ul>
            </ul>
          </div>
          <div class="col-xs-6 display-tooltip"></div>
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
            <h4>CPU</h4>
            <input name="cpu" id="gauge_cpu" class="dial" data-readOnly="true" data-angleOffset="-125" data-angleArc="250" value="0">
          </div>
          <div class="col-xs-4" id="memory">
            <h4>Memory</h4>
            <input name="memory" id="gauge_memory" class="dial" data-readOnly="true" data-angleOffset="-125" data-angleArc="250" value="0">
          </div>
          <div class="col-xs-4" id="swap">
            <h4>Swap</h4>
            <input name="swap" id="gauge_swap" class="dial" data-readOnly="true" data-angleOffset="-125" data-angleArc="250" value="0">
          </div>
        </div>
      </div>
    </div>
    <div class="col-xs-12 col-sm-6 detail-info" id="filesystem">
      <div class="container-fluid">
        <div class="row">
          <div class="col-xs-12">
            <h4>{t}Filesystem{/t}</h4>
          </div>
        </div>
      </div>
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

  <div class="row row-detail">
    <div class="col-xs-12 detail-info" id="reporting">
      <div class="container-fluid">
        <div class="row">
          <div class="col-xs-12">
            <h4>{t}Reporting{/t}</h4>
          </div>
          <div class="col-xs-6 reporting">
            <div id="month_reporting"></div>
          </div>
          <div class="col-xs-6">
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <div class="row row-detail">
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
/* Init gauge */
size = parseInt($("#cpu").width() * 0.7);
$('.dial').attr('data-width', size).attr('data-height', size);
$('.dial').knob();

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

  $(document).on('centreon.host_detail', function(e) {
    /* Update blick general */
    var diffDate = moment().unix() - hostData.lastChange;
    $('.longoutput').html(hostData.output);
    $('#status').removeClass(function(index, css) {
      return (css.match(/(^|\s)label-\S+/g) || []).join(' ');
    });
    $('#status').addClass('label-' + hostData.status).text(hostData.status);
    $('#since_status').text(
      moment.duration(diffDate, 'seconds').humanize()
    );

    /* Update block network */
    if (hostData.network !== undefined) {
      var $in, $out, $networkList = $("#network ul");
      $networkList.children().remove();
      $.each(hostData.network, function(name, value) {
        $('<li></li>').addClass('title').text(name).appendTo($networkList);
        $in = $('<li></li>').addClass('info').appendTo($networkList);
        $in.html(
          '<div class="row">' +
          '<div class="col-xs-8">In</div>' +
          '<div class="col-xs-4"><span class="network-line" data-name="' + name + ' - In :" data-unit="' + value.unit + '">' + value.in.join(',') + '</span></div>' +
          '</div>'
        );
        $out = $('<li></li>').addClass('info').appendTo($networkList);
        $out.html(
          '<div class="row">' +
          '<div class="col-xs-8">Out</div>' +
          '<div class="col-xs-4"><span class="network-line" data-name="' + name + ' - Out :" data-unit="' + value.unit + '">' + value.out.join(',') + '</span></div>' +
          '</div>'
        );
      });
      $('.network-line').sparkline('html', {
        disableTooltips: true
      });
      $('.network-line').on('sparklineRegionChange', function(e) {
        var sparkline = e.sparklines[0],
            textInfo = $(e.currentTarget).data('name') + " " +
                       sparkline.getCurrentRegionFields().y + " " +
                       $(e.currentTarget).data('unit');
        $("#network .display-tooltip").text(textInfo);
      })
      .on('mouseleave', function(e) {
        $("#network .display-tooltip").text('');
      });
    }

    /* Update block system */
    if (hostData.system !== undefined) {
      if (hostData.system.memory !== undefined) {
        var percent = hostData.system.memory.current * 100 / hostData.system.memory.max;
        $('#gauge_memory').val(percent).trigger('change');
      }
      if (hostData.system.cpu !== undefined) {
        $('#gauge_cpu').val(hostData.system.cpu).trigger('change');
      }
      if (hostData.system.swap !== undefined) {
        var percent = hostData.system.swap.current * 100 / hostData.system.swap.max;
        $('#gauge_swap').val(percent).trigger('change');
      }
    }

    /* Update filesystems block */
    if (hostData.filesystem !== undefined) {
    $('#filesystem .fs').remove();
      $.each(hostData.filesystem, function(name, value) {
        $('<div></div>').addClass('col-xs-12').addClass('col-sm-6').addClass('fs').append(
          $('<div></div>').addClass('row').append(
            $('<div></div>').addClass('col-xs-4').text(name)
          ).append(
            $('<div></div>').addClass('col-xs-8').append(
              $('<div></div>').addClass('progress').append(
                $('<div></div>').addClass('progress-bar')
                  .attr('role', 'progressbar')
                  .attr('aria-valuenow', value.current)
                  .attr('aria-valuemin', value.min)
                  .attr('aria-valuemax', value.max)
                  .css('width', (value.current * 100 / value.max) + '%')
                  .text(value.current + ' / '  + value.max + ' ' + value.unit)
              )
            )
          )
        ).appendTo('#filesystem > .container-fluid > .row');
      });
    }
  });

  loadData();
});
</script>
{/block}
