@extends('admin::layouts.default')
@section('title', $view->original_title)
@section('style')
<link rel="stylesheet" href="/css/admin/common-top-original.css">
<link href="/js/libs/themes/jquery-ui/jquery-ui.min.css" media="screen" rel="stylesheet" type="text/css">
@stop
@section('script')
<script type="text/javascript" src="/js/admin/original_setting.js"></script>
<script type="text/javascript" src="/js/libs/jquery-ui.min.js"></script>
<script type="text/javascript" src="/js/libs/themes/jquery-ui/jquery.ui.datepicker-ja.js"></script>
@stop
@section('content')
<div class="main-contents setting-original">
    <h1><?php echo $view->original_title; ?></h1>
    <div class="original-back">
        <a href="/admin/company/original-setting/?company_id=<?php echo h($view->params['company_id']);?>" class="btn-t-gray">戻る</a>
    </div>
    <div class="main-original">
        <form action="/admin/company/original-setting-confirm" method="post" name="form" id="form">
        <input type="hidden" name="company_id" id="company_id" value="<?php echo h($view->params['company_id']);?>">
        <?php $view->form->getSubForm('originalSetting')->form("id"); ?>
        @csrf
        <div class="section">
            <h2><?php echo $view->original_sub_title; ?></h2>
            <table class="form-basic">
            <?php foreach ($view->form->getSubForm('originalSetting')->getElements() as $name => $element):?>
            <?php if($element->getType() == "hidden") continue; ?>
            <tr>
                <th><span><?php echo $element->getLabel()?></span></th>
                <td>
                    <?php if($element->getType() == "text") $element->setAttribute("style", "width:60%;"); ?>
                    <?php echo h($view->form->getSubForm('originalSetting')->getElement($name)->getValue());?>
                    <?php if($name == "contract_staff_id") : ?><br /><?php endif; ?>
                    <?php if($name == "cancel_staff_id") : ?><br /><?php endif; ?>
                    <?php if($element->getDescription() != "") : ?><br /><?php endif; ?>
                    <?php foreach ($element->getMessages() as $error):?>
                    <p class="error"><?php echo h($error)?></p>
                    <?php endforeach;?>
                    <?php if($name == "contract_staff_id") : ?>
                        <?php if ($element->getValue() != "" && $view->form->getSubForm('originalSetting')->getElement("contract_staff_name")->getValue() == "") : ?>
                        <p class="error">担当者名が設定されていません。参照ボタンより取得してください。</p>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if($name == "cancel_staff_id") : ?>
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
                    <?php echo nl2br(h($view->form->getSubForm('other')->getElement($name)->getValue()));?>
                    <?php foreach ($element->getMessages() as $error):?>
                    <p class="error"><?php echo h($error)?></p>
                    <?php endforeach;?>
                </td>
            </tr>
            <?php endforeach;?>
            </table>
        </div>

        <div class="section ">
            <table class="form-basic">
            <tr>
                <td colspan="2" class="back">
                    <input type="submit" id="back" name="back" value="戻る" class="btn-t-gray">
                    <input type="submit" id="submit-complete" class="btn-t-blue" name="submit-complete" value="完了">
                </td>
            </tr>
            </table>
        </div>
        </form>
    </div>
</div>
@stop
