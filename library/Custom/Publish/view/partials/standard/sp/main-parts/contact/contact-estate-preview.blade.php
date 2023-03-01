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
    $hp = $view->page->getHp();
?>
  <!-- お問い合わせ 入力 -->
  <section class="article-contact">
    <h2 class="article-heading">
      物件のお問い合わせ
    </h2>
    <p class="form-enter-tx">
        お問い合わせは、下のフォームにご入力のうえ、「上記にご同意の上 確認画面へ進む」ボタンをクリックしてください。なお、内容により返信できない場合や、返答までに日数を要する場合がありますので、予めご了承ください。
    </p>
    <div class="form-flow form-flow1">
      <ul>
        <li><span>入力</span></li><li><span>確認</span></li><li><span>送信完了</span></li>
      </ul>
    </div>
    <div class="element">
      <div class="form-error">
        <ul>
          <?php if (isset($view->errors)):?>
          <?php foreach($view->errors as $error): ?>
          <li>※<?php echo htmlspecialchars($error) ?></li>
          <?php endforeach ?>
          <?php endif ?>
        </ul>
      </div>
    </div>


    <form>
      <dl class="form-article">
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
                            if($element[$choice] == ''){
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

                        for($i=0; $i<11; $i++){
                            $choice = 'choice_'.($i+1);
                            if(!array_key_exists($choice, $element) || empty($element[$choice])){
                                continue;
                            }
                            $options[] = $element[$choice];
                        }
                     }
                }
            ?>

            <dt <?php if($must_flg): ?>class="form-must"<?php endif ?>><?php echo( htmlspecialchars($label) ) ?>
            </dt>
            <dd>
              <td>

                <?php // お問い合わせ内容 ?>
                <?php if($itemKey=='subject'): ?>
                <ul class="list-select-set">
                    <?php $element = $values['form'][$val->getName()]; ?>
                    <?php $no=1; foreach($options as $option): ?>
                    <?php if(empty($option))continue; ?>
                    <li>
                    <label for="<?php echo htmlspecialchars($itemKey).$no?>">
                    <span class="checkbox">
                    <input type="checkbox" name="<?php echo htmlspecialchars($itemKey)?>[]" value="<?php echo $no?>" id="<?php echo htmlspecialchars($itemKey).$no?>">
                    </span>
                    <span class="name">
                    <?php echo htmlspecialchars($option) ?>
                    </span>
                    </label>
                    </li>
                    <?php $no++; endforeach ?>
                </ul>
                <p class="form-article-textarea"><span>備考</span><br><textarea></textarea></p>
                <?php // 周辺エリア情報 - Peripheral Information ?>
                <?php elseif($itemKey=='peripheral'): ?>
                  <ul class="list-select-set">
                    <li>
                      <label for="<?php echo htmlspecialchars($itemKey)?>">
                      <span class="checkbox">
                      <input type="checkbox" name="<?php echo htmlspecialchars($itemKey)?>[]" id="<?php echo htmlspecialchars($itemKey)?>">
                      </span>
                      <span class="name">エリア情報の提供を希望する</span>
                      </label>
                      <span class="<?php echo htmlspecialchars($itemKey)?>">（ご提供する「エリア情報」の詳細は<a href="#" class="js-fdp-modal">こちら</a>をご覧ください）</span>
                    </li>
                  </ul>
                <?php // 連絡先 ?>
                <?php elseif (array_key_exists($itemKey, $contactInfo)  && !$contactInfoProcessed) : ?>
                    <div class="form-parts-tx">
                    <?php if (!empty($contactInfoAnnotation)):?>
                      <p class="tx-annotation">※<?php echo htmlspecialchars($contactInfoAnnotation)?></p><?php endif ?>
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
                <div class="form-parts-tx">
                <input type="text" name="<?php echo htmlspecialchars($itemKey) ?>" <?php if($apiContactForm->getItemUnitWord($itemKey) !== null) : ?>style="width: 90%;"<?php endif; ?> class="input-tx1" value=""> <span ><?php echo $apiContactForm->getItemUnitWord($itemKey); ?></span>
                </div>

                <?php // テキストエリア ?>
                <?php elseif($type=='textarea'): ?>
                <?php if(!isset($apiElement)) $apiElement = $apiContactForm->getElement($itemKey); ?>
                <div class="form-article-textarea">
                <textarea class="form-textarea" name="<?php echo(htmlspecialchars($itemKey )) ?>" ><?php echo htmlspecialchars($apiElement->getValue()) ?></textarea>
                </div>

                <?php // チェックボックス ?>
                <?php elseif($type=='checkbox'): ?>
                <ul class="list-select-set">
                <span class="checkbox">
                  <?php $no=1; foreach($options as $option): ?>
                  <li>
                  <label for="<?php echo htmlspecialchars($itemKey).$no?>">
                  <span class="checkbox">
                  <input type="checkbox" name="<?php echo $itemKey?>[]" value="<?php echo $no?>" id="<?php echo htmlspecialchars($itemKey).$no?>" >
                  </span>
                  <span class="name">
                  <?php echo htmlspecialchars($option) ?>
                  </span>
                  </label>
                  </li>
                  <?php $no++; endforeach ?>
                </span>
                </ul>
                <?php // ラジオボタン ?>
                <?php elseif($type=='radio'): ?>
                <ul class="list-select-set">
                  <?php $no=1; foreach($options as $option): ?>

                  <li>
                  <label for="<?php echo htmlspecialchars($itemKey ).$no?>">
                  <span class="radio">
                  <input type="radio" name="<?php echo htmlspecialchars($itemKey ) ?>[]" value="<?php echo $no?>" id="<?php echo htmlspecialchars($itemKey ).$no?>" >
                  </span>
                  <span class="name">
                  <?php echo htmlspecialchars($option) ?>
                  </span>
                  </label>
                  </li>
                  <?php $no++; endforeach ?>
                </ul>

                <?php // プルダウン ?>
                <?php elseif($type=='select'): ?>
                <div class="select-one">
                <select name="<?php echo htmlspecialchars($itemKey)?>">
                  <?php $no=1; foreach($options as $option): ?>
                  <option value="<?php echo $no?>"><?php echo htmlspecialchars($option) ?></option>
                  <?php $no++; endforeach ?>
                </select>
                </div>
                <?php endif ?>
              </td>
            </dd>
            <?php endforeach ?>

      </dl>
      <p class="element-tx tx-annotation">
                ※営利目的・商用利用は固くお断りいたします。
      </p>
      
      <?php if($hp->hankyo_plus_use_flg): ?>
        <p class="element-tx">
          <input type="checkbox" name="hankyo-plus" value="hankyo-plus" checked>希望条件に近い最適な物件の紹介を受けられるように、閲覧履歴（価格、エリアなど）を提供する。
        </p>
      <?php endif ?>

      <p class="link-form-privacy">
          お問い合わせを行う前に、<a <?php echo $view->hpHref($view->privacypolicy); ?>>プライバシーポリシー</a>を必ずお読みください。<br> プライバシーポリシーに同意いただいた場合は「上記にご同意の上 確認画面へ進む」のボタンをクリックしてください。
      </p>
      <p class="btn-lv3 btn-confirm"><input type="submit" value="上記にご同意の上 確認画面に進む"></p>

	<?php 
        $config = getConfigs('v1api.api');
        $img_server = $config->img_server ;
	?>
      <section class="form-list-article">
        <h3 class="article-heading sub">お問い合わせ物件</h3>

        <div class="article-object">
          <label for="hoge1111" class="object-check"><input type="checkbox" id="hoge1111"></label>
          <div class="object-body">
            <div class="object-l">
              <figure class="object-thumb">
                <img src="<?= $img_server ?>/image_files/index/bukken/6954262274/2.jpeg?width=320&amp;height=320" alt="">
              </figure>
            </div>
            <div class="object-r">
              <p class="object-price">5万円</p>
              <dl class="object-data">
                <dt>管理費等：</dt>
                <dd>9,000円</dd>
                <dt>敷/礼：</dt>
                <dd>1ヶ月/1ヶ月</dd>
                <dt>間取り：</dt>
                <dd>ワンルーム</dd>
                <dt>面積：</dt>
                <dd>15.8m<sub>2</sub></dd>
                <dt>築年月：</dt>
                <dd>1975年5月(築40年1ヶ月)</dd>
              </dl>
              <p class="object-traffic">御茶ノ水/ＪＲ総武・中央緩行線<br></p>
              <p class="object-address">千代田区神田駿河台２丁目</p>
            </div>
          <!-- /object-body --></div>
        <!-- /article-object --></div>

        <div class="article-object">
          <label for="hoge33333" class="object-check"><input type="checkbox" id="hoge33333"></label>
          <div class="object-body">
            <div class="object-l">
              <figure class="object-thumb">
                <img src="<?= $img_server ?>/image_files/index/bukken/6954262274/2.jpeg?width=320&amp;height=320" alt="">
              </figure>
            </div>
            <div class="object-r">
              <p class="object-price">5万円</p>
              <dl class="object-data">
                <dt>管理費等：</dt>
                <dd>9,000円</dd>
                <dt>敷/礼：</dt>
                <dd>1ヶ月/1ヶ月</dd>
                <dt>間取り：</dt>
                <dd>ワンルーム</dd>
                <dt>面積：</dt>
                <dd>15.8m<sub>2</sub></dd>
                <dt>築年月：</dt>
                <dd>1975年5月(築40年1ヶ月)</dd>
              </dl>
              <p class="object-traffic">御茶ノ水/ＪＲ総武・中央緩行線<br></p>
              <p class="object-address">千代田区神田駿河台２丁目</p>
            </div>
          <!-- /object-body --></div>
        <!-- /article-object --></div>
      </section>

      </form>
      </section>
