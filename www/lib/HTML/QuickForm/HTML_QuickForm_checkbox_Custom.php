<?php

class HTML_QuickForm_checkbox_Custom extends HTML_QuickForm_checkbox
{
    public function toHtml()
    {
        return '<div class="md-checkbox md-checkbox-inline">' . parent::toHtml() . '</div>';
    }
}
