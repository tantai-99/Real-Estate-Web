<?php if ($view->publishType == config('constants.publish_type.TYPE_PUBLIC') && $view->tag) : ?>
<?php if ($view->tag->google_analytics_code) echo trim($view->tag->google_analytics_code); ?>
<?php if ($view->tag->above_close_head_tag) echo trim($view->tag->above_close_head_tag); ?>
<?php endif; ?>