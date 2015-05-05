{extends file="file:[Core]viewLayout.tpl"}

{block name="title"}{t}Event Logs{/t}{/block}

{block name="content"}
<div class="content-container">
  <!-- Search block -->
  <form id="filters">
  {include file='tools/datatable-search.tpl'}
  </form>
  <!-- End search block -->
  <div class="row">
    <div class="infinite-scroll col-sm-1">
    </div>
    <div class="event-detail col-sm-1" style="display: none;">
      <div class="row">
        <div class="col-sm-10 object-name">
        </div>
        <div class="pull-right status">
         <div class="label"></div>
        </div>
        <div class="col-sm-12 date">
        </div>
        <div class="col-sm-12 description">
        </div>
        <div class="col-sm-12">
          <div class="graph"></div>
        </div>
      </div>
    </div>
  </div>
</div>
{/block}

{block name="javascript-bottom" append}
<script>
$(function() {
  {literal}
  var eventlogTmpl = '<div class="row event status-{{{status}}}" data-statustext="{{{status_text}}}" data-statusclass="{{{status_css}}}" data-serviceid="{{{service_id}}}">' +
    '<div class="col-xs-1 logo">' +
    '  {{{logo}}}' +
    '</div>' +
    '<div class="col-xs-11">' +
    '  <div class="row">' +
    '    <div class="col-xs-12">' +
    '     <span class="object-name">{{{object_name}}}</span>' +
    '     <span class="pull-right duration" data-duration="{{{datetime}}}"></span>' +
    '   </div>' +
    '    <div class="col-xs-12 description">' +
  	'      {{{description}}}' +
    '    </div>' +
    '    <div class="action">' + 
    '      <div class="row">' +
    '        <div class="pull-right">' +
    //'         <a href="#" class="readmore">More</a>' +
    '        </div>' +
    '      </div>' +
    '    </div>' +
    '  </div>' +
    '</div>' +
  '</div>';
  {/literal}

  $('.infinite-scroll').on("loaded", function () {
    var now = moment();
    $('.duration[data-duration]').each(function(e, elem) {
      var datetime = moment.unix($(elem).data('duration'));
      $(elem).text(moment.duration(now - datetime).humanize());
    });
  });

  $('.infinite-scroll').centreonInfiniteScroll({
    ajaxUrlGetScroll: "{url_for url="/centreon-realtime/eventlogs"}",
    ajaxUrlGetNew: "{url_for url="/centreon-realtime/eventlogs/refresh"}",
    formFilter: "#filters",
    template: eventlogTmpl 
  });

  $("input[name='period']").daterangepicker({
    timePicker: true,
    timePickerIncrement: 5,
    timePicker12Hour: false,
    format: 'YYYY-MM-DD HH:mm'
  });
  $("input[name='period']").on( "apply.daterangepicker", function( e, $picker ) {
    $($picker.element[0]).trigger('change');
  });

  //jsUrl.graph = "{url_for url='/centreon-performance/graph'}";
  $(".infinite-scroll").on("click", ".event", function () {
    if ($(this).hasClass("active")) {
      $(this).removeClass("active");
      $(".infinite-scroll").removeClass("active");
      $(".event-detail").removeClass("active");
      $(".event-detail").one("transitionend", function () {
        $(".event-detail").hide();
      });
    } else {
      $(".infinite-scroll").find(".event").removeClass("active");
      $(this).addClass("active");
      $(".infinite-scroll").addClass("active");
      $(".event-detail").show();
      $(".event-detail").addClass("active");
      $(".event-detail .object-name").text($(this).find(".object-name").text());
      var time = $(this).find('.duration').data('duration');
      $(".event-detail .date").text(moment.unix(time).calendar());
      $(".event-detail .description").text($(this).find(".description").text());
      var statusClass = $(this).data("statusclass");
      $(".event-detail .status > .label").text(
        $(this).data("statustext")
      ).removeClass().addClass("label " + statusClass);
      /* Load graph if function exists */
      /*if (typeof addChart === "function") {
        var serviceId = $(this).data("serviceid");
        if (serviceId !== undefined) {
          addChart("graph", serviceId, time - 1800, time + 1800);
        }
      }*/
    }
  });

  function runSearch() {
      $("input[name='advsearch']").centreonsearch("fillAssociateFields");
      $(".infinite-scroll").children().remove();
      $(".infinite-scroll").centreonInfiniteScroll("loadData");
  }

  /* Initialize search */
  //var reload = true;
  $("input.centreon-search").on("blur keyup", function(e) {
    if (e.type === 'blur' || e.keyCode == 13) {
      runSearch();
    } else {
      /* Fill the advanced search */
      var advString = $("input[name='advsearch']").val();
      var searchTag = $(this).data("searchtag");
      var tagRegex = new RegExp( "(^| )" + searchTag + ":((?![\"'])\\S+|\".*\"|'.*')", "g" );
      var splitRegex = new RegExp( "([^\\s\"']+|\"([^\"]*)\"|'([^']*)')", "g" );

      /* Remove the existing values */
      advString = advString.replace( tagRegex, "").trim();
      while (match = splitRegex.exec($(this).val())) {
        advString += " " + searchTag + ":" + match[1];
      }
      $("input[name='advsearch']").val(advString.trim());
    }
  });

  $('select.centreon-search').on('change', function(e) {
    /* Fill the advanced search */
    var advString = $( "input[name='advsearch']" ).val();
    var searchTag = $( this ).data( "searchtag" );
    var tagRegex = new RegExp( "(^| )" + searchTag + ":((?![\"'])\\S+|\".*\"|'.*')", "g" );
    var splitRegex = new RegExp( "([^\\s\"']+|\"([^\"]*)\"|'([^']*)')", "g" );

    /* Remove the existing values */
    advString = advString.replace( tagRegex, "").trim();
    while ( match = splitRegex.exec( $( this ).find("option:selected").text()) ) {
        advString += " " + searchTag + ":" + match[1];
    }
    $( "input[name='advsearch']" ).val( advString.trim() );

    e.preventDefault();
    runSearch();
  });

  $("#btnSearch").on("click", function (e) {
    e.preventDefault();
    runSearch();
  });

  $("#filters").on("submit", function(e) {
    e.preventDefault();
    e.stopPropagation();
    runSearch();
  });

  $("input[name='advsearch']").centreonsearch({
    minChars: 2,
    fnRunSearch: function (obj) {
      runSearch();
    },
    tags: {
      "host": "input[name='host']",
      "service": "input[name='service']",
      "status": "select[name='status']",
      "eventtype": "select[name='eventtype']",
      "output": "input[name='output']"
    },
    associateFields: {
      "host": "input[name='host']",
      "service": "input[name='service']",
      "status": "select[name='status']",
      "eventtype": "select[name='eventtype']",
      "output": "input[name='output']"
    }
  });
});
</script>
{/block}
