{extends '030_parent.tpl'}
{strip}
{block 'b3'}
    child b3}*
{/block}
{block 'b4'}
    child b4 {$b4}*
    {$smarty.block.child}*
{/block}
{block 'b5' nocache}
    child b5 {$b5}*
    {$smarty.block.child}*
{/block}
{block 'b61'}
    child b61 {$b6}*
    include 61 {include '030_include.tpl'}
{/block}
{block 'b62' nocache}
    child b62 {$b6}*
    include 62 {include '030_include.tpl'}
{/block}
{block 'b63'}
    child b63 {$b6}*
    {include '030_include_2.tpl'}
{/block}
{block 'b64'}
    child b64 {$b6}*
    include b64 {include '030_include.tpl' nocache}
{/block}
