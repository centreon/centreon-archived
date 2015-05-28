
<h4>List templates </h4>
<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
  <div class="panel panel-default">
    <div class="panel-heading" role="tab" id="headingOne">
      <h4 class="panel-title">
        <a data-toggle="collapse" data-parent="#accordion" href="#collapse{{host_id}}" aria-expanded="true" aria-controls="collapseOne">
          {{Command}}
        </a>
      </h4>
    </div>

    <div id="collapse{{host_id}}" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
      <div class="panel-body">

            <table>
                <tr>
                    <td>Command</td>
                    <td>{{Command}}</td>
                </tr>

                <tr>
                    <td>Time period</td>
                    <td>{{Time period}}</td>
                </tr>

                <tr>
                    <td>Max check attempts</td>
                    <td>{{Max check attempts}}</td>
                </tr>

                <tr>
                    <td>Check interval</td>
                    <td>{{Check interval}}</td>
                </tr>

                <tr>
                    <td>Retry check interval</td>
                    <td>{{Retry check interval}}</td>
                </tr>

                <tr>
                    <td>Active checks enabled</td>
                    <td>{{Active checks enabled}}</td>
                </tr>

                 <tr>
                    <td>Passive checks enabled</td>
                    <td>{{Passive checks enabled}}</td>
                </tr>
            </table>

    <h6>Services Templates</h6>
    <ul>
    {{#servicesTemplate}}
            <li>{{text}}</li>
    {{/servicesTemplate}}
    </ul>
        </div>
    </div>
  </div>
</div>

