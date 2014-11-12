$(document).on('centreon.refresh_status', function(e) {
  $('.top-counter .label').addClass('hide');
  if (statusData.service.warning != 0) {
    $('.top-counter-service .label-warning').removeClass('hide').text(statusData.service.warning);
  }
  if (statusData.service.critical != 0) {
    $('.top-counter-service .label-danger').removeClass('hide').text(statusData.service.critical);
  }
  if (statusData.host.warning != 0) {
    $('.top-counter-host .label-warning').removeClass('hide').text(statusData.host.warning);
  }
  if (statusData.host.critical != 0) {
    $('.top-counter-host .label-danger').removeClass('hide').text(statusData.host.critical);
  }
});
