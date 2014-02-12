{extends file="../../viewLayout.tpl"}

{block name="title"}Connector{/block}

{block name="content"}
    <div class="container">
        {$form}
    </div>
{/block}

{block name="javascript-bottom" append}
    <script>
    {$formValidate}
    </script>
{/block}