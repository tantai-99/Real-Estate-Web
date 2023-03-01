<div id="g-navi">
    <ul class="g-navi-main">

        <?php $module = getModuleName() ?>
        <?php $controller = 'index' ?>
        <li class="<?php if (isCurrent(null, $controller)) : ?> is-active<?php endif; ?>">
            <a class="i-m-home" href="<?php echo $view->route('index', 'index') ?>">ホーム</a>
        </li>

        <?php $controller = 'site-setting' ?>
        <?php $eSearchSetting = 'estate-search-setting' ?>
        <?php $eSpecial = 'estate-special' ?>
        <?php $secondEstate = 'second-estate-search-setting' ?>
        <?php $isAllowedSecondEstate = getInstanceUser('cms')->isAvailableSecondEstate() && $view->acl()->isAllowed('index', $secondEstate);
        $agency = !getInstanceUser('cms')->isNerfedTop();
        ?>
        <?php if ($view->acl()->isAllowed('index', $controller) || $view->acl()->isAllowed('design', $controller) || $view->acl()->isAllowed('image', $controller)) : ?>
            <li class="pull<?php if (isCurrent(null, $controller) || isCurrent(null, $eSearchSetting) || isCurrent(null, $eSpecial) || isCurrent(null, $secondEstate)) : ?> is-active<?php endif; ?>">
                <a class="i-m-base">基本設定</a>
                <div class="pull-wrap">
                    <ul>
                        <?php if ($view->acl()->isAllowed('index', $controller)) : ?>
                            <li class="<?php if (isCurrent('index', $controller)) : ?>is-active<?php endif; ?>"><a class="i-m-set" href="<?php echo $view->route('index', $controller) ?>">初期設定</a></li>
                        <?php endif; ?>
                        <?php if ($view->acl()->isAllowed('design', $controller) && $agency) : ?>
                            <li class="<?php if (isCurrent('design', $controller)) : ?>is-active<?php endif; ?>"><a class="i-m-design" href="<?php echo $view->route('design', $controller); ?>">デザイン選択</a></li>
                        <?php endif; ?>
                        <?php if ($view->acl()->isAllowed('image', $controller)) : ?>
                            <li class="<?php if (isCurrent('image')) : ?>is-active<?php endif; ?>"><a class="i-m-img" href="<?php echo $view->route('image', $controller) ?>">画像フォルダ</a></li>
                        <?php endif; ?>
                        <?php if ($view->acl()->isAllowed('file2', $controller)) : ?>
                            <li class="<?php if (isCurrent('file2')) : ?>is-active<?php endif; ?>"><a class="i-m-file" href="<?php echo $view->route('file2', $controller) ?>">ファイル管理</a></li>
                        <?php endif; ?>

                        <?php if ($view->cms_plan > config('constants.cms_plan.CMS_PLAN_LITE')) : ?>
                            <?php if ($view->acl()->isAllowed('index', $eSearchSetting)) : ?>
                                <li class="<?php if (isCurrent('index', $eSearchSetting)) : ?>is-active<?php endif; ?>"><a class="i-m-article" href="<?php echo $view->route('index', $eSearchSetting)?>">物件検索設定</a></li>
                            <?php endif; ?>

                            <?php if ($isAllowedSecondEstate) : ?>
                                <li class="<?php if (isCurrent('second-estate-search-setting')) : ?>is-active<?php endif; ?>"><a class="i-m-ad" href="<?php echo $view->route('index', $secondEstate)?>">2次広告自動公開設定</a></li>
                            <?php endif; ?>

                            <?php if ($view->acl()->isAllowed('index', $eSpecial)) : ?>
                                <li class="<?php if (isCurrent('index', $eSpecial)) : ?>is-active<?php endif; ?>"><a class="i-m-special" href="<?php echo $view->route('index', $eSpecial) ?>">特集設定</a></li>
                            <?php endif; ?>
                        <?php endif; ?>

                    </ul>
                </div>
            </li>
        <?php endif; ?>

        <?php $controller = 'site-map' ?>
        <?php if ($view->acl()->isAllowed('index', $controller)) : ?>
            <li class="<?php if (isCurrent(null, $controller) || isCurrent(null, 'page')) : ?> is-active<?php endif; ?>">
                <a class="i-m-edit<?php if (getInstanceUser('cms')->isCreator() && getInstanceUser('cms')->hasBackupData()) : ?> is-disable<?php endif; ?>" <?php if (!getInstanceUser('cms')->isCreator() || !getInstanceUser('cms')->hasBackupData()) : ?>href="<?php echo $view->route('index', $controller) ?>" <?php endif; ?>>ページの作成/更新</a>
            </li>
        <?php endif; ?>

        <?php $controller = 'publish' ?>
        <?php if ($view->acl()->isAllowed('simple', $controller) && !$view->acl()->isAllowed('publish', 'creator'))/*代行作成は非表示*/ : ?>
            <!--
            <li class="pull">
            -->
            <li class="<?php if (isCurrent(null, 'publish')) : ?> is-active<?php endif; ?><?php if (getInstanceUser('cms')->hasChanged()) : ?> is-alert<?php endif; ?>">
                <a class="i-m-publish" href="<?php echo route('default.' . $controller . '.simple') ?>">サイトの公開/更新</a>
                <!--
                <div class="pull-wrap">
                <ul>
                    <li><a class="i-m-pub" href="">公開設定</a></li>
                    <li><a class="i-m-test" href="">テストサイト確認</a></li>
                    <li><a class="i-m-backup" href="">バックアップ復元</a></li>
                </ul>
                </div>
                -->
                <?php if (getInstanceUser('cms')->hasChanged()) : ?>
                    <img class="icon-alert" src="/images/home/home_alert.png" alt="">
                <?php endif; ?>
            </li>
        <?php endif; ?>

        <?php $controller = 'creator' ?>
        <?php $subcontroller = 'publish' ?>
        <?php if ($view->acl()->isAllowed('copy-to-company', $controller) || $view->acl()->isAllowed('rollback', $controller) || $view->acl()->isAllowed('delete-hp', $controller) || $view->acl()->isAllowed('publish', $controller)) : ?>
            <li class="pull<?php if (isCurrent(null, $controller) || isCurrent(null, $subcontroller)) : ?> is-active<?php endif; ?>">
                <a class="i-m-deputize"><?php echo ($agency) ? "制作代行" : "代行作成"; ?></a>
                <div class="pull-wrap">
                    <ul>
                        <?php if ($view->acl()->isAllowed('publish', $controller)) : ?>
                            <li class="<?php if (isCurrent('simple', $subcontroller) || isCurrent('detail', $subcontroller)) : ?>is-active<?php endif; ?>">
                                <a <?php if (!getInstanceUser('cms')->getBackupHp()) : ?>href="<?php  echo $view->route('publish', $controller) ?>" <?php else : ?>class="is-disable" <?php endif; ?>><?php echo ($agency) ? "制作代行テストサイト" : "代行作成テストサイト確認"; ?></a>
                            </li>
                        <?php endif; ?>
                        <?php if ($view->acl()->isAllowed('copy-to-company', $controller)) : ?>
                            <li class="<?php if (isCurrent('copyToCompany', $controller)) : ?>is-active<?php endif; ?>">
                                <a <?php if (!getInstanceUser('cms')->getBackupHp()) : ?>href="<?php echo $view->route('copy-to-company', $controller) ?>" <?php else : ?>class="is-disable" <?php endif; ?>>代行更新</a>
                            </li>
                        <?php endif; ?>
                        <?php if ($view->acl()->isAllowed('rollback', $controller)) : ?>
                            <li class="<?php if (isCurrent('rollback', $controller)) : ?>is-active<?php endif; ?>">
                                <a <?php if (getInstanceUser('cms')->getBackupHp()) : ?>href="<?php echo $view->route('rollback', $controller) ?>" <?php else : ?>class="is-disable" <?php endif; ?>>ロールバック</a>
                            </li>
                        <?php endif; ?>
                        <?php if ($view->acl()->isAllowed('delete-hp', $controller)) : ?>
                            <li class="<?php if (isCurrent('deleteHp', $controller)) : ?>is-active<?php endif; ?>">
                                <a href="<?php  echo $view->route('delete-hp', $controller)  ?>"><?php echo ($agency) ? "制作代行サイト削除" : "代行作成データ削除"; ?></a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </li>
        <?php endif; ?>
    </ul>


    <ul class="g-navi-sub">
        <?php $controller = 'diacrisis' ?>
        <li class="<?php if (isCurrent(null, 'diacrisis')) : ?> is-active <?php endif; ?>"><a class="i-m-karte" href="<?php echo $view->route('index', $controller) ?>">評価・分析</a></li>
        <?php if (getInstanceUser('cms')->isLogin() && !getInstanceUser('cms')->getProfile()->isAnalyze()) : ?>
            <?php $controller = 'utility' ?>
            <li><a class="i-m-help" href="<?php echo $view->route('index', $controller) ?>" target="_blank">お役立ち</a></li>
        <?php endif; ?>
    </ul>
</div>