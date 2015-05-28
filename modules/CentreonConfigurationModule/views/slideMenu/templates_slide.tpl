
<h4>List templates </h4>

{{#host_templates}}
<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
  <div class="panel panel-default">
    <div class="panel-heading" role="tab" id="heading{{id}}">
      <h4 class="panel-title">
        <a data-toggle="collapse" class="col-md-10" data-parent="#accordion" href="#collapse{{id}}" aria-expanded="true" aria-controls="collapse{{id}}">
          {{name}}
        </a>
        <a href="{{url_edit}}" alt="" class="col-md-2"><i class="icon-edit"></i></a>
      </h4>
    </div>

    <div id="collapse{{id}}" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
      <div class="panel-body">

        <h6>Defaut details</h6><hr>

            <table>
                <tr>
                    <td>Command</td>
                    <td>{{command}}</td>
                </tr>

                <tr>
                    <td>Time period</td>
                    <td>{{time_period}}</td>
                </tr>

                <tr>
                    <td>Max check attempts</td>
                    <td>{{max_check_attempts}}</td>
                </tr>

                <tr>
                    <td>Check interval</td>
                    <td>{{check_interval}}</td>
                </tr>

                <tr>
                    <td>Retry check interval</td>
                    <td>{{retry_check_interval}}</td>
                </tr>

                <tr>
                    <td>Active checks enabled</td>
                    <td>{{active_checks_enabled}}</td>
                </tr>

                 <tr>
                    <td>Passive checks enabled</td>
                    <td>{{passive_checks_enabled}}</td>
                </tr>
            </table>
    <h6>Services</h6>
    <hr>

    {{#servicesTemplate}}

    <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
      <div class="panel panel-default">
        <div class="panel-heading" role="tab" id="heading{{id}}">
          <h4 class="panel-title">
            <a data-toggle="collapse" data-parent="#accordion" href="#collapse{{id}}" aria-expanded="true" aria-controls="collapse{{id}}">
              {{name}}
            </a>
          </h4>
        </div>

        <div id="collapse{{id}}" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingOne">
          <div class="panel-body">

            <table>
                <tr>
                    <td>Command</td>
                    <td>{{command}}</td>
                </tr>

                <tr>
                    <td>Time period</td>
                    <td>{{time_period}}</td>
                </tr>

                <tr>
                    <td>Max check attempts</td>
                    <td>{{max_check_attempts}}</td>
                </tr>

                <tr>
                    <td>Check interval</td>
                    <td>{{check_interval}}</td>
                </tr>

                <tr>
                    <td>Retry check interval</td>
                    <td>{{retry_check_interval}}</td>
                </tr>

                <tr>
                    <td>Active checks enabled</td>
                    <td>{{active_checks_enabled}}</td>
                </tr>

                 <tr>
                    <td>Passive checks enabled</td>
                    <td>{{passive_checks_enabled}}</td>
                </tr>
            </table>
           </div>
         </div>
       </div>
       {{/servicesTemplate}}

     </div>
        </div>
    </div>
  </div>
  {{/host_templates}}
</div>

