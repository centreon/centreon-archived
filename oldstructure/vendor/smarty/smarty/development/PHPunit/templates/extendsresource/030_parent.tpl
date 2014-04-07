{strip}
{block 'b1'}
    parent b1 {$b1}*
    {block 'b2'}
       parent b2*
    {/block}
    {block 'b3' nocache}
        parent b3*
    {/block}
{/block}<br>
{block 'b4'}
    parent b4*
{/block}<br>
{block 'b5'}
    parent b5*
{/block}<br>
{block 'b61'}
    parent b61*
{/block}<br>
{block 'b62'}
    parent b62*
{/block}<br>
{block 'b63'}
    parent b63*
{/block}<br>
{block 'b64'}
    parent b64*
{/block}<br>
parent include {include '030_include.tpl' nocache inline}<br>
parent include2 {include '030_include_2.tpl' inline}<br><br>