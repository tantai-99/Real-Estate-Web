<?php
    use Library\Custom\Model\Lists\Original;
?>
@extends('admin::layouts.default')
@section('content')

    <?php 
    $class = $view->agency ? 'class="agency-disable"' : ''; ?>
    <?php 
    if(isset($view->original_edit) && $view->original_edit== true)
    {
            $classEdit = $view->original_edit ? 'class="agency-disable"' : '';
    }
    ?>
    @section('script')
        <script src="/js/admin/modal.js", type="text/javascript"> </script>
        <script src="/js/admin/company_detail.js",type="text/javascript"> </script>
        @stop
    @section('title')
        契約者詳細
    @endsection
        <!-- メインコンテンツ1カラム -->
        <div class="main-contents">
            <h1>契約者詳細</h1>
            <div class="btn-back-pageright">
                <a href="/admin/company" class="btn-t-gray">戻る</a>
            </div>
            <div class="main-contents-body">
                <div class="btn-agreement">
                    <ul>
                        <input type="hidden" id="company_id" name="company_id" value="<?php echo $view->form->getSubForm('basic')->getElement('id')->getValue();?>">
                        <input type="hidden" id="member_no"  name="member_no"  value="<?php echo h($view->form->getSubForm('basic')->getElement('member_no')->getValue());?>">
                        <li <?php echo $class; ?>><a <?php if (!$view->agency) :?> href="/admin/company/edit?id=<?php echo $view->form->getSubForm('basic')->getElement('id')->getValue(); ?>" <?php endif; ?> class="btn-t-blue size-l">契約者詳細編集</a></li>
                        <li <?php echo $class; ?>><a <?php if (!$view->agency) :?> href="/admin/company/tag?company_id=<?php echo $view->form->getSubForm('basic')->getElement('id')->getValue(); ?>" <?php endif; ?> class="btn-t-blue size-l"><?php echo $view->original_tag; ?></a></li>
                        <li <?php echo $class; ?>><a <?php if (!$view->agency) :?> href="/admin/company/private?company_id=<?php echo $view->form->getSubForm('basic')->getElement('id')->getValue(); ?>" <?php endif; ?> class="btn-t-blue size-l">非公開設定</a></li>

						<?php if(in_array($view->form->getSubForm('basic')->getElement('contract_type')->getValue(), [ config('constants.company_agreement_type.CONTRACT_TYPE_PRIME'), config('constants.company_agreement_type.CONTRACT_TYPE_DEMO')], true)
								&& $view->initialize_cms) { ?>
                        	<li><a href="/admin/company/initialize-cms?company_id=<?php echo $view->form->getSubForm('basic')->getElement('id')->getValue(); ?>" class="btn-t-blue size-l">CMSデータ削除</a></li>
						<?php } else {?>
                        	<li <?php echo $class; ?>><a class="btn-t-blue size-l">CMSデータ削除</a></li>
						<?php } ?>

                        <li <?php echo $class; ?>><a <?php if (!$view->agency) :?> href="/admin/company/group?company_id=<?php echo $view->form->getSubForm('basic')->getElement('id')->getValue(); ?>" <?php endif; ?> class="btn-t-blue size-l">グループ会社設定</a></li>
                        <?php if($view->form->getSubForm('basic')->getElement('contract_type')->getValue() == 0) : ?>
	                        <?php
	                        	if (
	                        		( $view->form->getSubForm('status')->getElement('cms_plan')->getValue() == config('constants.cms_plan.CMS_PLAN_STANDARD')	) ||
	                        		( $view->form->getSubForm('reserve')->getElement('reserve_cms_plan')->getValue() == config('constants.cms_plan.CMS_PLAN_STANDARD'))
	                        	) :
	                        ?>
                            	<li <?php echo $class; ?>><a href="/admin/map-option/edit?id=<?php  echo $view->form->getSubForm('basic')->getElement('id')->getValue(); ?>" class="btn-t-orange size-l">地図検索</a></li>
	                        <?php endif ;?>

                           <?php
								if (!($view->form->getSubForm('status')->getElement('cms_plan')->getValue() == NULL) && !($view->form->getSubForm('status')->getElement('cms_plan')->getValue() ==config('constants.cms_plan.CMS_PLAN_LITE')) || (!( $view->form->getSubForm('reserve')->getElement('reserve_cms_plan')->getValue() == config('constants.cms_plan.CMS_PLAN_LITE') ) && $view->form->getSubForm('reserve')->getElement('reserve_cms_plan')->getValue() > 0)) :
                            ?>
                                <li <?php echo $class; ?>><a <?php if (!$view->agency) :?> href="/admin/company/second-estate?company_id=<?php echo $view->form->getSubForm('basic')->getElement('id')->getValue(); ?>" <?php endif; ?> class="btn-t-orange size-l">2次広告自動公開設定</a></li>
                                <li <?php echo $class; ?>><a <?php if (!$view->agency) :?> href="/admin/company/estate-group?company_id=<?php echo $view->form->getSubForm('basic')->getElement('id')->getValue(); ?>" <?php endif; ?> class="btn-t-orange size-l">物件グループ設定</a></li>
                                <?php if (Original::checkPlanCanUseTopOriginal($view->form->getSubForm('status')->getElement('cms_plan')->getValue()) || Original::checkPlanCanUseTopOriginal($view->form->getSubForm('reserve')->getElement('reserve_cms_plan')->getValue())): ?>
                                    <li <?php echo $class; ?>><a <?php if (!$view->agency) :?> href="/admin/company/original-setting?company_id=<?php echo $view->form->getSubForm('basic')->getElement('id')->getValue(); ?>" <?php endif; ?> class="btn-t-orange size-l"><?php echo $view->original_setting_title; ?></a></li>
                                <?php endif; ?>
                            <?php endif ;?>
                            <?php if(isset($view->original_plan) || isset($view->original_edit)):?>
                                <?php if ($view->original_plan || $view->original_edit) :?>
                                    <li <?php if(isset($classEdit)) echo $classEdit ;?>><a href="/admin/company/original-edit?company_id=<?php echo $view->form->getSubForm('basic')->getElement('id')->getValue(); ?>" class="btn-t-orange size-l"><?php echo $view->original_edit_title; ?></a></li>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php endif ;?>
                        
                    </ul>
                </div>
                                
            <!-- section status -->
                <div class="section">
                    <h2>現在の契約情報</h2>
                    <table class="form-basic">
	                    <?php foreach ($view->form->getSubForm('status')->getElements() as $name => $element ):?>
		                    <tr>
	                            <th><?php echo $element->getLabel()?></th>
		                        <td>
	                                <?php 
	                                	switch ( $element->getName() )
		                                {
		                                  case 'cms_plan'	:
                                            echo $element->getValue() ? $view->cms_plan_list[ $element->getValue() ] : '-'	;
		                                  	break	;
		                                  default	:
		                                	echo h($element->getValue()) ;
		                                	break	;
	                                	}
	                                ?>
	                            </td>
		                    </tr>
	                    <?php endforeach ; ?>
                    </table>
                </div>

                <div class="section">
                    <h2>基本情報</h2>
                    <table class="form-basic">
                        <?php foreach ( $view->form->getSubForm('basic')->getElements() as $name => $element ):?>
                        <?php 
                            if( $element->getType() == "hidden" ) continue ; ?>
	                        <tr>
	                            <th><?php echo $element->getLabel()?></th>
	                            <td>
	                                <?php 
	                                  	switch ( $element->getName() )
	                                  	{
	                                      case 'contract_type' :
	                                    	echo $view->company_agree_list[	$element->getValue() ] ;
	                                    	break ;
	                                      default     :
	                                      	echo h($element->getValue()) ;
	                                      	break ;
	                                    }
	                                ?>
	                            </td>
	                        </tr>
                        <?php endforeach ; ?>
                    </table>
                </div>

                <div class="section">
                    <h2>契約情報予約</h2>
                    <table class="form-basic">
	                    <?php foreach ( $view->form->getSubForm('reserve')->getElements() as $name => $element ):?>
	                        <tr>
	                            <th><?php echo $element->getLabel()?></th>
	                            <td>
	                                <?php 
	                                    switch ( $element->getName() )
	                                    {
	                                      case 'contract_type' :
                                            
	                                  	    echo $view->company_agree_list[$element->getValue() ] ;
	                                        break ;
	                                      case 'reserve_cms_plan' :
                                            $view->cms_plan_list[ 0 ] = ''	;
	                                        echo $view->cms_plan_list[$element->getValue() ] ;
	                                        break ;
	                                      default:
	                                        echo h($element->getValue()) ;
	                                        break ;
	                                    }
	                                ?>
	                            </td>
	                       </tr>
	                    <?php endforeach ; ?>
                    </table>
                </div>

                <div class="section">
                    <h2>解約情報</h2>
                    <table class="form-basic">
                      <?php foreach ( $view->form->getSubForm('cancel')->getElements() as $name => $element ):?>
                         <tr>
                            <th><?php echo $element->getLabel(); ?></th>
                            <td><?php echo h($element->getValue()); ?></td>
                      <?php endforeach;?>
                    </table>
                </div>

                <?php if ($view->cms_plan != config('constants.cms_plan.CMS_PLAN_LITE')) { ?>
                <div class="section">
                    <h2>FDP情報</h2>
                    <table class="form-basic">
                    <?php foreach ($view->form->getSubForm('fdp')->getElements() as $name => $element):?>
                    
                    <?php if($element->getType() == "hidden") continue; ?>
                    <?php if($element->getType() == "text") $element->setAttributes(array("style" =>"width:60%","class" => "is-lock",
                    "disabled" => "is-disabled")); ?>

                    <tr<?php if($element->isRequired()): ?> class="is-require"<?php endif; ?>>
                        <th><span><?php echo $element->getLabel()?></span></th>
                        <td>
                            <?php $view->form->getSubForm('fdp')->form($name);?>
                            <?php foreach ($element->getMessages() as $error):?>
                            <p style="color:red;"><?php echo h($error)?></p>
                            <?php endforeach;?>

                        </td>
                    </tr>
                    <?php 
                        endforeach;?>
                    </table>
                </div>
                <?php } ?>

                <div class="section">
                    <h2>サーバーコンパネ情報</h2>
                    <table class="form-basic">
                    <?php foreach ($view->form->getSubForm('cp')->getElements() as $name => $element):?>
                    <?php if($element->getType() == "hidden") continue; ?>
                    <tr>
                        <th><?php echo $element->getLabel()?></th>
                        <td>
							<?php
								if ($name == "cp_password_used_flg" )
								{
									$element->getValue() == "0" ? print( "未設定" ) : print( "設定" ) ;
								}
								else
								{
									echo nl2br( h( $element->getValue() ) ) ;
								}
							?>
                        </td>
                    </tr>
                    <?php endforeach;?>
                    </table>
                </div>

                <div class="section">
                    <h2>CMS情報</h2>
                    <table class="form-basic">
                    <?php 
                    foreach ($view->form->getSubForm('cms')->getElements() as $name => $element):?>

                    <?php if($element->getType() == "hidden") continue; ?>
                    <tr>
                        <th><?php echo $element->getLabel()?></th>
                        <td>
                            <?php echo nl2br(h($element->getValue())); ?>
                        </td>
                    </tr>
                    <?php endforeach;?>
                    </table>
                </div>

                <div class="section">
                    <h2>FTP情報</h2>
                    <table class="form-basic">
                    <?php foreach ($view->form->getSubForm('ftp')->getElements() as $name => $element):?>

                    <?php if($element->getType() == "hidden") continue; ?>
                    <tr>
                        <th><?php echo $element->getLabel()?></th>
                        <td>
                            <?php if($element->getName() == "ftp_pasv_flg") : ?>
                                <?php if($element->getValue() != "" && $view->form->getSubForm('basic')->getElement('contract_type')->getValue() != config('constants.company_agreement_type.CONTRACT_TYPE_ANALYZE')) 
                                $view->pasv[0]='';
                                echo $view->pasv[$element->getValue()]; ?>
                            <?php elseif($element->getName() == "ftp_server_port") : ?>
                                <?php if($element->getValue() > 0) echo nl2br(h($element->getValue())); ?>
                            <?php else : ?>
                                <?php echo nl2br(h($element->getValue())); ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach;?>
                    </table>
                </div>

                <div class="section">
                    <h2>公開処理通知</h2>
                    <table class="form-basic">
                    <?php foreach ($view->form->getSubForm('pn')->getElements() as $name => $element):?>

                    <?php if($element->getType() == "hidden") continue; ?>
                    <tr>
                        <th><?php echo $element->getLabel()?></th>
                        <td>
							<?php if($element->getName() == "publish_notify") : ?>
								<?php
								if(isset($element->ValueOptions[ $element->getValue() ])) {
									echo $element->options[ $element->getValue() ];
								} else {
									echo $element->getValueOptions()[ '0' ];
                                }
								?>
                            <?php endif; ?>
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
                    <tr>
                        <th><?php echo $element->getLabel()?></th>
						<td>
                            <?php 
                                switch ( $element->getName() )
                                {
                                  case 'remarks' :
                                    echo nl2br( h( $element->getValue() ) ) ; 
                                    break ;
                                  default:
                                    echo h($element->getValue()) ;
                                    break ;
                                }
                            ?>
							<input type="hidden" name="other[<?php echo $element->getName(); ?>]" value="<?php echo $element->getValue(); ?>" /> 
						</td>
                    </tr>
                    <?php endforeach;?>
                    </table>
                </div>

            </div>
        </div>
        <?php if($view->form->getSubForm('basic')->getElement('domain')->getValue() == "" || $view->form->getSubForm('basic')->getElement("contract_type")->getValue() ==config('constants.company_agreement_type.CONTRACT_TYPE_DEMO')) : ?>
        <div class="section" style="text-align:right;padding-right:30px;">
            <?php if ( $view->current_hp ) : ?>
            <a class="btn-t-gray size-l" id="company_copy"   href="javascript:void(0)">このデモ会員をコピー</a>
            <?php else : ?>
            このデモ会員には、まだホームページのデータがありません。
            <?php endif ; ?>
            <a class="btn-t-gray size-l" id="company_delete" href="javascript:void(0)">削除</a>
        </div>
        <?php endif; ?>
@endsection
