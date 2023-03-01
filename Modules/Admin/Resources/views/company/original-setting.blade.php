@extends('admin::layouts.default')
@section('title', $view->original_title)
@section('style')
<link rel="stylesheet" href="/css/admin/common-top-original.css">
<link href="/js/libs/themes/jquery-ui/jquery-ui.min.css" media="screen" rel="stylesheet" type="text/css">
@stop
@section('script')
<script type="text/javascript" src="/js/admin/modal.js"></script>
<script type="text/javascript" src="/js/admin/original_setting.js"></script>
<script type="text/javascript" src="/js/libs/jquery-ui.min.js"></script>
<script type="text/javascript" src="/js/libs/themes/jquery-ui/jquery.ui.datepicker-ja.js"></script>
@stop
@section('content')
<div class="main-contents setting-original">
    <h1><?php echo $view->original_title; ?></h1>

    <div class="original-back">
        <a href="/admin/company/detail/?id=<?php echo h($view->params['company_id']);?>" class="btn-t-gray">戻る</a>
    </div>

    <div>
        <input type="hidden" id="staff_api_url" name="staff_api_url" value="<?php echo $view->backbone->staff->url; ?>">
        <form action="/admin/company/original-setting?company_id=<?php echo h($view->params['company_id']);?>" method="post" name="form" id="form">
            <input type="hidden" name="company_id" id="company_id" value="<?php echo h($view->params['company_id']);?>">
            <?php $view->form->getSubForm('originalSetting')->form("id"); ?>
            @csrf
            <div class="section">
                <h2><?php echo $view->contract_title; ?></h2>
                <table class="form-basic">
                <?php foreach ($view->form->getSubForm('originalSetting')->getElements() as $name => $element):?>
                    <?php if($element->getType() == "hidden") continue; ?>
                    <?php if($name == "member_name") continue; ?>
                    <?php if($name == "contract_staff_name") continue; ?>
                    <?php if($name == "contract_staff_department") continue; ?>
                    <?php if($name == "cancel_staff_name") continue; ?>
                    <?php if($name == "cancel_staff_department") continue; ?>
                    <tr<?php if($element->isRequired()):?> class="is-require"<?php endif;?>>
                        <th><span><?php echo $element->getLabel()?></span></th>
                        <td>
                        <?php if($element->getType() == "text") $element->setAttribute("style", "width:60%;"); ?>
                        <?php $view->form->getSubForm('originalSetting')->form($name);?>
                        <?php if($name == "contract_staff_id") : ?>
                            <button type="button" id="search_contract_staff" name="contract_staff_btn" class="btn-t-gray search_staff" value="contract_staff">参照</button><br />
                        <?php endif; ?>
                        <?php if($name == "cancel_staff_id") : ?>
                            <button type="button" id="search_cancel_staff" name="cancel_staff_btn" class="btn-t-gray search_staff" value="cancel_staff">参照</button><br />
                        <?php endif; ?>

                        <?php if($element->getDescription() != "") : ?>
                            <br />
                            <span style="font-size:10px;color:#848484"><?php echo $element->getDescription(); ?></span>
                        <?php endif; ?>

                        <?php foreach ($element->getMessages() as $error):?>
                            <p class="error"><?php echo h($error)?></p>
                        <?php endforeach;?>

                        <?php if($name == "contract_staff_id") : ?>
                            <span class="staff">
                                担当者名：<?php $view->form->getSubForm('originalSetting')->form("contract_staff_name");?><br />
                                部署　：<?php $view->form->getSubForm('originalSetting')->form("contract_staff_department");?>
                            </span>
                               <?php if ($element->getValue() != "" && $view->form->getSubForm('originalSetting')->getElement("contract_staff_name")->getValue() == "") : ?>
                                <p class="error">担当者名が設定されていません。参照ボタンより取得してください。</p>
                            <?php endif; ?>
                        <?php endif; ?>
                        <?php if($name == "cancel_staff_id") : ?>
                            <span class="staff">
                                担当者名：<?php $view->form->getSubForm('originalSetting')->form("cancel_staff_name");?><br />
                                部署　：<?php $view->form->getSubForm('originalSetting')->form("cancel_staff_department");?>
                            </span>
                            <?php if ($element->getValue() != "" && $view->form->getSubForm('originalSetting')->getElement("cancel_staff_name")->getValue() == "") : ?>
                                <p class="error">担当者名が設定されていません。参照ボタンより取得してください。</p>
                            <?php endif; ?>
                        <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach;?>
                <?php foreach ($view->form->getSubForm('other')->getElements() as $name => $element):?>
                    <?php if($element->getType() == "hidden") continue; ?>
                    <tr<?php if($element->isRequired()):?> class="is-require"<?php endif;?>>
                        <th><span><?php echo $element->getLabel()?></span></th>
                        <td>
                        <?php $view->form->getSubForm('other')->form($name);?>
                        <?php foreach ($element->getMessages() as $error):?>
                            <p class="error"><?php echo h($error)?></p>
                        <?php endforeach;?>
                        </td>
                    </tr>
                <?php endforeach;?>
                </table>
            </div>

            <div class="section">
                <table class="form-basic">
                    <tr>
                        <td colspan="2" class="back">
                            <a href="/admin/company/detail/?id=<?php echo h($view->params['company_id']);?>" class="btn-t-gray" id="back">戻る</a>
                            <button type="button" id="sub_edit" class="btn-t-blue" name="sub_edit" value="確認">確認</button>
                            <input type="hidden" id="submit-confirm" name="submit-confirm" value="submit-confirm">
                        </td>
                    </tr>
                </table>
            </div>
        </form>
    </div>
</div>
@stop