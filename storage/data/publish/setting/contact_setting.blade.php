;
;お問い合わせ系設定ファイル
;
<?php $pageForm = $view->pageForm;?>
<?php $contactForm = $pageForm->form->form;?>
<?php $values = $contactForm->getValues();?>
<?php $formValues = $values['form']?>
<?php 
$mailCrypt   = new \Library\Custom\Crypt\ContactMail();
?>

[auth]
company_id = <?php  echo("'".$view->company->id."'".PHP_EOL); ?>
api_key = <?php echo("'".$view->companyAccount[0]->api_key."'".PHP_EOL); ?>

[base]
api_url = <?php echo($view->api_url .PHP_EOL); ?>
page_type_code = <?php echo ("'".$view->page['page_type_code']."'".PHP_EOL); ?>
page_id = <?php echo ("'".$view->page['id']."'".PHP_EOL); ?>
hp_id = <?php echo ("'".$view->page['hp_id']."'".PHP_EOL); ?>
filename = <?php echo ("'".$view->page['filename']."'".PHP_EOL); ?>

[inquiry_mail]
<?php for($no=1; $no <= $view->contactForm->getMailToCount(); $no++): ?>
<?php $key = 'notification_to_'.$no ?>
mail_to_<?php echo $no; ?> = <?php echo("'".$mailCrypt->encrypt($view->contactForm->getElement($key)->getValue())."'".PHP_EOL); ?>
<?php endfor; ?>
subject=<?php echo ("'".str_replace("'", '"APOSTROPHE_MARKS"', $view->contactForm->getElement('notification_subject')->getValue())."'".PHP_EOL); ?>

[reply_mail]
autoreply_flg = <?php echo ("'".$view->contactForm->getElement('autoreply_flg')->getValue()."'".PHP_EOL);?>
mail_from = <?php echo ("'".$mailCrypt->encrypt($view->contactForm->getElement('autoreply_from')->getValue())."'".PHP_EOL);?>
name_from = <?php echo ("'".str_replace("'", '"APOSTROPHE_MARKS"', $view->contactForm->getElement('autoreply_sender')->getValue())."'".PHP_EOL);?>
subject = <?php echo ("'".str_replace("'", '"APOSTROPHE_MARKS"', $view->contactForm->getElement('autoreply_subject')->getValue())."'".PHP_EOL);?>
body = "<?php echo (str_replace('"', "'QUOTATION_MARKS'", $view->contactForm->getElement('autoreply_body')->getValue()));?>"


<?php 
	// CMS側フォームを取得
    $contactParts = new \Modules\Api\Http\Form\Contact\ContactAbstract();
    
    //連絡先情報をまとめる
    $contactInfo = $view->apiContactForm->getContactInfo($view->pageForm);
    $contactInfoAnnotation = $view->apiContactForm->getContactInfoAnnotation($contactInfo);
    $contactInfoProcessed = false;
?>

