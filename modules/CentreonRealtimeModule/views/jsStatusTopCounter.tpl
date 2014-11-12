$(document).on('centreon.refresh_status', function(e) {
  $('.top-counter .label').addClass('hide');
  $('.top-counter ul').html('');
  if (statusData.service.critical != 0) {
    $('.top-counter-service .label-danger').removeClass('hide').text(statusData.service.critical);
    $('.top-counter-service ul').append(
      $('<li></li>').append(
        $('<a></a>').attr('href', "{url_for url='/realtime/service'}?search=start%3D2").text(
          statusData.service.critical + " unhandled critical problem"
        )
      )
    );
  }
  if (statusData.service.warning != 0) {
    $('.top-counter-service .label-warning').removeClass('hide').text(statusData.service.warning);
    $('.top-counter-service ul').append(
      $('<li></li>').append(
        $('<a></a>').attr('href', "{url_for url='/realtime/service'}?search=start%3D1").text(
          statusData.service.warning + " unhandled warning alerts"
        )
      )
    );
  }
  if (statusData.host.critical != 0) {
    $('.top-counter-host .label-danger').removeClass('hide').text(statusData.host.critical);
    $('.top-counter-service ul').append(
      $('<li></li>').append(
        $('<a></a>').attr('href', "{url_for url='/realtime/host'}?search=start%3D2").text(
          statusData.host.critical + " unhandled critical problem"
        )
      )
    );
  }
  if (statusData.host.warning != 0) {
    $('.top-counter-host .label-warning').removeClass('hide').text(statusData.host.warning);
    $('.top-counter-service ul').append(
      $('<li></li>').append(
        $('<a></a>').attr('href', "{url_for url='/realtime/host'}?search=start%3D1").text(
          statusData.host.warning + " unhandled warning alerts"
        )
      )
    );
  }
});
