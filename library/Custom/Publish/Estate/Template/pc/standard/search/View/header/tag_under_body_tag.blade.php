<?php if ($view->publishType == config('constants.publish_type.TYPE_PUBLIC') && $view->tag) : ?>
<?php if ($view->tag->under_body_tag) echo trim($view->tag->under_body_tag); ?>
<?php endif; ?>