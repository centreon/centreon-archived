<p>
    {t}This installer will help you setup your database and your monitoring configuration.{/t}
    <br>
    {t}The entire process should take around ten minutes.{/t}
</p>

{if isset($errorMessage)}
    {$errorMessage}
{/if}

<script type="text/javascript">

    {literal}

    function validation() {
        return true;
    }

    {/literal}

</script>