<div class="modal-header">
<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
<h4>{t}Apply configuration{/t}</h4>
</div>
<div class="flash alert fade in" id="modal-flash-message" style="display: none;">
  <button type="button" class="close" aria-hidden="true">&times;</button>
</div>
<div class="wizard" id="applycfg">
  <ul class="steps">
    <li data-target="#applycfg_1" class="active"><span class="badge badge-info">1</span>{t}Generate files{/t}<span class="chevron"></span></li>
    <li data-target="#applycfg_2"><span class="badge badge-info">2</span>{t}Move files{/t}<span class="chevron"></span></li>
    <li data-target="#applycfg_3"><span class="badge badge-info">3</span>{t}Restart{/t}<span class="chevron"></span></li>
  </ul>
</div>
<div class="row-divider"></div>
<form role="form" class="form-horizontal" id="wizard_form">
  <div class="step-content">
    <div class="step-pane active" id="applycfg_1">
      <p>
        {t}Configuration files will be generated and put in a temporary directory.{/t}
        {t}Validity checks will be made against the generated files.{/t}
      </p>
      <button id="btn-generate" class="btn btn-success btn-lg">{t}Generate{/t}</button>
      <pre id="console-generate" class="hide margin-top-10">
      </pre>
    </div>
    <div class="step-pane" id="applycfg_2">
      <p>
        {t}Generated files will be moved to the final configuration directory.{/t}
      </p>
      <button id="btn-move" class="btn btn-success btn-lg">{t}Move{/t}</button>
      <pre id="console-move" class="hide margin-top-10">
      </pre>
    </div>
    <div class="step-pane" id="applycfg_3">
      <p>
        {t}The monitoring engine will be restarted.{/t} 
        {t}Changes made in the configuration files will be taken into account.{/t}
      </p>
      <button id="btn-restart" class="btn btn-success btn-lg">{t}Restart{/t}</button>
      <pre id="console-restart" class="hide margin-top-10">
      </pre>
    </div>
  </div>
  <div class="modal-footer">
    <button class="btn btn-default btn-prev" disabled>{t}Prev{/t}</button>
    <button class="btn btn-default btn-next" data-last="{t}Finish{/t}" id="wizard_submit">{t}Next{/t}</button>
  </div>
</form>
<script>
$(function() {
    $("input[type=checkbox][class=allBox]").each(function() {
      console.log($(this).attr('id'));
    });

    /**
     * @todo change jquery selector for something more accurate
     */

    /* File generation */
    $("#btn-generate").click(function() {
      var $csl = $("#console-generate");
      var $thisBtn = $(this);

      $thisBtn.attr('disabled', 'disabled');
      $csl.removeClass('hide');
      $csl.html("");

      $("input[type=checkbox][class=allBox]:checked").each(function() {
        var pollerId = $(this).val();

        $.ajax({
          url: '/api/configuration/1/generatecfg/' + pollerId,
          dataType: 'json'
        }).success(function(data, textStatus, jqXHR) {
          $csl.append(data.output);
          if (!data.status) {
            $thisBtn.removeAttr('disabled');
          } else {
            /* File testing */
            $.ajax({
              url: '/api/configuration/1/testcfg/' + pollerId,
              dataType: 'json'
            }).success(function(data2, textStatus2, jqXHR2) {
              $csl.append(data2);
              $thisBtn.removeAttr('disabled');
            }).error(function(jqXHR2, textStatus2, errorThrown2) {
              $csl.append(errorThrown2);
              $thisBtn.removeAttr('disabled');
            });
          }
        }).error(function(jqXHR, textStatus, errorThrown) {
          $csl.append(errorThrown);
          $thisBtn.removeAttr('disabled');
        });
      });
    });

    /* File copying */
    $("#btn-move").click(function() {
      var $csl = $("#console-move");
      var $thisBtn = $(this);

      $thisBtn.attr('disabled', 'disabled');
      $csl.removeClass('hide');
      $csl.html("");

      $("input[type=checkbox][class=allBox]:checked").each(function() {
        var pollerId = $(this).val();

        $.ajax({
          url: '/api/configuration/1/movecfg/' + pollerId,
          dataType: 'json'
        }).success(function(data, textStatus, jqXHR) {
          if (data.data.status) {
            $csl.append('Successfully copied files.');
          } else {
            $csl.append(data.data.stdout);
          }
          $thisBtn.removeAttr('disabled');
        }).error(function(jqXHR, textStatus, errorThrown) {
          $csl.append(errorThrown);
          $thisBtn.removeAttr('disabled');
        });
      });
    });

    /* Restart engine */
    $("#btn-restart").click(function() {
      var $csl = $("#console-restart");
      var $thisBtn = $(this);

      $thisBtn.attr('disabled', 'disabled');
      $csl.removeClass('hide');
      $csl.html("");

      $("input[type=checkbox][class=allBox]:checked").each(function() {
        var pollerId = $(this).val();

        $.ajax({
          url: '/api/configuration/1/restartcfg/1',
          dataType: 'json'
        }).success(function(data, textStatus, jqXHR) {
          $csl.html(data.data.command_line);
          $thisBtn.removeAttr('disabled');
        }).error(function(jqXHR, textStatus, errorThrown) {
          $csl.html(errorThrown);
          $thisBtn.removeAttr('disabled');
        }); 
      });
    });
});
</script>
