
<h4>Services List</h4>

    <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
      <div class="panel panel-default">
        <div class="panel-heading" role="tab" id="heading{{id}}">
          <h4 class="panel-title">
            <a data-toggle="collapse" data-parent="#accordion" href="#collapse{{id}}" aria-expanded="true" aria-controls="collapse{{id}}">
              {{Name}}
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