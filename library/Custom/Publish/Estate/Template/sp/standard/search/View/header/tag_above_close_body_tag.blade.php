<?php if ($view->publishType == config('constants.publish_type.TYPE_PUBLIC') && $view->tag) : ?>
<?php if ($view->tag->above_close_body_tag) echo trim($view->tag->above_close_body_tag); ?>
<?php endif; ?>