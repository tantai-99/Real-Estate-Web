@extends('admin::layouts.default')

@section('title', __('2次広告自動公開設定確認'))

@section('style')
	<link href='/js/libs/themes/blue/style.css' media="screen" rel="stylesheet" type="text/css">
	<link href='/js/libs/themes/jquery-ui/jquery-ui.min.css' media="screen" rel="stylesheet" type="text/css">
@stop

@section('script')
	<script type="text/javascript"  src="/js/libs/jquery-ui.min.js"></script>
	<script type="text/javascript"  src="/js/libs/themes/jquery-ui/jquery.ui.datepicker-ja.js"></script>
	<script type="text/javascript"  src="/js/admin/second_estate_edit.js"></script>
    <script type="text/javascript">
        function execSubmit(btnObj) {
            // ボタンを無効化
            $(btnObj).prop("disabled", true);
            // ボタンより各種属性取得
            var btnName = $(btnObj).attr('name');
            var btnVal = $(btnObj).val();
            // formに hiddenで追加
            var hideParam = $("<input/>").attr({ type: 'hidden', name: btnName, value: btnVal});
            $(btnObj).closest('form').append(hideParam).submit();
            return false;
        }
    </script>
@stop

@section('content')
<!-- メインコンテンツ1カラム -->
<div class="main-contents">
    <h1>2次広告自動公開設定</h1>
    <div class="main-contents-body">

        <form action="/admin/company/second-estate-confirm" method="post" name="form" id="form">
        <input type="hidden" name="company_id" id="company_id" value="<?php echo h($view->params['company_id']);?>">
        <?php $view->form->getSubForm('secondEstate')->form("id"); ?>
        @csrf
        <div class="section">
            <h2>2次広告自動公開</h2>
            <table class="form-basic">
            <?php foreach ($view->form->getSubForm('secondEstate')->getElements() as $name => $element):?>

            <?php if($element->getType() == "hidden") continue; ?>
            <?php if($name == "member_name") continue; ?>
            <?php //if($name == "contract_staff_name") continue; ?>
            <?php //if($name == "contract_staff_department") continue; ?>
            <?php //if($name == "cancel_staff_name") continue; ?>
            <?php //if($name == "cancel_staff_department") continue; ?>

            <tr>
                <th><span><?php echo $element->getLabel()?></span></th>
                <td style="white-space: nowrap;">
                    <?php if($element->getType() == "Zend_Form_Element_Text") $element->setAttrib("style", "width:60%;"); ?>

                    <?php echo h($view->form->getSubForm('secondEstate')->getElement($name)->getValue());?>


                    <?php if($name == "contract_staff_id") : ?><br /><?php endif; ?>
                    <?php if($name == "cancel_staff_id") : ?><br /><?php endif; ?>

                    <?php if($element->getDescription() != "") : ?><br /><?php endif; ?>

                    <?php foreach ($element->getMessages() as $error):?>
                    <p style="color:red;"><?php echo h($error)?></p>
                    <?php endforeach;?>

                    <?php if($name == "member_no") : ?>
                        <span style="font-size:12px;">
                        <?php echo $view->form->getSubForm('secondEstate')->getElement("member_name")->getLabel();?>：<?php $view->form->getSubForm('secondEstate')->form("member_name");?>
                        </span>
                        <?php foreach ($view->form->getSubForm('secondEstate')->getElement("member_name")->getMessages() as $error):?>
                        <p style="color:red;"><?php echo $this->h($error)?></p>
                        <?php endforeach;?>
                    <?php endif; ?>

                    <?php if($name == "contract_staff_id") : ?>
                        <?php if ($element->getValue() != "" && $view->form->getSubForm('secondEstate')->getElement("contract_staff_name")->getValue() == "") : ?>
                        <p style="color:red;">担当者名が設定されていません。参照ボタンより取得してください。</p>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if($name == "cancel_staff_id") : ?>
                        <?php if ($element->getValue() != "" && $view->form->getSubForm('secondEstate')->getElement("cancel_staff_name")->getValue() == "") : ?>
                        <p style="color:red;">担当者名が設定されていません。参照ボタンより取得してください。</p>
                        <?php endif; ?>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach;?>
            </table>
        </div>
        <div class="section">
            <h2>エリア</h2>
            <table class="form-basic">
            <?php foreach ($view->form->getSubForm('secondEstateArea')->getElements() as $name => $element):?>
                <?php if ($view->form->getSubForm('secondEstateArea')->getElement($name)->getValue() == null) continue;?>
                <tr<?php if($element->isRequired()):?> class="is-require"<?php endif;?>>
                <th><span><?php echo $element->getLabel()?></span></th>
                    <td style="white-space: nowrap;">
                        
                        <?php foreach ($view->form->getSubForm('secondEstateArea')->getElement($name)->getValue() as $value): ?>
                            

                            <?php $options = $view->form->getSubForm('secondEstateArea')->getElement($name)->getValueOptions();?>
                            <?php echo $options[$value]; ?>
                        <?php endforeach;?>
                    </td>
                </tr>
            <?php endforeach;?>
            </table>
        </div>
        <div class="section">
            <h2>その他</h2>
            <table class="form-basic">
            <?php foreach ($view->form->getSubForm('other')->getElements() as $name => $element):?>

            <?php if($element->getType() == "hidden") continue; ?>

            <tr<?php if($element->isRequired()):?> class="is-require"<?php endif;?>>
                <th><span><?php echo $element->getLabel()?></span></th>
                <td>
                    <?php echo nl2br(h($view->form->getSubForm('other')->getElement($name)->getValue()));?>
                    <?php if($element->getDescription() != "") : ?>
                    <br />
                    <span style="font-size:10px;color:#848484"><?php echo $element->getDescription(); ?></span>
                    <?php endif; ?>

                    <?php foreach ($element->getMessages() as $error):?>
                    <p style="color:red;"><?php echo h($error)?></p>
                    <?php endforeach;?>

                </td>
            </tr>
            <?php endforeach;?>
            </table>
        </div>


        <div class="section">
            <table class="form-basic">
            <tr>
                <td colspan="2" style="text-align:center;padding:10px;">
                    <input type="submit" id="back" name="back" value="戻る" class="btn-t-gray">
                    <input type="button" id="submit-complete" class="btn-t-blue" name="submit-complete" value="完了" onclick="execSubmit(this);return false;">

                </td>
            </tr>
            </table>
        </div>
        </form>
    <div>
</div>
@endsection
