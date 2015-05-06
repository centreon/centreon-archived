{block name="javascript" append}
<script>
var rules = {};

$.validator.setDefaults({
  ignore: [],
  highlight: function(element) {
    $(element).closest('.form-group').addClass('has-error');
  },
  unhighlight: function(element) {
    $(element).closest('.form-group').removeClass('has-error');
  },
  errorElement: 'span',
  errorClass: 'help-block',
  errorPlacement: function(error, element) {
    if(element.parent('.input-group').length) {
      error.insertAfter(element.parent());
    } else {
      error.insertAfter(element);
    }
  },
  invalidHandler: function (event, validator) {
    var list = $("#{$eventValidation['formId']}_errors");
    list.children().remove();
    $.each(validator.errorList, function(idx, error) {
      var label = $(error.element).closest('.form-group').find('label').text();
      $('<li></li>').text(label + " : " + error.message).appendTo(list);
    });
    list.closest('.flash').addClass('alert-danger').show();
  }
});

$.validator.addMethod(
  'forbiddenChar',
  function (value, element, characters) {
    var listCharacters = characters.split('');
    for (var item in listCharacters) {
      if (value.indexOf(listCharacters[item]) > -1) {
        return false;
      }
    }
    return true;
  },
  'Please enter valid characters.'
);

var elRules = {};
{foreach from=$eventValidation['validators'] key=fieldName item=fieldValidators}
  {foreach from=$fieldValidators key=type item=options}
    {if $type == 'remote'}
      elRules['remote'] = {
        url: "{url_for url=$options['action']}",
        type: 'post',
        data: {
          'module': function () {
            return $("[name='module']").val();
          },
          'object': function () {
            return $("[name='object']").val();
          },
          'object_id': function () {
            return $("[name='object_id']").val();
          },
          'clientside': true,
        }
      };
    {elseif $type == 'size'}
      {if $options['minlength']}
        elRules['minlength'] = "{$options['minlength']}";
      {/if}
      {if $options['maxlength']}
        elRules['maxlength'] = "{$options['maxlength']}";
      {/if}
    {elseif $type == 'forbiddenChar'}
      {if $options['characters']}
        elRules['forbiddenChar'] = "{$options['characters']}";
      {/if}
    {elseif $type == 'equalTo'}
      {if $options['equalfield']}
        elRules['equalTo'] = "#{$options['equalfield']}";
      {/if}
    {elseif $type == 'ipaddress'}
      {* TODO *}
    {elseif $type == 'depends'}
      elRules['required'] = {
        depends: function (element) {
          return $("#{$options['parent_id']}").val() != '{$options['parent_value']}';
        }
      };
    {/if}
  {/foreach}
  {if count($fieldValidators) > 0}
    rules[$({$fieldName}).attr('id')] = elRules;
    elRules = {};
  {/if}
{/foreach}
{if $eventValidation['formId']}
  $('#{$eventValidation['formId']}').validate({
    ignore: '*:not([name])',
    rules: rules
  });
{/if}
{if $eventValidation['extraJs']}
  {$eventValidation['extraJs']}
{/if}
</script>
{/block}
