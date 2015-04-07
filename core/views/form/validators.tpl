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

var elRules = {};
{foreach $eventValidation['validators'] as $fieldName => $fieldValidators}
  {foreach $fieldValidators as $type => $options}
    {if $type == 'remote'}
      elRules['remote'] = {
        url: "{url_for url=$options['action']}",
        type: 'type',
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
        }
      };
    {elseif $type == 'size'}
      elRules['size'] = {
        {if $options['minlength']}
        minlength: {$options['minlength']},
        {/if}
        {if $options['maxlength']}
        minlength: {$options['maxlength']},
        {/if}
      };
    {elseif $type == 'forbiddenChar'}
      {* TODO *}
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
  rules[{$fieldName}] = elRules;
  {/if}
{/foreach}
{if $eventValidation['formId']}
$('#{$eventValidation['formId']}').validate({
  rules: rules
});
{/if}
{if $eventValidation['extraJs']}
{$eventValidation['extraJs']}
{/if}
</script>
{/block}
