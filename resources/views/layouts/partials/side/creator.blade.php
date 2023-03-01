<div id="side">
    <div class="inner">
        <h1>基本設定</h1>
        <ul class="side-menu">
            <?php $controller = 'creator'; ?>
            <?php $subcontroller = 'publish'; ?>
            <?php if ($view->acl()->isAllowed('publish', $controller)): ?>
                <li class="<?php if (isCurrent('simple', $subcontroller) || isCurrent('detail', $subcontroller)): ?>is-active<?php endif; ?>">
                    <a <?php if (!getInstanceUser('cms')->getBackupHp()): ?>href="<?php echo $view->route('publish', $controller) ?>" <?php else: ?>class="is-disable"<?php endif; ?>>制作代行テストサイト</a>
                </li>
            <?php endif; ?>
            <?php if ($view->acl()->isAllowed('copy-to-company', $controller)): ?>
                <li class="<?php if (isCurrent('copyToCompany', $controller)): ?>is-active<?php endif; ?>">
                    <a <?php if (!getInstanceUser('cms')->getBackupHp()): ?>href="<?php echo $view->route('copy-to-company', $controller) ?>" <?php else: ?>class="is-disable"<?php endif; ?>>代行更新</a>
                </li>
            <?php endif; ?>
            <?php if ($view->acl()->isAllowed('rollback', $controller)): ?>
                <li class="<?php if (isCurrent('rollback', $controller)): ?>is-active<?php endif; ?>">
                    <a <?php if (getInstanceUser('cms')->getBackupHp()): ?>href="<?php echo $view->route('rollback', $controller) ?>" <?php else: ?>class="is-disable"<?php endif; ?>>ロールバック</a>
                </li>
            <?php endif; ?>
            <?php if ($view->acl()->isAllowed('delete-hp', $controller)): ?>
                <li class="<?php if (isCurrent('deleteHp', $controller)): ?>is-active<?php endif; ?>">
                    <a href="<?php echo $view->route('delete-hp', $controller) ?>">制作代行サイト削除</a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</div>
