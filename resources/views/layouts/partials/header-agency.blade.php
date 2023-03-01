<div id="g-header">
    <?php $cmsini = getConfigs('cms');?>
    <?php if ($cmsini->header->mark->class):?>
        <?php if($cmsini->header->mark->label === '検証HP2'): ?>
        <div class="h-mark testing2"><?php echo $cmsini->header->mark->label ?></div>
        <?php else:?>
        <div class="h-mark <?php echo $cmsini->header->mark->class ?>"><?php echo $cmsini->header->mark->label ?></div>
        <?php endif;?>
    <?php endif;?>
    <div class="h-logo">
        <img src="/images/common/logo.png" alt="">
        <img class="ml10" alt="" src="/images/common/deputize_logo_bk.png">
    </div>
    <?php if($profile = getInstanceUser('cms')->getProfile()):?>

        <?php if (getInstanceUser('cms')->isDeputize()):?>
            <div class="h-deptize">
                <div>会員：<?php echo h($profile->member_no)?>　<?php echo h($profile->getDisplayCompanyName())?></div>
                <a href="<?php echo urlSimple('logout', 'auth')?>">ログアウト</a>
            </div>
        <?php elseif (getControllerName()=='initialize') :?>
            <div class="h-deptize">
                <div><?php if($profile->domain):?>http://<?php echo h($profile->domain)?><?php endif;?></div>
                <a href="<?php echo urlSimple('logout', 'auth')?>" id="logout">ログアウト</a>
            </div>
        <?php else:?>
            <div class="h-account">
                <a href="javascript:void(0);"><?php echo h($profile->getDisplayCompanyName())?></a>
                <ul>
                    <!-- <li><a href="<?php //echo $this->route('index', 'password')?>">アカウント設定</a></li> -->
                    <li><a href="<?php echo urlSimple('logout', 'auth')?>" id="logout">ログアウト</a></li>
                </ul>
            </div>
        <?php endif;?>
    <?php elseif (getInstanceUser('cms')->getAdminProfile()):?>
        <div class="h-deptize">
            <a href="<?php echo urlSimple('logout', 'auth')?>">ログアウト</a>
        </div>
    <?php endif;?>
</div>