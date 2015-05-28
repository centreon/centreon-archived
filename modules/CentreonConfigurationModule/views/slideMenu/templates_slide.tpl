
<h4>List templates </h4>
<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
  <div class="panel panel-default">
  {{#templates}}
    {{#hostTemplate}}
    <div class="panel-heading" role="tab" id="headingOne">
      <h4 class="panel-title">
        <a data-toggle="collapse" data-parent="#accordion" href="#collapse{{host_id}}" aria-expanded="true" aria-controls="collapseOne">
          {{host_name}}
        </a>
      </h4>
    </div>

    <div id="collapse{{host_id}}" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
      <div class="panel-body">
            <h6>Host activate</h6>
            {{host_activate}}
    {{/hostTemplate}}

    <h6>Services Templates</h6>
    <ul>
    {{#servicesTemplate}}
            <li>{{text}}</li>
    {{/servicesTemplate}}
    </ul>
        </div>
    </div>
    {{/templates}}
  </div>
</div>

