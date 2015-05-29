<h5>Tags</h5><hr>
{{#tags}}
<h6>Global Tags</h6>
<ul>
 {{#globals}}
 <li class="tagGlobal">{{.}}</li>
 {{/globals}}
 </ul>
<hr>
<h6>Inherited Tags</h6>
<ul>
{{#herited}}
<li class="tagGlobalNotDelete">{{.}}</li>
{{/herited}}
</ul>
{{/tags}}