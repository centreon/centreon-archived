{strip}
{block 'b3'}
    grandchild b3 {$b3}*
    include b3 {include '030_include.tpl'}
{/block}
{block 'b4' nocache}
    grandchild b4 {$b4}*
{/block}
{block 'b5'}
    grandchild b5 {$b5}*
{/block}
{block 'b6'}
    grandchild b6 {$b6}*
{/block}