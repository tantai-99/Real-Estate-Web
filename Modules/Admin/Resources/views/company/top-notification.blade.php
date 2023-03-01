@extends('admin::layouts.default')
@section('title', $view->title)
@section('style')
<link rel="stylesheet" href="/css/admin/common-top-original.css">
@stop
@section('script')
<script type="text/javascript">
    window.titleNews = <?php echo json_encode($view->newsArr)?>;
    window.updateText = "<?php echo $text->get('notification_settings.update.button');?>";
    window.deleteText = "<?php echo $text->get('notification_settings.delete.button');?>";
</script>
<script type="text/javascript" src="/js/admin/preview_top.js"></script>
<script type="text/javascript" src="/js/admin/top-settings/notification.js"></script>
@stop
<?php
    $params = $view->params;
    $form = $view->form;
    $validate = (bool)$view->hp;
?>
@section('content')
<div class="main-contents">
    <form id="form" name="form" method="post" action="">
    <div class="main-contents-body top-original-list top-notification-setting">

        <div class="section head-section">


            <div class="left">
                <div class="item title">
                    <h2><?php echo $view->title ?></h2>
                </div>

                <div class="item btns-content">
                    <div class="item">プレビュー</div>
                    <div class="item btns">
                        <a href="#" class="btn-p-pc btn-preview" data-type="pc"></a>
                        <a href="#" class="btn-p-sp btn-preview" data-type="sp"></a>
                    </div>
                </div>

                <div class="item">
                    <input type="submit" name="submit" class="btn-t-blue"
                           id="notification-settings-submit"
                        value="<?php echo $text->get('notification_settings.submit');?>"

                           <?php echo (!$validate)?'disabled':'';?>
                    />
                </div>
            </div>

            <div class="right">
                <a href="<?php echo $params['backTOP'];?>" class="btn btn-t-gray btn-redirect">
                        <?php echo $text->get('back_to_top_detail');?>
                </a>
            </div>

        </div>

        <div class="clearfix"></div>

        <?php if($validate):?>
        <?php $pages = $form->getSubForm('pages')->getSubForms(); ?>

        <?php if(count($pages) > 0):?>
         <?php foreach($pages as $k=>$v): ?>
            <div class="section">
                <div class="section-title">
                    <?php echo $v->getTitle() . $v->getElement('notification_type')->getValue(); ?>
                </div>
                <table class="tb-basic tb-centered tb-select tb-bordered">
                    <thead>
                    <tr>
                        <th>
                            <?php echo $text->get('notification_settings.news_column_1');?>
                        </th>
                        <th>
                            <?php echo $text->get('notification_settings.news_column_2');?>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>
                            <?php echo $v->getElement('page_size')->getValue();?>
                        </td>
                        <td>
								<div class="d-flex">
									<div class="d-flex flex-start col-5">
										<?php echo $v->form('id'); ?>
										<div class="select-custom select-ct lg w-100"><?php echo $v->form('page_size');?><div class="sel-custom"><?php echo $v->getElement('page_size')->getValue();?></div></div>
									</div>
									<div class="d-flex flex-end col-5 mr-5">
                                        <input type="hidden" name="<?php echo $v->getElement('cms_disable')->getFullName(); ?>" value="<?php echo $v->getElement('cms_disable')->getValue(); ?>">
										<?php //echo $v->form('cms_disable');?>
                                        <input type="checkbox" id="<?php echo $v->getElement('cms_disable')->getId(); ?>" class="form-control  settings-form" name="<?php echo $v->getElement('cms_disable')->getFullName(); ?>" value="1" <?php echo ($v->getElement('cms_disable')->getValue()) ? 'checked' : ''; ?>>
                                        <label for="<?php echo $v->getElement('cms_disable')->getId(); ?>" class="optional"><?php echo $v->getElement('cms_disable')->getLabel(); ?></label>
									</div>
								</div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        <?php endforeach;?> 
        <?php endif;?>



        <?php if(count($pages) > 0): ?>
        <div class="section">
            <div class="section-title">
                <?php echo $text->get('notification_settings.create');?>
            </div>
            <div class="set-box">
                <div class="flex-table-box">
                    <table class="tb-basic tb-centered tb-create tb-notification-create">
                        <tbody>
                        <tr>
                            <td width="30%">
                                <?php echo $text->get('notification_settings.row.title');?>
                            </td>
                            <td width="60%">
                                <div><?php echo $form->getSubForm('create')->form('title');?></div>
                                <div class="title-error errors" style="text-align:left;">
                                    <?php foreach($form->getSubForm('create')->getElement('title')->getMessages() as $error): ?>
                                        <p style="color:red;"><?php echo h($error)?></p>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <?php echo $text->get('notification_settings.row.class');?>
                            </td>
                            <td>
                                <div><?php echo $form->getSubForm('create')->form('class');?></div>
                                <div class="class-error errors" style="text-align:left;">
                                    <?php foreach($form->getSubForm('create')->getElement('class')->getMessages() as $error): ?>
                                        <p style="color:red;"><?php echo h($error);?></p>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <?php echo $text->get('notification_settings.row.parent_page_id');?>
                            </td>
                            <td class="force-text-left">
                                <div class="notification-radio">
                                    <?php echo $form->getSubForm('create')->form('parent_page_id');?>
                                </div>
                                <div class="parent_page_id-error errors">
                                    <?php foreach($form->getSubForm('create')->getElement('parent_page_id')->getMessages() as $error): ?>
                                        <p style="color:red;"><?php echo h($error); ?></p>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                </div>
                <div class="flex-box">
                    <div class="flex-box-submit">
                        <div class="box-submit">
                            <?php //echo $view->form->create->form('submit');?>
                            <input type="submit" name="create[submit]" id="create-submit" value="<?php echo $text->get('notification_settings.create.button') ?>" class="btn-t-blue btn-noti-create">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif;?>



        <?php if(count($pages) > 0): ?>
        <?php $count = 1;?>
        <?php foreach($pages as $k=>$v): ?>
        <div class="section">
            <div class="section-title">
                <?php echo $text->get('notification_settings.list_'.$count.'.title');?>
                <?php $count++;?>
            </div>
            <table class="tb-basic tb-centered tb-news-list" data-id="<?php echo $v->getElement('page_id')->getValue();?>">
                <thead>
                    <tr>
                        <th width="20%"><?php echo $text->get('notification_settings.row.title');?></th>
                        <th width="20%"><?php echo $text->get('notification_settings.row.class');?></th>
                        <th width="60%"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $details = $v->getSubForms();?>
                    <?php if(count($details) > 0): ?>
                        <?php
                        /**
                         * @var $detail Zend_Form
                         */
                        ?>
                    <?php foreach($details as $key_detail => $detail):?>

                        <?php $jsonData = array(
                                'title' => $detail->getElement('title')->getValue(),
                                'class' => $detail->getElement('class')->getValue(),
                                'parent_page_id' => $detail->getElement('parent_page_id')->getValue(),
                                'id' => $detail->getElement('id')->getValue()
                            );?>
                    <tr
                            data-id="<?php echo $jsonData['id'];?>"
                            data-details="<?php echo htmlspecialchars(json_encode($jsonData), ENT_QUOTES, 'UTF-8')?>"
                    >
                        <td><?php echo $detail->getElement('title')->getValue();?></td>
                        <td><?php echo $detail->getElement('class')->getValue();?></td>
                        <td class="actions">
                            <div class="sort">
                                <i class="i-e-up"></i>
                                <i class="i-e-down"></i>
                            </div>
                            <div class="buttons" >
                                <?php //echo $detail->form('update');?>
                                <input type="submit" value="<?php echo $text->get('notification_settings.update.button'); ?>" class="btn-t-gray btn-noti-update">
                                <?php //echo $detail->form('delete');?>
                                <input type="submit" value="<?php echo $text->get('notification_settings.delete.button'); ?>" class="btn-t-gray btn-noti-delete">
                            </div>
                        </td>
                    </tr>
                    <?php endforeach;?>
                    <?php endif;?>

                </tbody>
            </table>
        </div>


            <?php endforeach;?>
            <?php unset($count);?>
        <?php endif;?>

        <?php endif;?>


    </div>

    </form>