<?php foreach( $view->contactForm->getSortedFormElements() as $val ):?>
<?php 
	$name = $val->getName();
	$element = $formValues[$name];
	if ($element['required_type'] == 3) continue;
	$must_flg = ($element['required_type'] == 1) ? true : false;
    $label = $val->getTitle();
    if (is_null($label)){
        $label = $element['item_title'];
    }
	$itemKey = $view->apiContactForm->getItemKey($name);

    if (array_key_exists($itemKey, $contactInfo) && !$contactInfoProcessed){
        $label = '連絡先';
        $must_flg = true;
    }
    if (array_key_exists($itemKey, $contactInfo) && $contactInfoProcessed){
        continue;
    }
    $options = array();
    if($val instanceof \Library\Custom\Hp\Page\SectionParts\Form\Element\Free){
        $type = $element['choices_type_code'];
        if ($type=='select' || $type=='checkbox' || $type=='radio'){
            for($i=0; $i < 10; $i++){
                $choice = 'choice_'.($i+1);
                if(!array_key_exists($choice, $element) || $element[$choice] == ''){
                     continue;
                }
                $options[] = $element[$choice];
            }
            if ($type=='select'){
                $options = array_merge(array('選択してください'), $options);
            }
        }

    //物件リクエスト
    }elseif($val instanceof \Library\Custom\Hp\Page\SectionParts\Form\Element\FreeItem && $itemKey!='request'){
        $type = $element['choices_type_code'];
        if ($type=='select' || $type=='checkbox' || $type=='radio'){
            for($i=0; $i < 10; $i++){
                $choice = 'choice_'.($i+1);
                if(!array_key_exists($choice, $element) || $element[$choice] == ''){
                     continue;
                }
                $options[] = $element[$choice];
            }
            if ($type=='select'){
                $options = array_merge(array('選択してください'), $options);
            }
        }

    }else{
        $apiElement = $view->apiContactForm->getElement($itemKey);
        if(get_class($apiElement) == 'Library\Custom\Form\Element\Text'){
            $type = 'text';
         }else if(get_class($apiElement) == 'Library\Custom\Form\Element\Textarea'){
            $type = 'textarea';
         }else if (get_class($apiElement) == 'Library\Custom\Form\Element\Select'){
            $type = 'select';
            $options = $apiElement->getValueOptions();
            $options = array_merge(array('選択してください'), $options);
         }else if (get_class($apiElement) == 'Library\Custom\Form\Element\Radio'){
            $type = 'radio';
            $options = $apiElement->getValueOptions();
         }else if (get_class($apiElement) == 'Library\Custom\Form\Element\MultiCheckbox'){
            $type = 'checkbox';
            $options = $apiElement->getValueOptions();
         }
         if ($itemKey=='subject'){
            $options = array();

            for($i=0; $i<11; $i++){
                $choice = 'choice_'.($i+1);
                if(!array_key_exists($choice, $element) || $element[$choice] == ''){
                    continue;
                }
                $options[] = $element[$choice];
            }
			$apiElement = $view->apiContactForm->getElement('subject_memo');
			$subjectMemo['item_key']='subject_memo';
			$subjectMemo['type']='textarea';
			$subjectMemo['label']='備考';
			$subjectMemo['options']=array();
         }
        //物件リクエスト
        elseif ($itemKey=='request'){
            $type = $element['choices_type_code'];
            if ($type=='select' || $type=='checkbox' || $type=='radio'){
                $options = array();
                for($i=0; $i < 10; $i++){
                    $choice = 'choice_'.($i+1);
                    if(!array_key_exists($choice, $element) || $element[$choice] == ''){
                         continue;
                    }
                    $options[] = $element[$choice];
                }
                if ($type=='select'){
                    $options = array_merge(array('選択してください'), $options);
                }
            }

            if ($element['detail_flg']=="1") {
                $apiElement = $view->apiContactForm->getElement('request_memo');
                $subjectMemo['item_key']='request_memo';
                $subjectMemo['type']='textarea';
                $subjectMemo['label']='リクエスト備考';
                $subjectMemo['options']=array();
            }
        }

    }
//exit;
?>
<?php if ($itemKey=='subject'):?>
[<?php echo ("'".'item_'.$itemKey."'") ?>]
item_no = <?php echo("'". $name."'".PHP_EOL); ?>
item_key = <?php echo("'".$itemKey."'".PHP_EOL); ?>
label = <?php echo ("'".$label."'".PHP_EOL); ?>
use_flg = true
must_flg = <?php echo ($must_flg) ? 'true'.PHP_EOL : 'false'.PHP_EOL;?>
type = <?php echo ("'".$type."'".PHP_EOL);?>
<?php foreach($options as $val) :?>
option[]=<?php echo ("'".$val."'".PHP_EOL);?>
<?php endforeach;?>
;[<?php echo('item_'.$itemKey.'_memo');?>]
sub_item_key=<?php echo($subjectMemo['item_key'].PHP_EOL);?>
sub_type=<?php echo ("'".$subjectMemo['type']."'".PHP_EOL);?>
sub_label=<?php echo ("'".$subjectMemo['label']."'".PHP_EOL);?>

