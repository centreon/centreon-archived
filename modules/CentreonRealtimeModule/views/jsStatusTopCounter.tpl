$(document).on('centreon.refresh_status', function(e) {
  $('.top-counter .label').addClass('hide');
  $('.top-counter-service ul').html('');
  $('.top-counter-host ul').html('');
  if (statusData.service.critical != 0) {
    $('.top-counter-service .label-danger').removeClass('hide').text(statusData.service.critical);
    $('.top-counter-service ul').append(
      $('<li></li>').append(
        $('<a></a>').attr('href', "{url_for url='/realtime/service'}?search=status:2").text(
          statusData.service.critical + " unhandled critical problem"
        ).prepend(
          $('<i></i>').addClass('fa fa-times-circle info')
        )
      )
    );
  }
  if (statusData.service.warning != 0) {
    $('.top-counter-service .label-warning').removeClass('hide').text(statusData.service.warning);
    $('.top-counter-service ul').append(
      $('<li></li>').append(
        $('<a></a>').attr('href', "{url_for url='/realtime/service'}?search=status:1").text(
          statusData.service.warning + " unhandled warning alerts"
        ).prepend(
          $('<i></i>').addClass('fa fa-warning info')
        )
      )
    );
  }
  if (statusData.host.critical != 0) {
    $('.top-counter-host .label-danger').removeClass('hide').text(statusData.host.critical);
    $('.top-counter-host ul').append(
      $('<li></li>').append(
        $('<a></a>').attr('href', "{url_for url='/realtime/host'}?search=state:2").text(
          statusData.host.critical + " host down"
        ).prepend(
          $('<i></i>').addClass('fa fa-times-circle info')
        )
      )
    );
  }
  if (statusData.host.warning != 0) {
    $('.top-counter-host .label-warning').removeClass('hide').text(statusData.host.warning);
    $('.top-counter-host ul').append(
      $('<li></li>').append(
        $('<a></a>').attr('href', "{url_for url='/realtime/host'}?search=state:1").text(
          statusData.host.warning + " unhandled warning alerts"
        ).prepend(
          $('<i></i>').addClass('fa fa-warning info')
        )
      )
    );
  }
  if (statusData.poller.activity != 0 || statusData.poller.stopped != 0) {
    $('.top-counter-poller .label-danger').removeClass('hide').text(
      statusData.poller.activity + statusData.poller.stopped
    );
  }
  if (statusData.poller.latency != 0) {
    $('.top-counter-poller .label-warning').removeClass('hide').text(statusData.poller.latency);
  }
});

$(function() {
  $('.top-counter-poller').on('show.bs.dropdown', function(e) {
    $.ajax({
      url: "{url_for url='/realtime/poller/status'}",
      type: 'get',
      dataType: 'json',
      success: function(data, textStatus, jqXHR) {
        $('.top-counter-poller ul').html('');
        if (data.success) {
          $.each(data.values, function(idx, poller) {
            var $pollerLi = $('<li></li>'),
                $pollerLine = $('<div></div>').addClass('row'),
                $pollerStatus = $('<div></div>').addClass('col-xs-1'),
                $pollerLatency = $('<div></div>').addClass('col-xs-4');
            /* Add name */
            $('<div></div>').addClass('col-xs-7').text(poller.name).appendTo($pollerLine);
            /* Add status */
            if (poller.running == null || poller.running == 0) {
              $pollerStatus.addClass('mini danger').append(
                $('<i></i>').addClass('fa fa-power-off')
              );
            } else if (poller.disconnect == 1) {
              $pollerStatus.addClass('mini warning').append(
                $('<i></i>').addClass('fa fa-unlink')
              );
            } else {
              $pollerStatus.addClass('mini success').append(
                $('<i></i>').addClass('fa fa-check')
              );
            }
            $pollerStatus.appendTo($pollerLine);
            /* Add latency */
            /* @todo: color by level of latency */
            $pollerLatency.text(poller.latency);
            $pollerLatency.appendTo($pollerLine);
            
            $('<a></a>').append($pollerLine).appendTo($pollerLi);
            
            $('.top-counter-poller ul').append($pollerLi);
          });
        }
      }
    });
  });
});
