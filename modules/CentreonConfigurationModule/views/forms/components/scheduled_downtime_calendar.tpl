<input type="hidden" name="{$element['name']}" id="{$element['id']}">
<div class="row scheduled-downtime" data-input-id="{$element['id']}">
  <!-- List of periods and add periods -->
  <div class="col-xs-12 col-sm-3 periods">
    <div>
      <a href="#" class="addPeriodBtn"><i class="fa fa-plus-circle"></i> {t}Add a period{/t}</a>
    </div>
    <ul class="list-unstyled list">
    </ul>
  </div>
  <!-- Calendar for select days and time -->
  <div class="col-xs-12 col-sm-9">
    <div class="row">
      <div class="col-xs-12 calendar">
        <div class="week-days">
          <div data-wday="monday">{t}Monday{/t}</div>
          <div data-wday="tuesday">{t}Tuesday{/t}</div>
          <div data-wday="wednesday">{t}Wednesday{/t}</div>
          <div data-wday="thursday">{t}Thursday{/t}</div>
          <div data-wday="friday">{t}Friday{/t}</div>
          <div data-wday="saturday">{t}Saturday{/t}</div>
          <div data-wday="sunday">{t}Sunday{/t}</div>
        </div>
        <div class="days">
          {section name="day" start=1 loop=32 step=1}
          {assign var="dayName" value=$weeklyDays[$smarty.section.day.index % 7]}
          <div data-wday="{$dayName}" data-day="{$smarty.section.day.index}">
            <span>{$smarty.section.day.index}</span>
            <div class="period-spot">
            </div>
          </div>
          {/section}
        </div>
      </div>
      <!-- Period definition -->
      <div class="col-xs-12 period-info" style="display: none;">
        <div class="row">
          <div class="col-xs-12"><cite class="description"></cite></div>
          <!-- Period type -->
          <div class="col-xs-12">
            <div class="form-group">
              <label class="label-controller floatLabel" for="period_type">Period type</label>
              <select name="period_type" id="period_type" class="form-control">
                <option value="weekly">Weekly</option>
                <option value="monthly">Monthly</option>
                <option value="custom">Custom</option>
              </select>
            </div>
          </div>
          <!-- Time start -->
          <div class="col-xs-6">
            <div class="form-group">
              <label class="label-controller floatLabel" for="time_start">Time start</label>
              <input type="text" name="time_start" id="time_start" placeholder="Time start">
            </div>
          </div>
          <!-- Time end -->
          <div class="col-xs-6">
            <div class="form-group">
              <label class="label-controller floatLabel" for="time_end">Time end</label>
              <input type="text" name="time_end" id="time_end" placeholder="Time end">
            </div>
          </div>
          <!-- Fixed/Flexible -->
          <div class="col-xs-6">
            <div class="form-group">
              <label class="form-group">Type</label>
              <div class="choiceGroup">
                <label class="label-controller">
                  <input type="radio" name="fixed" value="fixed" checked> Fixed
                </label>
                <label class="label-controller">
                  <input type="radio" name="fixed" value="flexibled"> Flexibled
                </label>
              </div>
            </div>
          </div>
          <!-- Duration if fixed -->
          <div class="col-xs-6">
            <div class="form-group">
              <label class="label-controller floatLabel" for="duration">Duration</label>
              <input type="text" name="duration" id="duration" data-parentfield="fixed" data-parentvalue="flexibled" placeholder="Duration">
            </div>
          </div>
          <!-- Button for validate -->
          <div class="col-xs-12">
            <button class="btnC btnDefault cancelPeriodBtn">Cancel</button> <button class="btnC btnSuccess validPeriodBtn">Valid</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
