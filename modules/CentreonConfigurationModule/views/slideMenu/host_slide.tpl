<div class="headerWrapper">
    <div class="row">
        <div class="iconWrapper col-md-3">
            <i class="icon-host"></i>
        </div>
        <h4 class="col-md-7">{{hostConfig.Name}}</h4>
        <div class="iconWrapper col-md-2">
            <a href="{{edit_url}}" alt=""><i class="icon-edit"></i></a>
        </div>
    </div>
    <div class="row">
        <div class="stateWrapper col-md-3">
            <ul>
                <li>
                     <i class="icon-service success"></i>
                     <strong>{{servicesStatus.success}}</strong>
                </li>
                 <li>
                     <i class="icon-service warning"></i>
                     <strong>{{servicesStatus.warning}}</strong>
                </li>
                 <li>
                     <i class="icon-service danger"></i>
                     <strong>{{servicesStatus.danger}}</strong>
                </li>

            </ul>
        </div>
        <div class="detailsWrapper col-md-9">
            <dl class="list-group">
                  <dt class="">Command</dt>
                  <dd>{{hostConfig.Command}}</dd>

                  <dt class="">Time Period</dt>
                  <dd>{{hostConfig.Time period}}</dd>

                  <dt class="">Max check attempts</dt>
                  <dd>{{hostConfig.Max check attempts}}</dd>

                  <dt class="">Check interval</dt>
                  <dd>{{hostConfig.Check interval}}</dd>

                  <dt class="">Retry check interval</dt>
                  <dd>{{hostConfig.Retry check interval}}</dd>

                  <dt class="">Active checks enabled</dt>
                  <dd>{{hostConfig.Active checks enabled}}</dd>

                  <dt class="">Passive checks enabled</dt>
                  <dd>{{hostConfig.Passive checks enabled}}</dd>
            </dl>
        </div>
    </div>
</div>