{$field}
<script>
$(function() {
  {get_custom_js}
});
</script>

{block name="javascript" append}
<script>

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

{if isset($typeField) && $typeField == 'number'}
    $('#'+$({$fieldName}).attr('id')).rules('add', {
      number: true
    });
{/if}
{foreach from=$eventValidation['validators'] key=fieldName item=fieldValidators}
  {foreach from=$fieldValidators key=type item=options}
    var cle, valeur;
    
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
          $('#'+$({$fieldName}).attr('id')).rules('add', {
            minlength: "{$options['minlength']}"
          });
      {/if}
      {if $options['maxlength']}
        $('#'+$({$fieldName}).attr('id')).rules('add', {
            maxlength: "{$options['maxlength']}"
          });
      {/if}
    {elseif $type == 'forbiddenChar'}
      {if $options['characters']}
        $('#'+$({$fieldName}).attr('id')).rules('add', {
            forbiddenChar: "{$options['characters']}"
        });
      {/if}
    {elseif $type == 'equalTo'}
      {if $options['equalfield']}
        $('#'+$({$fieldName}).attr('id')).rules('add', {
          equalTo: "{$options['equalfield']}"
        });
      {/if}
    {elseif $type == 'illegalChars'}
        $('#'+$({$fieldName}).attr('id')).rules('add', {
            forbiddenChar: "$smarty.const.CENTREON_ILLEGAL_CHAR_OBJ"
        });
    {/if}
  {/foreach}
{/foreach}
</script>

{/block}

