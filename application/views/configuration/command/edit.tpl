{extends file="../../viewLayout.tpl"}

{block name="title"}Command{/block}

{block name="content"}
    <div class="container">
        <div class="row">
            <form class="form-horizontal" role="form" {$form.attributes}>
                {$form.name.html}
                {$form.command_type.html}
                {$form.command_line.html}
                {$form.enable_shell.html}
                {$form.argument_description.html}
                {$form.hidden}
                {$form.save_form.html}
            </form>
        </div>
    </div>
{/block}
