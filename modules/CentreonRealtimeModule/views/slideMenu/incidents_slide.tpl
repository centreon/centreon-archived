<h4>Indirect issues</h4>
{{^indirect_issues}}
<p>No issues</p>
{{/indirect_issues}}
<hr>

<h4>Direct issues</h4>
{{#direct_issues}}
    <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
      <div class="panel panel-default">
        <div class="panel-heading" role="tab" id="heading{{id}}">
          <h4 class="panel-title">
            <a data-toggle="collapse" data-parent="#accordion" href="#collapse{{issue_id}}" aria-expanded="true" aria-controls="collapse{{id}}">
              {{description}}
            </a>
          </h4>
        </div>

        <div id="collapse{{issue_id}}" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingOne">
          <div class="panel-body">

            <table>
                <tr>
                    <td>Start Time</td>
                    <td>{{start_time}}</td>
                </tr>

                <tr>
                    <td>End Time</td>
                    <td>{{end_time}}</td>
                </tr>

                <tr>
                    <td>acknowledgement</td>
                    <td>{{ack_time}}</td>
                </tr>

                <tr>
                    <td>Last update</td>
                    <td>{{last_update}}</td>
                </tr>

                <tr>
                    <td>Downtime</td>
                    <td>{{in_downtime}}</td>
                </tr>

            </table>
        </div>
      </div>
    </div>
{{/direct_issues}}
