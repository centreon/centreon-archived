{extends file="file:[Core]viewLayout.tpl"}
{block name="title"}
       {t}Service Details{/t}
{/block}

{block name="content"}
<br>
<table class='table table-hover table-bordered' style='vertical-align: middle; margin: 20px;padding:15px;width:50%'>
<tbody>
<tr>
<td style="width: 1px; background-color:#194B7F; margin: 0px; padding: 0px; min-width: 10px; max-width: 15px;"></td>
<td style="width: 70px; text-align: center; vertical-align: middle;"><i class="fa fa-hdd-o fa-3"></i></td>
<td style="vertical-align: middle;"><span style="font-size: 20px;"><a href='#'>Nom de l'Host</a></span>
<br><a href="#">127.0.0.1 / localhost</a></td>
<td></td>
</tr>
</tbody>        
</table>

<div style='margin:20px;'>
<ul class="nav nav-tabs">
  <li class="active"><a href="#availability" data-toggle="tab">General Information</a></li>
  <li><a href="#performance" data-toggle="tab">Ticket History</a></li>
  <li><a href="#performance" data-toggle="tab">Performance</a></li>
  <li><a href="#logs" data-toggle="tab">Logs</a></li>
  <li><a href="#traps" data-toggle="tab">Traps</a></li>  <li><a href="#traps" data-toggle="tab">Traps</a></li>
</ul>
</div>


{/block}
