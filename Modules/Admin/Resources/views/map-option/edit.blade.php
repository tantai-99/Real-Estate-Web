@extends('admin::layouts.default')

@section('title', __('地図検索'))

@section('style')
	<link href='/js/libs/themes/blue/style.css' media="screen" rel="stylesheet" type="text/css">
	<link href='/js/libs/themes/jquery-ui/jquery-ui.min.css' media="screen" rel="stylesheet" type="text/css">
@stop

@section('script')
	<script type="text/javascript"  src="/js/libs/jquery-ui.min.js"></script>
	<script type="text/javascript"  src="/js/libs/themes/jquery-ui/jquery.ui.datepicker-ja.js"></script>
	<script type="text/javascript"  src="/js/admin/company_regist.js"></script>
@stop

@section('content')
<!-- メインコンテンツ1カラム -->
<div class="main-contents">
	<h1>地図検索</h1>
	<div style="text-align:right;margin-top:-50px;margin-right:20px;">
		<a href="/admin/company/detail/?id=<?php echo h($view->form->getSubForm('map')->getValue('id'));?>" class="btn-t-gray">戻る</a>
	</div>

	<div class="main-contents-body">
		<input type="hidden" id="member_api_url" name="member_api_url" value="<?php echo $view->backbone->member->url	; ?>">
		<input type="hidden" id="staff_api_url"  name="staff_api_url"  value="<?php echo $view->backbone->staff->url	; ?>">

		<form action="/admin/map-option/edit?id=<?= $view->form->getSubForm('map')->getValue('id'); ?>" method="post" name="form" id="form">
        @csrf
        <?php $view->form->getSubForm('map')->form( "id" ) ; ?>

            <div class="section">
              <h2>スタンダード地図オプション</h2>
              <table class="form-basic">
              <?php foreach ($view->form->getSubForm('map')->getElements() as $name => $element):?>
    
              <?php if( $element->getType() == "hidden"       ) continue ; ?>
              <?php if( $name               == "map_contract_staff_name"        ) continue ; ?>
              <?php if( $name               == "map_contract_staff_department"  ) continue ; ?>
	          <?php if( $name               == "map_cancel_staff_name"          ) continue ; ?>
	          <?php if( $name               == "map_cancel_staff_department"    ) continue ; ?>
    
              <tr<?php if($element->isRequired()):?> class="is-require"<?php endif;?>>
                <th><span><?php echo $element->getLabel()?></span></th>
                <td style="white-space: nowrap;">
                  <?php if($element->getType() == "text") $element->setAttribute("style", "width:60%;"); ?>
    
                  <?php $view->form->getSubForm('map')->form( $name ) ; ?>
    
                  <?php if( $name == "map_contract_staff_id"	) : ?>
                    <button type="button" id="map_contract_staff" name="contract_staff_btn" class="btn-t-gray search_staff" value="map-map_contract_staff">参照</button><br />
                  <?php endif; ?>
    
                  <?php if( $name == "map_cancel_staff_id"		) : ?>
                    <button type="button" id="map_cancel_staff"   name="cancel_staff_btn"   class="btn-t-gray search_staff" value="map-map_cancel_staff"  >参照</button><br />
                  <?php endif; ?>
    
                  <?php if( $element->getDescription() != ""	) : ?>
	                <br />
	                <span style="font-size:10px;color:#848484"><?php echo $element->getDescription(); ?></span>
                  <?php endif; ?>
    
                  <?php foreach ( $element->getMessages() as $error ): ?>
                  <p style="color:red;"><?= h($error)?></p>
                  <?php endforeach;?>
    
                  <?php if( $name == "map_contract_staff_id" ) : ?>
                    <span style="font-size:12px;">
                      担当者名：<?php $view->form->getSubForm('map')->form( "map_contract_staff_name"       ) ; ?><br />
                      部署　　：<?php $view->form->getSubForm('map')->form( "map_contract_staff_department" ) ; ?>
                    </span>
                    <?php if ( $element->getValue() != "" && $view->form->getSubForm('map')->getElement("map_contract_staff_name")->getValue() == "" ) : ?>
                      <p style="color:red;">担当者名が設定されていません。参照ボタンより取得してください。</p>
                    <?php endif; ?>
                  <?php endif; ?>

                  <?php if( $name == "map_cancel_staff_id" ) : ?>
                    <span style="font-size:12px;">
                      担当者名：<?php $view->form->getSubForm('map')->form( "map_cancel_staff_name"       ) ; ?><br />
                      部署　　：<?php $view->form->getSubForm('map')->form( "map_cancel_staff_department" ) ; ?>
                    </span>
                    <?php if ( $element->getValue() != "" && $view->form->getSubForm('map')->getElement("map_cancel_staff_name")->getValue() == "" ) : ?>
                      <p style="color:red;">担当者名が設定されていません。参照ボタンより取得してください。</p>
                    <?php endif; ?>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach;?>
              </table>
            </div>

			<div class="section">
				<table class="form-basic">
				<tr>
					<td colspan="2" style="text-align:center;padding:10px;">
					<a href="/admin/company/detail/?id=<?php echo h($view->form->getSubForm('map')->getValue('id'));?>" class="btn-t-gray" id="back">戻る</a>
					<button type="button" id="sub_edit" class="btn-t-blue" name="sub_edit" value="確認">確認</button>
					<input  type="hidden" id="asd" name="asd" value="asd">

				</td>
			</tr>
			</table>
		</div>
		</form>
  </div>
</div>
@endsection

