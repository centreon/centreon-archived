<div class="headerWrapper">
    <div class="row">
        <div class="iconWrapper col-md-2">
            <i class="icon-host"></i>
        </div>
        <h4 class="col-md-8">{{hostConfig.Name}}</h4>
        <div class="buttonGroup col-md-2">
            <i class="icon-edit"></i>
        </div>
    </div>
    <div class="row">
        <div class="stateWrapper col-md-2">
            <ul>
                <li>
                     <i class="icon-service"></i>
                     <strong>{{servicesStatus.success}}</strong>
                </li>
                 <li>
                     <i class="icon-service"></i>
                     <strong>{{servicesStatus.warning}}</strong>
                </li>
                 <li>
                     <i class="icon-service"></i>
                     <strong>{{servicesStatus.danger}}</strong>
                </li>

            </ul>
        </div>
        <div class="buttonGroup col-md-10">
        </div>
    </div>
</div>