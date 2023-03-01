<?php
use Library\Custom\Form\Element;
use Modules\Api\Http\Form\Contact\ContactAbstract;
use App\Repositories\HpContactParts\HpContactPartsRepository;
	// ユーザーサイト側フォームを取得
    $apiContactForm = new ContactAbstract();
    $apiContactForm->init();

	// CMS側フォームを取得
	$page = $view->page->getRow();
    $formContact = \Library\Custom\Hp\Page::factory($view->hp, $page);
    $formContact->init();
    $formContact->load($load_from_request=true);
    $values = $formContact->form->form->getValues();

    //連絡先情報をまとめる
    $contactInfo = $apiContactForm->getContactInfo($formContact);
    $contactInfoAnnotation = $apiContactForm->getContactInfoAnnotation($contactInfo);
    $contactInfoProcessed = false;
?>
      <div class="element">
        <p class="element-tx">
        お問い合わせは、下のフォームにご入力のうえ、「上記にご同意の上 確認画面へ進む」ボタンをクリックしてください。<br>
        なお、内容により返信できない場合や、返答までに日数を要する場合がありますので、予めご了承ください。
        </p>
        <div class="form-flow form-flow1">
          <ul>
            <li><span>入力</span></li><li><span>確認</span></li><li><span>送信完了</span></li>
          </ul>
        </div>
        <div class="form-error">
          <ul>
            <?php if (isset($view->errors)):?>
            <?php foreach($view->errors as $error): ?>
            <li>※<?php echo htmlspecialchars($error) ?></li>
            <?php endforeach ?>
            <?php endif ?>
          </ul>
        </div>
        <form action="#" method="">
        <table class="form-table">
           <?php foreach($formContact->form->form->getSortedFormElements() as $val):
                $element = $values['form'][$val->getName()];
                if ($element['required_type'] == HpContactPartsRepository::REQUIREDTYPE_HIDDEN){continue;}
                $must_flg = ($element['required_type'] == 1) ? true : false;
                $label = $val->getTitle();
                if (is_null($label)){
                    $label = $element['item_title'];
                }
                $itemKey = $apiContactForm->getItemKey($val->getName());
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
                            if(!array_key_exists($choice, $element) || $element[$choice] ==''){
                                 continue;
                            }
                            $options[] = $element[$choice];
                        }
                        if ($type=='select'){
                            $options = array_merge(array('選択してください'), $options);
                        }
                    }
                }else{
                    $apiElement = $apiContactForm->getElement($itemKey);
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
                        for($i=0; $i<10; $i++){
                            $choice = 'choice_'.($i+1);
                            if(!array_key_exists($choice, $element) || $element[$choice] ==''){
                                continue;
                            }
                            $options[] = $element[$choice];
                        }
                     }
                     else if ($itemKey=='request'){
                        $type = $element['choices_type_code'];
                        // $options = array();
                        // for($i=0; $i<10; $i++){
                        //     $choice = 'choice_'.($i+1);
                        //     if(!array_key_exists($choice, $element) || $element[$choice] ===''){
                        //         continue;
                        //     }
                        //     $options[] = $element[$choice];
                        // }
                        if ($type=='select' || $type=='checkbox' || $type=='radio'){
                            for($i=0; $i < 10; $i++){
                                $choice = 'choice_'.($i+1);
                                if(!array_key_exists($choice, $element) || $element[$choice] ==''){
                                     continue;
                                }
                                $options[] = $element[$choice];
                            }
                            if ($type=='select'){
                                $options = array_merge(array('選択してください'), $options);
                            }
                        }
                    }
                }
            ?>

            <tr>
              <th <?php if($must_flg): ?>class="form-must"<?php endif ?>><span style="display:block;"><?php echo( htmlspecialchars($label) ) ?></span></th>
            </tr>
            <tr>
              <td>

                <?php // お問い合わせ内容 ?>
                <?php if($itemKey=='subject'): ?>
                <ul>
                    <?php $element = $values['form'][$val->getName()]; ?>
                    <?php $no=1; foreach($options as $option): ?>
                    <?php if(empty($option))continue; ?>
                    <li><input type="checkbox" name="<?php echo htmlspecialchars($itemKey)?>[]" value="<?php echo $no?>" id="<?php echo htmlspecialchars($itemKey).$no?>"><label for="<?php echo htmlspecialchars($itemKey).$no?>"><?php echo htmlspecialchars($option) ?></label></li>
                    <?php $no++; endforeach ?>
                </ul>
                <p class="tx-note">備考<br><textarea class="form-textarea" name="subject_memo" ></textarea></p>

                <?php // 物件リクエスト内容 ?>
                <?php elseif($itemKey=='request' && ($type == 'checkbox' || $type=='radio' || $type=='select')): ?>

                <?php // チェックボックス ?>
                <?php if($type=='checkbox'): ?>
                <ul>
                  <?php $no=1; foreach($options as $option): ?>
                  <?php if(empty($option))continue; ?>
                  <li><input type="checkbox" name="<?php echo $itemKey?>[]" value="<?php echo $no?>" id="<?php echo htmlspecialchars($itemKey).$no?>" ><label for="<?php echo htmlspecialchars($itemKey).$no?>"><?php echo htmlspecialchars($option) ?></label></li>
                  <?php $no++; endforeach ?>
                </ul>

                <?php // ラジオボタン ?>
                <?php elseif($type=='radio'): ?>
                <ul class="list-radio" >
                  <?php $no=1; foreach($options as $option): ?>
                  <?php if(empty($option))continue; ?>
                  <li><input type="radio" name="<?php echo htmlspecialchars($itemKey ) ?>[]" value="<?php echo $no?>" id="<?php echo htmlspecialchars($itemKey ).$no?>" ><label for="<?php echo htmlspecialchars($itemKey ).$no?>"><?php echo htmlspecialchars($option) ?></label></li>
                  <?php $no++; endforeach ?>
                </ul>

                <?php // プルダウン ?>
                <?php elseif($type=='select'): ?>
                <select name="<?php echo htmlspecialchars($itemKey)?>">
                  <?php $no=1; foreach($options as $option): ?>
                  <?php if(empty($option))continue; ?>
                  <option value="<?php echo $no?>"><?php echo htmlspecialchars($option) ?></option>
                  <?php $no++; endforeach ?>
                </select>
                <?php endif ?>

                <?php if($element['detail_flg'] == "1") : ?>
                <p class="tx-note">リクエスト備考<br><textarea class="form-textarea" name="request_memo" ></textarea></p>
                <?php endif; ?>

                <?php // 連絡先 ?>
                <?php elseif (array_key_exists($itemKey, $contactInfo)  && !$contactInfoProcessed) : ?>
                    <?php if (!empty($contactInfoAnnotation)):?><p class="element-tx tx-annotation">※<?php echo htmlspecialchars($contactInfoAnnotation)?></p><?php endif ?>
                    <dl class="form-address">
                      <?php $no=1; foreach($contactInfo as $val): ?>
                      <dt><?php echo htmlspecialchars($val['label']) ?></dt>
                      <dd class="person-tel">
                        <?php if($val['key'] === 'person_tel'): ?>
                          <?php for($i=1; $i<=3; $i++ ): ?>
                            <input type="text" name="" class="input-tx3" value="">
                            <?php if($i !== 3): ?>
                              <span class="line">-</span>
                            <?php endif; ?>
                          <?php endfor; ?>
                        <?php else: ?>
                          <input type="text" name="" class="input-tx1" value="">
                        <?php endif; ?>
                      </dd>
                      <?php $no++; endforeach ?>
                    </dl>
                    <?php $contactInfoProcessed=true; ?>

                <?php // 面積系（テキスト-ラジオ） ?>
                <?php elseif($itemKey=='property_exclusive_area' || $itemKey=='property_building_area' || $itemKey=='property_land_area' ): ?>
                        <div class="text-radio">
                          <input type="text" name="" class="input-tx3" value="">
                            <?php $apiElement = $apiContactForm->getElement($itemKey."_sub");?>
                                <?php if(!is_null($apiElement)): ?>
                                    <?php $options = $apiElement->getValueOptions();?>
                                    <ul class="list-radio">
                                        <?php $no=1; foreach($options as $option): ?>
                                        <?php if(empty($option))continue; ?>
                                        <li><input type="radio" name=<?php echo $itemKey."_sub" ?> value=$no id=<?php echo $itemKey."_sub" ?>><label for="<?php echo $itemKey."_sub" ?>"><?php echo $option?></label></li>
                                        <?php $no++; endforeach ?>
                                    </ul>
                                <?php endif ?>
                        </div>
                <?php // 築年数 ?>
                <?php elseif($itemKey=='property_age'): ?>
                        <div class="text-radio">
                          <?php $apiElement = $apiContactForm->getElement($itemKey."_sub");?>
                               <?php if(!is_null($apiElement)): ?>
                                   <?php $options = $apiElement->getValueOptions();?>
                                    <ul class="list-radio">
                                       <?php $no=1; foreach($options as $option): ?>
                                       <?php if(empty($option))continue; ?>
                                       <li><input type="radio" name=<?php echo $itemKey."_sub" ?> value=$no id=<?php echo $itemKey."_sub" ?>><label for="<?php echo $itemKey."_sub" ?>"><?php echo $option?></label></li>
                                     <?php $no++; endforeach ?>
                                   </ul>
                          <?php endif ?>
                          <input type="text" name="" class="input-tx3" value=""><?php echo $apiContactForm->getItemUnitWord($itemKey); ?>
                        </div>
                <?php // 間取り ?>
                <?php elseif($itemKey=='property_layout'): ?>
                <select name="<?php echo htmlspecialchars($itemKey)?>">
                  <?php $no=1; foreach($options as $option): ?>
                  <?php if(empty($option))continue; ?>
                  <option value="<?php echo $no?>"><?php echo htmlspecialchars($option) ?></option>
                  <?php $no++; endforeach ?>
                </select>
                <?php $apiElement = $apiContactForm->getElement($itemKey."_sub");?>
                    <?php if(!is_null($apiElement)): ?>
                        <?php $options = $apiElement->getValueOptions();?>
                        <?php $options = array_merge(array('選択してください'), $options);?>
                        <select name="<?php echo htmlspecialchars($itemKey)?>">
                          <?php $no=1; foreach($options as $option): ?>
                          <?php if(empty($option))continue; ?>
                          <option value="<?php echo $no?>"><?php echo htmlspecialchars($option) ?></option>
                          <?php $no++; endforeach ?>
                        </select>
                    <?php endif; ?>

                <?php // テキスト ?>
                <?php elseif($type=='text'): ?>
                <input type="text" name="<?php echo htmlspecialchars($itemKey) ?>" class="input-tx1 <?php if($apiContactForm->getItemUnitWord($itemKey) !== null) : ?>w-90<?php endif; ?>" value=""> <span><?php echo $apiContactForm->getItemUnitWord($itemKey); ?></span>

                <?php // テキストエリア ?>
                <?php elseif($type=='textarea'): ?>
                <textarea class="form-textarea" name="<?php echo(htmlspecialchars($itemKey )) ?>" ></textarea>

                <?php // チェックボックス ?>
                <?php elseif($type=='checkbox'): ?>
                <ul>
                  <?php $no=1; foreach($options as $option): ?>
                  <li><input type="checkbox" name="<?php echo $itemKey?>[]" value="<?php echo $no?>" id="<?php echo htmlspecialchars($itemKey).$no?>" ><label for="<?php echo htmlspecialchars($itemKey).$no?>"><?php echo htmlspecialchars($option) ?></label></li>
                  <?php $no++; endforeach ?>
                </ul>

                <?php // ラジオボタン ?>
                <?php elseif($type=='radio'): ?>
                <ul class="list-radio" >
                  <?php $no=1; foreach($options as $option): ?>
                  <li><input type="radio" name="<?php echo htmlspecialchars($itemKey ) ?>[]" value="<?php echo $no?>" id="<?php echo htmlspecialchars($itemKey ).$no?>" ><label for="<?php echo htmlspecialchars($itemKey ).$no?>"><?php echo htmlspecialchars($option) ?></label></li>
                  <?php $no++; endforeach ?>
                </ul>

                <?php // プルダウン ?>
                <?php elseif($type=='select'): ?>
                <select name="<?php echo htmlspecialchars($itemKey)?>">
                  <?php $no=1; foreach($options as $option): ?>
                  <option value="<?php echo $no?>"><?php echo htmlspecialchars($option) ?></option>
                  <?php $no++; endforeach ?>
                </select>
                <?php endif ?>
              </td>
            </tr>
            <?php endforeach ?>

          </table>
          <p class="element-tx tx-annotation">※営利目的・商用利用は固くお断りいたします。</p>
          <p class="link-form-privacy">お問い合わせを行う前に、<a <?php echo $view->hpHref($view->privacypolicy); ?>>プライバシーポリシー</a>を必ずお読みください。<br> プライバシーポリシーに同意いただいた場合は「上記にご同意の上 確認画面へ進む」のボタンをクリックしてください。</p>

          <p class="tac"><input type="submit" value="上記にご同意の上 確認画面へ進む" name="next" class="btn-lv1" disabled></p>
        </form>
      </div>
