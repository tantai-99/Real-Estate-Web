<script>
  $(function () {

    'use strict';

    /**
     * 入力画面に戻る処理
     */
    $('.returnToEdit').on('click', function () {
      var $btn;
      $btn = $('<input>').attr({type: 'submit', name: 'back', value: '入力画面に戻る'}).hide();
      $('form[name="formConfirm"]').append($btn);
      $btn.trigger('click');
      return false;
    });
  });
</script>

<section class="article-contact">

  <h2 class="article-heading">
    物件のお問い合わせ
  </h2>
  <p class="form-confirm-tx">
    お問い合わせありがとうございます。この内容でよろしければ「送信する」を、やり直す場合は「入力画面に戻る」をクリックしてください。
  </p>

    <div class="form-flow form-flow2">
      <ul>
        <li><span>入力</span></li><li><span>確認</span></li><li><span>送信完了</span></li>
      </ul>
    </div>

  <form name="formConfirm" action="<?php echo '/inquiry/'.$view->base['filename'].'/edit/?window='.htmlspecialchars($view->window) ?>" method="post">
    <input type="hidden" name="token" value="<?php echo htmlspecialchars($view->token) ?>">
    <input type="hidden" name="target" value="<?php echo htmlspecialchars($view->window) ?>">
    <input type="hidden" name="page" value="<?php echo htmlspecialchars($view->page) ?>">
    <input type="hidden" name="bukken_id_csv" value="<?php echo htmlspecialchars($view->bukken_id_csv) ?>">
    <input type="hidden" name="bukken_type" value="<?php echo htmlspecialchars($view->bukken_type) ?>">
    <input type="hidden" name="contact_type" value="<?php echo htmlspecialchars($view->contact_type) ?>">
    <input type="hidden" name="special_id" value="<?php echo htmlspecialchars($view->special_id) ?>">
    <input type="hidden" name="recommend_flg" value="<?php htmlspecialchars($view->recommend_flg) ?>">
    <input type="hidden" name="from_searchmap" value="<?php echo htmlspecialchars($view->from_searchmap) ?>">
    <?php foreach ($view->selectedEstate as $bukken_id): ?>
      <input type="hidden" name="selectedEstate[]" value="<?php echo htmlspecialchars($bukken_id) ?>">	<?php if($item['item_key']=='contact_info') continue; ?>
    <?php endforeach ?>
    <input type="hidden" name="estateListElement" value="<?php echo htmlspecialchars($view->estateListElement) ?>">
    <input type="hidden" value="<?php echo '/inquiry/'.$view->urlName.'/complete/'?>" id="url">


    <dl class="form-article confirm">
        <?php foreach($view->contactItems as $item): ?>
        <?php if($item['item_key']=='contact_info') continue; ?>
        <?php if ($item['item_key'] =='peripheral' && !$view->api->isFDP) continue;?>
        <dt <?php if($item['must_flg']): ?>class="form-must"<?php endif ?>>
          <?php echo( htmlspecialchars($item['label']) ) ?>
        </dt>
        <dd>
          <?php // お問い合わせ内容 ?>
          <?php if($item['item_key']=='subject'): ?>
          <ul>
            <?php foreach($item['option_checked'] as $checked_no): ?>
            <li>
            <?php echo( htmlspecialchars($item['option'][$checked_no-1]) ) ?>
              <input type="hidden" name="<?php echo htmlspecialchars($item['item_key']) ?>[]" value="<?php echo( htmlspecialchars($checked_no) ) ?>">
            </li>
            <?php endforeach ?>
            <?php if(!is_null($item['subject_more_item_value']) && !empty($item['subject_more_item_value'])): ?>
              <dd>
            <?php echo( '<備考>'.'<br/>' ) ?>
            <?php echo( nl2br(htmlspecialchars($item['subject_more_item_value']))) ?>
                <input type="hidden" name="<?php echo(htmlspecialchars($item['subject_more_item_key'])) ?>" value="<?php echo(htmlspecialchars($item['subject_more_item_value'])) ?>">
            </dd>
            <?php endif ?>
          </ul>

          <?php // 周辺エリア情報 - Peripheral Information ?>
          <?php elseif($item['item_key']=='peripheral' && $item['option_checked']): ?>
          <ul>
            <li>
              <?php echo( nl2br(htmlspecialchars($item['label']))) ?>
              <input type="hidden" name="<?php echo(htmlspecialchars($item['item_key'])) ?>" value="1">
            </li>
          </ul>
                              
          <?php // 反響プラス ?>
          <?php elseif ($item["item_key"] == 'hankyo_plus'): ?>
          <ul>
            <li>
              <?php $hankyo_plus = isset($_POST['hankyo_plus']) ? "提供する" : "提供しない";
              echo $hankyo_plus; ?>
              <?php if (isset($_POST['hankyo_plus'])): ?>
                <input type="hidden" name="<?php echo(htmlspecialchars($item['item_key'])) ?>" value="1">
              <?php endif; ?>
            </li>
          </ul>

          <?php // 連絡先 ?>
          <?php elseif($item['item_key']=='connection'): ?>
          <dl>
            <?php $no=1; foreach($item['items'] as $connection_item): ?>
            <?php if(is_null($connection_item['item_value'])) continue;?>
            <dt><?php echo htmlspecialchars($connection_item['label']) ?>：</dt>
            <dd><?php echo (htmlspecialchars($connection_item['item_value'])) ?></dd>
                <?php if($connection_item['item_key'] === 'person_tel'): ?>
                    <?php for($i=1; $i<=3; $i++ ): ?>
                        <input type="hidden" name="<?php echo 'person_tel' . $i?>" value="<?php echo (htmlspecialchars($connection_item['item_value_' . $i])) ?>">
                    <?php endfor; ?>
                <?php endif; ?>
              <input type="hidden" name="<?php echo(htmlspecialchars($connection_item['item_key'])) ?>" value="<?php echo(htmlspecialchars($connection_item['item_value'])) ?>">

              <?php $no++; endforeach ?>
          </dl>

          <?php // 面積 ?>
          <?php elseif($item['item_key']=='property_exclusive_area' || $item['item_key']=='property_building_area' || $item['item_key']=='property_land_area'): ?>
          <?php if ($item['option_selected']>=2 && $item['sub_option_selected']>=2): ?>
          <?php echo( nl2br(htmlspecialchars($item['item_value'])) )?><?php echo $item['sub_option'][$item['sub_option_checked'][0]-1]?>
              <input type="hidden" name="<?php echo( nl2br(htmlspecialchars($item['item_key'])) )?>" value="<?php echo $item['item_value']?>">	<?php echo( nl2br(htmlspecialchars($item['item_value'])) )?><?php echo $item['sub_option'][$item['sub_option_checked'][0]-1]?>
              <input type="hidden" name="<?php echo htmlspecialchars($item['item_key'].'_sub') ?>[]" value="<?php echo $item['sub_option_checked'][0]?>">
            <?php endif; ?>

          <?php // 築年数 ?>
          <?php elseif($item['item_key']=='property_age'): ?>
          <?php echo $item['sub_option'][$item['sub_option_checked'][0]-1]?><?php echo( nl2br(htmlspecialchars($item['item_value'])) )?>
            <input type="hidden" name="<?php echo htmlspecialchars($item['item_key'].'_sub') ?>[]" value="<?php echo $item['sub_option_checked'][0]?>">
            <input type="hidden" name="<?php echo( nl2br(htmlspecialchars($item['item_key'])) )?>" value="<?php echo $item['item_value']?>">
            <?php if(!empty($item['item_value'])) : ?><span><?php echo $view->contact->getItemUnitWord($item['item_key']); ?></span><?php endif; ?>

          <?php // 間取り ?>
          <?php elseif($item['item_key']=='property_layout'): ?>
          <?php echo $item['option'][$item['option_selected']-1] ?><?php echo $item['sub_option'][$item['sub_option_selected']-1] ?>
            <input type="hidden" name="<?php echo( htmlspecialchars($item['item_key']) ) ?>" value="<?php echo $item['option_selected']?>">
            <input type="hidden" name="<?php echo (htmlspecialchars($item['item_key'].'_sub'))?>" value="<?php echo $item['sub_option_selected']?>">


            <?php // テキスト ?>
          <?php elseif($item['type']=='text'): ?>
          <?php echo( htmlspecialchars($item['item_value']) ) ?><?php if(!empty($item['item_value'])) : ?><span><?php echo $view->contact->getItemUnitWord($item['item_key']); ?></span><?php endif; ?>
            <input type="hidden" name="<?php echo( htmlspecialchars($item['item_key']) ) ?>" value="<?php echo( htmlspecialchars($item['item_value']) ) ?>">

            <?php // テキストエリア ?>
          <?php elseif($item['type']=='textarea'): ?>
          <?php echo( nl2br(htmlspecialchars($item['item_value'])) ) ?>
            <input type="hidden" name="<?php echo( nl2br(htmlspecialchars($item['item_key'])) ) ?>" value="<?php echo( htmlspecialchars($item['item_value']) ) ?>">

            <?php // チェックボックス・ラジオボタン ?>
          <?php elseif($item['type']=='checkbox' || $item['type']=='radio'): ?>
          <ul>
            <?php foreach($item['option_checked'] as $checked_no): ?>
            <li><?php echo( $item['option'][$checked_no-1] ) ?></li>
              <input type="hidden" name="<?php echo( nl2br(htmlspecialchars($item['item_key']))) ?>[]" value="<?php echo($checked_no) ?>">
            <?php endforeach ?>
          </ul>

          <?php // プルダウン ?>
          <?php elseif($item['type']=='select'): ?>
          <ul>
            <?php if ($item['option_selected']>=2): ?>
            <li><?php echo $item['option'][$item['option_selected']-1] ?></li>
              <input type="hidden" name="<?php echo htmlspecialchars($item['item_key'])?>" value="<?php echo $item['option_selected']?>">
              <?php endif; ?>
          </ul>
          <?php endif ?>
        </dd>
        <?php endforeach ?>

      </dl>

      <section class="form-list-article">
      <div class="article-object">
      <h3 class="article-heading sub">お問い合わせ物件</h3>
      <?php echo $view->estatelist ?>
      <!-- /article-object --></div>
      </section>

      <div class="btn-send">
        <ul>
          <li class="btn-back"><input type="submit" name="back" value="入力画面に戻る" class="btn-lv2 returnToEdit"></li>
          <li class="btn-lv3"><input type="submit" value="送信する" name="send"></li>
        </ul>
      </div>
    </form>

</section>