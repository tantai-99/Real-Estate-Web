<?php //代行作成時サイドナビ表示 ;?>
<?php if ($view->acl()->isAllowed('publish', 'creator')) : ?>
    @include('layouts/partials/side/creator' )
<?php else : ?>
    <script>
        $(function () {
            $('body.column2').removeClass('column2');
        });
    </script>
<?php endif; ?>
