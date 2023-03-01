@extends('admin::layouts.default')
@section('title', $view->title)
@section('style')
<link rel="stylesheet" href="/css/admin/common-top-original.css">
<style type="text/css">
    .f-file-up .up-btn:before {
        content: "<?php echo $text->get('list_file_edit.upload');?>";
    }
</style>
@stop
@section('script')
<script type="text/javascript" src="/js/admin/preview_top.js"></script>
<script type="text/javascript" src="/js/admin/top-settings/file.js"></script>
<script type="text/javascript">
    $(function(){
        $('.arrow').on('click', function(){
            var orderby = $(this).attr('data-orderby');
            var field = $(this).closest('th').attr('data-field');
            var url =$(this).closest('table').attr('data-url');
            window.location.href = url + '&field=' + field + '&orderby=' + orderby;
        });
    })
</script>
@stop
<?php
    $data = $view->data;
    $params = $view->params;
?>
@section('content')
<div class="main-contents">

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
                        <input type="submit" name="submit" class="btn-t-blue btn-upload"
                               data-dir-key="<?php echo (!$view->params['isRoot'] && $view->params['sub_dir'])?$view->params['sub_dir']:'';?>"
                               value="<?php echo $text->get('list_file_edit.submit');?>"/>
                    </div>
                </div>

                <div class="right">

                    <?php if($view->params['isRoot']): ?>
                    <a href="<?php echo $params['backTOP'];?>">
                        <button class="btn-t-gray btn-redirect">
                            <?php echo $text->get('back_to_top_detail');?>
                        </button>
                    </a>
                    <?php else: ?>
                        <a href="<?php echo $params['backCurrent'];?>">
                            <button class="btn-t-gray btn-redirect">
                                <?php echo $text->get('back_to_list_file_edit');?>
                            </button>
                        </a>
                    <?php endif;?>
                </div>

            </div>

            <div class="clearfix"></div>


            <div class="section">
                <table id="table-list" class="tb-basic table-list sortable <?php echo (!$view->params['isRoot'] && $view->params['sub_dir'])?'':'root';?>" data-url="<?php echo $params['currentUrl'];?>">
                    <thead>
                        <tr>
                            <th width="20%" data-field="name">
                                <?php echo $text->get('list_file_edit.name');?>
                                <span class="arrows">
                                    <i class="arrow arrow-up" data-orderby="desc"></i>
                                    <i class="arrow arrow-down" data-orderby="asc"></i>
                                </span>
                            </th>
                            <th width="20%" data-field="date">
                                <?php echo $text->get('list_file_edit.date');?>
                                <span class="arrows">
                                    <i class="arrow arrow-up" data-orderby="desc"></i>
                                    <i class="arrow arrow-down" data-orderby="asc"></i>
                                </span>
                            </th>
                            <th width="20%"></th>
                            <th width="40%"></th>
                        </tr>
                    </thead>
                    <tbody>

                    <?php foreach($data->nlist as $f): ?>
                        <?php if ($f->isFile):?>
                            <tr class="is-file">
                                <td class="<?php if (!$f->isSpecialFile || ($f->isSpecialFile && $f->hasBackup)): ?>uploaded<?php endif ?>">
                                    <div data-subdir="<?php echo $data->sub_dir ?>"
                                       data-file="<?php print $f->name ?>"
                                       data-edit-name="<?php print $f->data['can_edit_name'] ?>"
                                       data-edit-data="<?php print $f->data['can_edit_data'] ?>"
                                       class="file-item <?php if ($f->hasContextMenu):?>has-context-menu<?php endif ?>"
                                    >
                                        <?php print($f->name) ?>
                                    </div>
                                </td>
                                <td><?php print($f->updatedAt) ?></td>
                                <td><?php print($f->title); ?></td>
                                <td class="text-right">
                                <?php if ($f->isSpecialFile && $f->hasBackup): ?>
                                    <button class="btn-t-gray btn-revert" data-file="<?php print $f->name ?>">
                                        <?php echo $text->get('list_file_edit.back');?>
                                    </button>
                                <?php elseif (!$f->isSpecialFile): ?>
                                    <button class="btn-t-gray btn-remove" data-file="<?php print $f->name ?>">
                                        <?php echo $text->get('list_file_edit.remove');?>
                                    </button>
                                <?php endif ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <tr class="is-folder">
                                <td>
                                    <a
                                        data-subdir="<?php echo $data->sub_dir ?>"
                                        data-file="<?php print $f->name ?>"
                                        href="<?php print($f->data['link']) ?>">
                                        <img src="/images/icon/folder.png" />
                                        <span><?php print($f->name); ?></span>
                                    </a>
                                </td>
                                <td><?php print($f->updatedAt); ?></td>
                                <td><?php print($f->title); ?></td>
                                <td class="text-right">
                                	<?php if($f->name !== '..'):?>
                                    <input type="submit" name="submit" class="btn-t-blue btn-upload"
                                           data-key="<?php print($f->data['key']); ?>"
                                           data-dir-key="<?php print($f->data['key']); ?>"
                                           value="<?php echo $text->get('list_file_edit.submit');?>"/>
                                    <?php endif;?>
                                </td>
                            </tr>
                        <?php endif ?>
                    <?php endforeach?>

                    </tbody>
                </table>


                <div class="clearfix"></div>

                <div class="section warning-list-file errors text-bold">
                    <?php foreach($view->params['warnings'] as $k => $v):?>
                           <?php echo '<div><span>'.$k.'</span><span>'.$v.'</span></div>'; ?>
                    <?php endforeach;?>
                </div>
            </div>

        </div>
</div>
@stop