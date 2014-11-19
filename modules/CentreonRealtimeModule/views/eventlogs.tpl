{extends file="file:[Core]viewLayout.tpl"}

{block name="title"}{t}Event Logs{/t}{/block}

{block name="content"}
<div class="content-container">
  <div class="panel panel-default">
    <div class="panel-heading">
      <div class="row search">
        <form role="form" id="filters">
          <div class="form-group col-md-4">
            <input type="text" name="period" class="form-control" placeholder="{t}Period{/t}">
          </div>
          <div class="form-group col-md-4">
            <select name="status" multiple style="width: 100%">
              <option value="0">{t}OK / Up{/t}</option>
              <option value="1">{t}Warning / Down{/t}</option>
              <option value="2">{t}Critical / Unreachable{/t}</option>
              <option value="3">{t}Unknown{/t}</option>
              <option value="4">{t}Pending{/t}</option>
              <option value="5">{t}Information{/t}</option>
            </select>
          </div>
          <div class="form-group col-md-4">
            <select name="msg_type" multiple style="width: 100%">
              <option value="0">{t}Service alert{/t}</option>
              <option value="1">{t}Host Alert{/t}</option>
              <option value="2">{t}Service Notification{/t}</option>
              <option value="3">{t}Host Notification{/t}</option>
              <option value="4">{t}Warning{/t}</option>
              <option value="5">{t}External command{/t}</option>
              <option value="6">{t}Current service state{/t}</option>
              <option value="7">{t}Current host state{/t}</option>
              <option value="8">{t}Initial service state{/t}</option>
              <option value="9">{t}Initial host state{/t}</option>
              <option value="10">{t}Aclknownledge service{/t}</option>
              <option value="11">{t}Aclknownledge host{/t}</option>
            </select>
          </div>
          <div class="form-group col-md-4">
            <!-- @TODO dynamic load -->
            <select name="instance_name" multiple style="width: 100%">
              <option>Central</option>
            </select>
          </div>
          <div class="form-group col-md-8">
            <input type="text" name="output" class="form-control" placeholder="{t}Filter message{/t}">
          </div>
        </form>
      </div>
    </div>
  </div>
  <div class="row facets" style="display: none">
  </div>
  <table class="table table-striped table-condensed table-bordered" id="eventlogs">
  <thead>
    <tr>
      <th class="span-1">{t}Date{/t}</th>
      <th class="span-2">{t}Host{/t}</th>
      <th class="span-2">{t}Service{/t}</th>
<!--  <th class="span-2">{t}Instance{/t}</th>-->
      <th class="span-1">{t}Status{/t}</th>
      <th class="span-6">{t}Message{/t}</th>
      <th class="badge-new-events" style="display: none;"><a href="#"><i class="fa fa-caret-up"></i> <span></span></a></th>
    </tr>
  </thead>
  <tbody>
  </tbody>
  </table>
</div>
{/block}

{block name="javascript-bottom" append}
<script>
$(function() {
  $('#eventlogs').centreonTableInfiniteScroll({
    ajaxUrlGetScroll: "{url_for url="/realtime/eventlogs"}",
    ajaxUrlGetNew: "{url_for url="/realtime/eventlogs/refresh"}",
    formFilter: "#filters",{literal}
    templateRows: "<tr class='{{{border_color}}}'> \
      <td class='span-1'>{{{datetime}}}</td> \
      <td class='span-2'>{{{host_logo}}} {{{host}}}</td> \
      <td class='span-2'>{{{service_logo}}} {{{service}}}</td> \
<!--  <td class='span-2'>{{{instance}}}</td>--> \
      <td class='span-1 centreon-status-{{{status}}}' style='text-align:center;'>{{{status_text}}}</td> \
      <td class='span-6'>{{{output}}}</td> \
    </tr>"{/literal}
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
});
</script>
{/block}
