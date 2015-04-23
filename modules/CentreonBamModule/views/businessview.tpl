{extends file="file:[Core]viewLayout.tpl"}

{block name=title}Business Unit{/block}

{block name=content}
<div class="container">
      <!--<h4>Business Units</h4>-->
        <div class="grid-stack bam-view">
            <div id="ba1" class="grid-stack-item" data-gs-x="0" data-gs-y="0" data-gs-width="3" data-gs-height="4">
              <div class="bg-success grid-stack-item-content">
                  <img src="/static/centreon-bam/img/compas.svg" alt="Compas"/>
                <h3>Sales</h3>
                <p>Availability : 100%</p>
              </div>
            </div>

            <div id="ba2" class="grid-stack-item" data-gs-x="3" data-gs-y="0" data-gs-width="3" data-gs-height="4">
              <div class="bg-danger grid-stack-item-content">
                  <img src="/static/centreon-bam/img/loop.svg" alt="Infinity-Loop"/>
                  <h3>R&amp;D</h3>
                  <p>Availability : 40%<br/>Forge is down</p>
              </div>
            </div>

            <div id="ba3" class="grid-stack-item" data-gs-x="6" data-gs-y="0" data-gs-width="3" data-gs-height="4">
              <div class="bg-success grid-stack-item-content">
              	<img src="/static/centreon-bam/img/chat.svg" alt="Chat"/>
                <h3>Service Desk</h3>
                <p>Availability : 100%</p>
              </div>
            </div>
    </div>
</div>
{/block}

{block name="javascript-bottom" append}
{/block}
