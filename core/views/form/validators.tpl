{block name="javascript" append}
<script>
var rules = {};

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
      
    if (typeof {$fieldName} !== "undefined") {
      rules[$({$fieldName}).attr('id')] = elRules;
    }
    elRules = {};
  {/if}
{/foreach}
{if $eventValidation['extraJs']}
  {$eventValidation['extraJs']}
{/if}
    
formValidRule["{$formName}"] = rules;
</script>
{/block}
