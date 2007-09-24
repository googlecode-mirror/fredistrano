<?php
// As “HtmlHelper::tagErrorMsg()” does not fit our needs,
// we have to write our own function, which we put in a custom helper:

class ErrorHelper extends Helper
{
    function showMessage($target)
    {
        list($model, $field) = explode('/', $target);

        if (isset($this->validationErrors[$model][$field]))
        {
            return sprintf('<div class="error_message">%s</div>',
                              $this->validationErrors[$model][$field]);
        }
        else
        {
            return null;
        }
    }
}


?>
