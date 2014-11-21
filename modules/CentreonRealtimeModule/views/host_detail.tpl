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
  
  <div class="row row-detail" id="application">
    <div class="col-xs-12">
     <h4>{t}Applications{/t}</h4>
    </div>
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
          <div class="col-xs-12 centreon_table">
            <table class="table table-bordered table-condensed">
              <thead>
                <tr>
                  <th>{t}Date{/t}</th>
                  <th>{t}Service{/t}</th>
                  <th>{t}Status{/t}</th>
                  <th>{t}Type{/t}</th>
                  <th>{t}Message{/t}</th>
                </tr>
              </thead>
              <tbody>
              </tbody>
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

function resizeCell(list) {
  var maxsize = 0;
  $.each(list, function(idx, el) {
    if ($(el).height() > maxsize) {
      maxsize = $(el).height();
    }
  });
  $.each(list, function(idx, el) {
    $(el).find('.row:first').css('min-height', maxsize + 'px');
  });
}

$(function() {
  var hostData,
      eData = new $.Event('centreon.host_detail'),
      hostReporting = new CalHeatMap();

  /* Init gauge */
  size = parseInt($("#cpu").width() * 0.7);
  $('.dial').attr('data-width', size).attr('data-height', size);
  $('.dial').knob();

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

    resizeCell(["#general", "#network"]);

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

    resizeCell(["#system", "#filesystem"]);

    /* Add applications */
    if (hostData.application !== undefined) {
      var applications = $('#application .app');
      $.each(hostData.application, function(appName, app) {
        var found = false,
            appId = 'app_' + appName.toLowerCase().replace(' ', '_');
        $.each(applications, function(idx, application) {
          if ($(application).attr('id') == appId) {
            found = idx;
          }
        });
        if (found === false) {
          /* Create application block */
          $('<div></div>')
             .addClass('col-xs-12 col-sm-4 detail-info app')
             .attr('id', appId)
             .append(
               $('<div><div>').addClass('container-fluid').append(
                 $('<div></div>').addClass('row').append(
                   $('<div></div>').addClass('col-xs-12').html(
                     '<h4>' + appName + '</h4>'
                   )
                 ).append(
                   $('<div></div>').addClass('col-xs-12 centreon_table').append(
                     $('<table></table>').addClass('table table-bordered table-condensed').append(
                       $('<thead></thead>').html(
                         '<tr>' +
                         '<td>Service</td>' +
                         '<td>Status</td>' +
                         '<td>Output</td>' +
                         '</tr>'
                       )
                     ).append(
                       $('<tbody></tbody>')
                     )
                   )
                 )
               )
             )
             .appendTo('#application');
        } else {
          applications.splice(found, 1);
        }
        /* Add service to listing */
        $tbody = $('#' + appId).find('tbody');
        $tbody.children().remove();
        $.each(app, function(idx, service) {
          $('<tr></tr>').append(
            $('<td></td>').text(service.name)
          ).append(
            $('<td></td>').append(
              $('<span></span>').addClass('label').addClass('label-' + service.status).text(service.status)
            )
          ).append(
            $('<td></td>').text(service.output)
          )
          .appendTo($tbody);
        });
      });
      /* Remove old application */
      $.each(applications, function(idx, application) {
        $(application).remove();
      });
    }

    /* Get eventlogs */
    $.ajax({
      url: "{url_for url='/realtime/eventlogs/lasthostevents/[i:id]/10' params=$routeParams}",
      method: 'get',
      dataType: 'json',
      success: function(data, textStatus, jqXHR) {
        $.each(data, function(idx, values) {
          var type, state;
          if (values.type == 0) {
            type = "SOFT";
          } else {
            type = "HARD";
          }
          switch (values.status) {
            case '0':
              state = 'Ok';
              break;
            case '1':
              state = 'Warning';
              break;
            case '2':
              state = 'Critical';
              break;
            case '3':
              state = 'Unknown';
              break;
            case '4':
              state = 'Pending';
              break;
            case '5':
              state = 'Information';
              break;
          } 
          $('<tr></tr>')
            .addClass('centreon-border-status-' + values.status)
            .append(
              $('<td></td>').text(values.datetime)
            )
            .append(
              $('<td></td>').html(values.service)
            )
            .append(
              $('<td></td>').addClass('centreon-status-' + values.status).text(state)
            )
            .append(
              $('<td></td>').text(type)
            )
            .append(
              $('<td></td>').html(values.output)
            )
            .appendTo('#eventlogs tbody')
        });
      }
    });
  });

  loadData();
});
</script>
{/block}
