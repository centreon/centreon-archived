{extends file="file:[Core]viewLayout.tpl"}

{block name="title"}{t}Event Logs{/t}{/block}

{block name="content"}
<div class="content-container">
  <!-- Search block -->
  <div class="panel panel-default">
    <div class="panel-heading">
      <div class="row search">
        <form role="form" id="filters">
          <div class="form-group col-md-4">
            <input type="text" name="period" class="form-control" placeholder="{t}Period{/t}">
          </div>
          <div class="form-group col-md-4">
            <select name="status" multiple style="width: 100%">
              <option value="h_0">{t}Host Up{/t}</option>
              <option value="h_1">{t}Host Down{/t}</option>
              <option value="h_2">{t}Host Unreachable{/t}</option>
              <option value="h_4">{t}Host Pending{/t}</option>
              <option value="h_5">{t}Host Information{/t}</option>
              <option value="s_0">{t}Service OK{/t}</option>
              <option value="s_1">{t}Service Warning{/t}</option>
              <option value="s_2">{t}Service Critical{/t}</option>
              <option value="s_3">{t}Service Unknown{/t}</option>
              <option value="s_4">{t}Service Pending{/t}</option>
              <option value="s_5">{t}Service Information{/t}</option>
            </select>
          </div>
          <div class="form-group col-md-4">
            <select name="msg_type" multiple style="width: 100%">
              <option value="0">{t}Service alert{/t}</option>
              <option value="1">{t}Host alert{/t}</option>
              <option value="2">{t}Service notification{/t}</option>
              <option value="3">{t}Host notification{/t}</option>
              <option value="4">{t}Warning{/t}</option>
              <option value="5">{t}External command{/t}</option>
              <option value="6">{t}Current service state{/t}</option>
              <option value="7">{t}Current host state{/t}</option>
              <option value="8">{t}Initial service state{/t}</option>
              <option value="9">{t}Initial host state{/t}</option>
              <option value="10">{t}Service acknowledgement{/t}</option>
              <option value="11">{t}Host acknowledgement{/t}</option>
            </select>
          </div>
          <div class="form-group col-md-4">
            <!-- @TODO dynamic load -->
            <select name="instance_name" multiple style="width: 100%">
              <option>Central</option>
            </select>
          </div>
          <div class="form-group col-md-4">
            <select name="type" style="width: 100%">
              <option></option>
              <option value="0">{t}Soft{/t}</option>
              <option value="1">{t}Hard{/t}</option>
            </select>
          </div>
          <div class="form-group col-md-4">
            <input type="text" name="output" class="form-control" placeholder="{t}Filter message{/t}">
          </div>
        </form>
      </div>
    </div>
  </div><!-- End search block -->
  {* <div class="row facets" style="display: none">
  </div>
  <table class="table table-striped table-condensed table-bordered" id="eventlogs">
  <thead>
    <tr>
      <th class="span-1">{t}Date{/t}</th>
      <th class="span-2">{t}Host{/t}</th>
      <th class="span-2">{t}Service{/t}</th>
<!--  <th class="span-2">{t}Instance{/t}</th>-->
      <th class="span-1">{t}Status{/t}</th>
      <th class="span-1">{t}Type{/t}</th>
      <th class="span-5">{t}Message{/t}</th>
      <th class="badge-new-events" style="display: none;"><a href="#"><i class="fa fa-caret-up"></i> <span></span></a></th>
    </tr>
  </thead>
  <tbody>
  </tbody>
  </table> *}
  <div class="row">
    <div class="infinite-scroll col-xs-12 col-sm-9">
    </div>
    <div class="col-sm-3 hidden-xs">
    </div>
  </div>
</div>
{/block}

{block name="javascript-bottom" append}
<script>
$(function() {
  {literal}
  var eventlogTmpl = '<div class="row event">' +
    '<div class="col-xs-1 logo">' +
    '  {{{logo}}}' +
    '</div>' +
    '<div class="col-xs-11">' +
    '  <div class="row">' +
    '    <div class="col-xs-12">' +
    '     <span class="object-name">{{{object_name}}}</span>' +
    '     <span class="pull-right duration" data-duration="{{{datetime}}}"></span>' +
    '   </div>' +
    '    <div class="col-xs-12 description">' +
  	'      {{{description}}}' +
    '    </div>' +
    '    <div class="action">' + 
    '      <div class="row">' +
    '        <div class="pull-right">' +
    '         <a href="#" class="readmore">More</a>' +
    '        </div>' +
    '      </div>' +
    '    </div>' +
    '  </div>' +
    '</div>' +
  '</div>';
  {/literal}

  $('.infinite-scroll').on("loaded", function () {
    var now = moment();
    $('.duration[data-duration]').each(function(e, elem) {
      var datetime = moment.unix($(elem).data('duration'));
      $(elem).text(moment.duration(now - datetime).humanize());
    });
  });

  $('.infinite-scroll').centreonInfiniteScroll({
    ajaxUrlGetScroll: "{url_for url="/centreon-realtime/eventlogs"}",
    ajaxUrlGetNew: "{url_for url="/centreon-realtime/eventlogs/refresh"}",
    formFilter: "#filters",
    template: eventlogTmpl 
  });

  $("input[name='period']").daterangepicker({
    timePicker: true,
    timePickerIncrement: 5,
    timePicker12Hour: false,
    format: 'YYYY-MM-DD HH:mm'
  });
  $("input[name='period']").on( "apply.daterangepicker", function( e, $picker ) {
    $($picker.element[0]).trigger('change');
  });

  $("select[name='status']").select2({
    placeholder: "{t}Status{/t}"
  });

  $("select[name='msg_type']").select2({
    placeholder: "{t}Message type{/t}"
  });

  $("select[name='instance_name']").select2({
    placeholder: "{t}Instance{/t}"
  });

  $("select[name='type']").select2({
    placeholder: "{t}State type{/t}",
    allowClear: true
  });
});
</script>
{/block}