<?php //物件リクエスト ?>
<?php elseif ($itemKey=='request'):?>
[<?php echo ("'".'item_'.$itemKey."'") ?>]
item_no = <?php echo("'". $name."'".PHP_EOL); ?>
item_key = <?php echo("'".$itemKey."'".PHP_EOL); ?>
label = <?php echo ("'".$label."'".PHP_EOL); ?>
use_flg = true
must_flg = <?php echo ($must_flg) ? 'true'.PHP_EOL : 'false'.PHP_EOL;?>
type = <?php echo ("'".$type."'".PHP_EOL);?>
<?php foreach($options as $val) :?>
option[]=<?php echo ("'".$val."'".PHP_EOL);?>
<?php endforeach;?>
;[<?php echo('item_'.$itemKey.'_memo');?>]
<?php if(isset($subjectMemo)):?>
sub_item_key=<?php echo($subjectMemo['item_key'].PHP_EOL);?>
sub_type=<?php echo ("'".$subjectMemo['type']."'".PHP_EOL);?>
sub_label=<?php echo ("'".$subjectMemo['label']."'".PHP_EOL);?>
<?php endif;?>

<?php elseif (array_key_exists($itemKey, $contactInfo)  && !$contactInfoProcessed) : ?>
[<?php echo ('item_contact_info') ?>]
item_key = 'contact_info'
label = <?php echo ("'".$label."'".PHP_EOL); ?>
use_flg = true
must_flg = <?php echo ($must_flg) ? 'true'.PHP_EOL : 'false'.PHP_EOL;?>
<?php if (!empty($contactInfoAnnotation)):?>
contactInfoAnnotation = <?php echo ("'".$contactInfoAnnotation."'".PHP_EOL); ?>
<?php endif ?>
<?php $no=1; foreach($contactInfo as $val): ?>
[<?php echo ("'".'item_'.$val['key']."'") ?>]
item_no  = <?php echo ("'".$val['name']."'".PHP_EOL); ?>
item_key = <?php echo ("'".$val['key']."'".PHP_EOL); ?>
label = <?php echo ("'".$val['label']."'".PHP_EOL); ?>
use_flg = true
must_flg = <?php echo ($val['required_type']==1) ? 'true'.PHP_EOL : 'false'.PHP_EOL;?>

<?php $no++; endforeach; ?>
<?php $contactInfoProcessed=true; ?>
<?php elseif($itemKey=='property_exclusive_area' || $itemKey=='property_building_area' || $itemKey=='property_land_area' || 
 $itemKey=='property_age' ||  $itemKey=='property_layout'): ?>
[<?php echo ("'".'item_'.$itemKey."'");?>]
item_no = <?php echo ("'".$name."'".PHP_EOL); ?>
item_key = <?php echo ("'".$itemKey."'".PHP_EOL); ?>
label = <?php echo ("'".$label."'".PHP_EOL); ?>
use_flg = true
must_flg = <?php echo ($must_flg) ? 'true'.PHP_EOL : 'false'.PHP_EOL;?>
type = <?php echo ("'".$type."'".PHP_EOL);?>
<?php foreach($options as $val) :?>
option[]=<?php echo ("'".$val."'".PHP_EOL);?>
<?php endforeach;?>

<?php $apiElement = $view->apiContactForm->getElement($itemKey."_sub");?>
<?php if(!is_null($apiElement)): ?>
<?php $options = $apiElement->getValueOptions();?>
<?php if(get_class($apiElement) == 'Library\Custom\Form\Element\Select'):?>
<?php $options = array_merge(array('選択してください'), $options);?>
<?php endif ?>
<?php foreach($options as $option): ?>
<?php if($option == '') continue; ?>
sub_option[]=<?php echo ("'".$option."'".PHP_EOL);?>
<?php endforeach ?>
<?php endif ?>
<?php else: ?>
[<?php echo ("'".'item_'.$itemKey."'"); ?>]
item_no = <?php echo ("'".$name."'".PHP_EOL); ?>
item_key = <?php echo ("'".$itemKey."'".PHP_EOL); ?>
label = <?php echo ("'".$label."'".PHP_EOL); ?>
use_flg = true
must_flg = <?php echo ($must_flg) ? 'true'.PHP_EOL : 'false'.PHP_EOL;?>
type = <?php echo ("'".$type."'".PHP_EOL);?>
<?php foreach($options as $val) :?>
option[]=<?php echo ("'".$val."'".PHP_EOL);?>
<?php endforeach;?>
<?php endif;?>
<?php endforeach;?>

[item_hankyo_plus]
<?php if($view->hankyo_plus_use_flg): ?>
item_key = 'hankyo_plus'
use_flg = <?php echo ($view->hankyo_plus_use_flg) ? 'true'.PHP_EOL : 'false'.PHP_EOL;?>
label = '閲覧履歴'
type = 'checkbox'
<?php endif;?>