</div>

<?php if($validate):?>
<div class="edit-form" style="display:none;">
    <form method="post" class="top-notification-setting form-edit">
        <div class="section" >
            <div class="section-title text-left">
                <h2 class="pt10 pb10">
                    <?php echo $text->get('notification_settings.update');?>
                </h2>
            </div>
            <div class="set-box-noti-edit">
                <div class="flex-table-box-noti-edit">
            <table class="tb-basic tb-centered tb-edit tb-bordered">
                <tbody>
                <tr>
                    <td width="35%">
                        <?php echo $text->get('notification_settings.row.title');?>
                    </td>
                    <td width="55%">
                        <div>
                            <?php echo $form->getSubForm('edit')->form("id");?>
                            <?php echo $form->getSubForm('edit')->form('title');?>
                        </div>
                        <div class="title-error errors">
                            <?php foreach($form->getSubForm('edit')->getElement('title')->getMessages() as $error): ?>
                                <p style="color:red;"><?php echo h($error)?></p>
                            <?php endforeach; ?>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td>
                        <?php echo $text->get('notification_settings.row.class');?>
                    </td>
                    <td>
                        <div><?php echo $form->getSubForm('edit')->form('class');?></div>
                        <div class="class-error errors">
                            <?php foreach($form->getSubForm('edit')->getElement('class')->getMessages() as $error): ?>
                                <p style="color:red;"><?php echo h($error);?></p>
                            <?php endforeach; ?>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td>
                        <?php echo $text->get('notification_settings.row.parent_page_id');?>
                    </td>
                    <td class="force-text-left">
                        <div class="notification-radio">
                            <?php echo $form->getSubForm('edit')->form('parent_page_id');?>
                        </div>
                        <div class="parent_page_id-error errors">
                            <?php foreach($form->getSubForm('edit')->getElement('parent_page_id')->getMessages() as $error): ?>
                                <p style="color:red;"><?php echo h($error); ?></p>
                            <?php endforeach; ?>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
            </div>
                <div class="flex-box-noti-edit">
                    <div class="flex-box-submit-noti-edit">
                        <div class="box-submit-noti-edit">
                        <?php
                            //$updateText = $text->get('notification_settings.update.submit');
                            //$view->form->edit->getElement('submit')->setLabel($updateText);
                            //echo $view->form->edit->form('submit');
                        ?>
                        <input type="submit" name="edit[submit]" id="edit-submit" value="<?php echo $text->get('notification_settings.update.submit'); ?>" class="btn-t-blue btn-noti-create">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<div class="delete-form" style="display:none;">
    <form method="post" class="top-notification-setting form-delete">
        <div class="section" >
            <div class="section-title text-left">
                <h2 class="pt10 pb10">
                    <?php echo $text->get('notification_settings.delete');?>
                </h2>
            </div>
            <table class="tb-basic tb-edit tb-bordered">
                <tbody>
                <tr>
                    <td width="30%">
                        <?php echo $text->get('notification_settings.row.title');?>
                    </td>
                    <td width="70%">
                        <?php echo $form->getSubForm('delete')->form('id');?>
                        <div id="delete-title">
                            <?php echo $form->getSubForm('delete')->getElement('title')->getValue(); ?>
                        </div>
                        <div class="title-error errors">
                            <?php foreach($form->getSubForm('delete')->getElement('title')->getMessages() as $error): ?>
                                <p style="color:red;"><?php echo h($error)?></p>
                            <?php endforeach; ?>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td>
                        <?php echo $text->get('notification_settings.row.class');?>
                    </td>
                    <td>
                        <div id="delete-class">
                            <?php echo $form->getSubForm('delete')->getElement('class')->getValue();?>
                        </div>
                        <div class="class-error errors">
                            <?php foreach($form->getSubForm('delete')->getElement('class')->getMessages() as $error): ?>
                                <p style="color:red;"><?php echo h($error);?></p>
                            <?php endforeach; ?>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td>
                        <?php echo $text->get('notification_settings.row.parent_page_id');?>
                    </td>
                    <td>
                        <div id="delete-parent_page_id">
                            <?php echo $form->getSubForm('delete')->getElement('parent_page_id')->getLabel();?>
                        </div>
                        <div class="parent_page_id-error errors">
                            <?php foreach($form->getSubForm('delete')->getElement('parent_page_id')->getMessages() as $error): ?>
                                <p style="color:red;"><?php echo h($error); ?></p>
                            <?php endforeach; ?>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
            <div class="p10 text-center w-100">
                <?php //echo $view->form->delete->form('cancel');?>
                <button name="delete[cancel]" id="delete-cancel" type="button" class="btn-t-gray modal-close-button"><?php echo $text->get('notification_settings.delete.cancel'); ?></button>
                <?php //$deleteText = $text->get('notification_settings.delete.button');?>
                <?php //$view->form->delete->getElement('submit')->setLabel($deleteText);?>
                <?php //echo $view->form->delete->form('submit');?>
                <input type="submit" name="delete[submit]" id="delete-submit" value="<?php echo $text->get('notification_settings.delete.button'); ?>" class="btn-t-blue btn-noti-create">
            </div>
        </div>
    </form>
</div>

<?php endif;?>
@stop