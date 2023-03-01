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
        <div class="form-error">
          <ul>
          </ul>
        </div>
        <form action="<?php echo '/inquiry/'.$view->base['filename'].'/confirm/?window='.htmlspecialchars($view->window) ?>" method="post">
          <input type="hidden" name="token" value="<?php echo htmlspecialchars($view->token) ?>">
          <input type="hidden" name="target" value="<?php echo htmlspecialchars($view->window) ?>">
          <input type="hidden" name="page" value="<?php echo htmlspecialchars($view->page) ?>">
          <input type="hidden" name="bukken_id_csv" value="<?php echo htmlspecialchars($view->bukken_id_csv) ?>">
          <input type="hidden" name="bukken_type" value="<?php echo htmlspecialchars($view->bukken_type) ?>">
          <input type="hidden" name="contact_type" value="<?php echo htmlspecialchars($view->contact_type) ?>">
          <input type="hidden" name="special_id" value="<?php echo htmlspecialchars($view->special_id) ?>">
          <input type="hidden" name="recommend_flg" value="<?php echo htmlspecialchars($view->recommend_flg) ?>">
          <input type="hidden" name="from_searchmap" value="<?php echo htmlspecialchars($view->from_searchmap) ?>">
          <input type="hidden" name="estateListElement" value="<?php echo htmlspecialchars($view->estateListElement) ?>">

          <dl class="form-article">
            <?php foreach($view->contactItems as $item): ?>
            <?php if($item['item_key']=='contact_info') continue; ?>
            <?php if ($item['item_key'] =='peripheral' && !$view->api->isFDP) continue;?>
            <?php if ($item['item_key'] == 'hankyo_plus') continue; ?>
            <dt <?php if($item['must_flg']): ?>class="form-must"<?php endif ?>>
              <th><span><?php echo( htmlspecialchars($item['label']) ) ?></span></th>
            </dt>
            <dd>
              <td>
                <div class="form-error <?php echo htmlspecialchars($item['item_key']) ?>-err"></div>
                <?php // お問い合わせ内容 ?>
                <?php if($item['item_key']=='subject'): ?>
                <div class="form-error <?php echo htmlspecialchars($item['subject_more_item_key']) ?>-err"></div>
                <ul class="list-select-set subject-input">
                  <?php $no=1; foreach($item['option'] as $option): ?>
                  <?php if(empty($option))continue; ?>
                  <li>
                  <label for="<?php echo htmlspecialchars($item['item_key']).$no?>">
                  <span class="checkbox">
                  <input type="checkbox" name="<?php echo htmlspecialchars($item['item_key']) ?>[]" value="<?php echo $no?>" id="<?php echo htmlspecialchars($item['item_key']).$no?>" <?php if(in_array($no,$item['option_checked'])): ?> checked <?php endif ?> >
                  </span>
                  <span class="name">
                  <?php echo htmlspecialchars($option) ?>
                  </span>
                  </label>
                  </li>
                  <?php $no++; endforeach ?>
                </ul>
                <div class="form-article-textarea">
                <span>備考<br></span>
                <textarea class="form-textarea <?php echo htmlspecialchars($item['subject_more_item_key']) ?>-input" name="<?php echo htmlspecialchars($item['subject_more_item_key']) ?>" maxlength="2000" validatelength="1000" label="備考"><?php echo htmlspecialchars($item['subject_more_item_value']) ?></textarea>
                </div>
                <?php // 周辺エリア情報 - Peripheral Information ?>
                <?php elseif($item['item_key']=='peripheral'): ?>
                  <ul class="list-select-set">
                    <li>
                      <label for="<?php echo htmlspecialchars($item['item_key'])?>">
                      <span class="checkbox">
                      <input type="checkbox" name="<?php echo htmlspecialchars($item['item_key'])?>[]" value="1" <?php if ($item["option_checked"]): ?> checked <?php endif ?> id="<?php echo htmlspecialchars($item['item_key'])?>">
                      </span>
                      <span class="name">エリア情報の提供を希望する</span>
                      </label>
                      <span class="<?php echo htmlspecialchars($item['item_key'])?>">（ご提供する「エリア情報」の詳細は<a href="#" class="js-fdp-modal">こちら</a>をご覧ください）</span>
                    </li>
                  </ul>
                <?php // 連絡先 ?>
                <?php elseif($item['item_key']=='connection'): ?>
                    <div class="form-parts-tx">
                    <?php if(count($item['items'])>=1): ?><?php if (!empty($item['annotation'])):?><p class="tx-annotation">※<?php echo htmlspecialchars($item['annotation'])?></p><?php endif ?><?php endif ?>
                    <dl class="form-address">
                      <?php $no=1; foreach($item['items'] as $connection_item): ?>
                      <div class="form-error <?php echo htmlspecialchars($connection_item['item_key']) ?>-err"></div>
                      <dt><?php echo htmlspecialchars($connection_item['label']) ?></dt>
                      <dd class="person-tel">
                        <?php if($connection_item['item_key'] === 'person_tel'): ?>
                          <?php for($i=1; $i<=3; $i++ ): ?>
                            <input type="text" name="<?php echo htmlspecialchars($connection_item['item_key']) . $i?>"
                              class="input-tx3 <?php echo htmlspecialchars($connection_item['item_key']) ?>-input"
                              value="<?php echo htmlspecialchars($connection_item['item_value_' . $i]) ?>" maxlength="<?php echo htmlspecialchars($connection_item['maxlength'][$i - 1]) ?>" validatelength="<?php echo htmlspecialchars($connection_item['validatelength'][$i - 1]) ?>" label="<?php echo htmlspecialchars($connection_item['label']) ?>">
                            <?php if($i !== 3): ?>
                              <span class="line">-</span>
                            <?php endif; ?>
                          <?php endfor; ?>
                        <?php else: ?>
                          <input type="text" name="<?php echo htmlspecialchars($connection_item['item_key']) ?>"
                            class="input-tx1 <?php echo htmlspecialchars($connection_item['item_key']) ?>-input"
                            value="<?php echo htmlspecialchars($connection_item['item_value']) ?>" maxlength="<?php echo htmlspecialchars($connection_item['maxlength']) ?>" validatelength="<?php echo htmlspecialchars($connection_item['validatelength']) ?>" label="<?php echo htmlspecialchars($connection_item['label']) ?>"></dd>
                        <?php endif; ?>
                      </dd>
                      <?php $no++; endforeach ?>
                    </dl>
                <?php // テキスト ?>
                <?php elseif($item['type']=='text'): ?>
                <div class="form-parts-tx">
                <input type="text" name="<?php echo htmlspecialchars($item['item_key']) ?>" class="input-tx1 <?php echo htmlspecialchars($item['item_key']) ?>-input <?php if($view->contact->getItemUnitWord($item['item_key']) !== null) : ?>w-90<?php endif; ?>" value="<?php echo htmlspecialchars($item['item_value']) ?>" maxlength="<?php echo htmlspecialchars($item['maxlength']) ?>" validatelength="<?php echo htmlspecialchars($item['validatelength']) ?>" label="<?php echo htmlspecialchars($item['label']) ?>"> <?php echo $view->contact->getItemUnitWord($item['item_key']); ?>
                </div>

                <?php // テキストエリア ?>
                <?php elseif($item['type']=='textarea'): ?>
                <div class="form-article-textarea">
                <textarea class="form-textarea <?php echo htmlspecialchars($item['item_key']) ?>-input" name="<?php echo htmlspecialchars($item['item_key']) ?>" maxlength="<?php echo htmlspecialchars($item['maxlength']) ?>" validatelength="<?php echo htmlspecialchars($item['validatelength']) ?>" label="<?php echo htmlspecialchars($item['label']) ?>"><?php echo htmlspecialchars($item['item_value']) ?></textarea>
                </div> 
                <?php // チェックボックス ?>
                <?php elseif($item['type']=='checkbox'): ?>
                <ul class="list-select-set <?php echo htmlspecialchars($item['item_key']) ?>-input">
                  <?php $no=1; foreach($item['option'] as $option): ?>
                  <li>
                  <label for="<?php echo htmlspecialchars($item['item_key']).$no ?>">
                  <span class="checkbox">
                  <input type="checkbox" name="<?php echo $item['item_key'] ?>[]" value="<?php echo $no ?>" id="<?php echo htmlspecialchars($item['item_key']).$no ?>" <?php if(in_array($no,$item['option_checked'])): ?> checked <?php endif ?> >
                  </span>
                  <span class="name">
                  <?php echo htmlspecialchars($option) ?>
                  </span>
                  </label>
                  </li>
                  <?php $no++; endforeach ?>
                </ul>

                <?php // ラジオボタン ?>
                <?php elseif($item['type']=='radio'): ?>
                <ul class="list-select-set <?php echo htmlspecialchars($item['item_key']) ?>-input" >
                  <?php $no=1; foreach($item['option'] as $option): ?>
                  <li>
                  <label for="<?php echo htmlspecialchars($item['item_key']).$no ?>">
                  <span class="radio">
                  <input type="radio" name="<?php echo htmlspecialchars($item['item_key']) ?>[]" value="<?php echo $no ?>" id="<?php echo htmlspecialchars($item['item_key']).$no ?>" <?php if(in_array($no,$item['option_checked'])): ?> checked <?php endif ?> >
                  </span>
                  <span class="name">
                  <?php echo htmlspecialchars($option) ?>
                  </span>
                  </label>
                  </li>
                  <?php $no++; endforeach ?>
                </ul>

                <?php // プルダウン ?>
                <?php elseif($item['type']=='select'): ?>
                <div class="select-one">  
                <select name="<?php echo htmlspecialchars($item['item_key'])?>" class="<?php echo htmlspecialchars($item['item_key']) ?>-input">
                  <?php $no=1; foreach($item['option'] as $option): ?>
                  <option value="<?php echo $no?>" <?php if($no == $item['option_selected']): ?> selected <?php endif ?> ><?php echo htmlspecialchars($option) ?></option>
                  <?php $no++; endforeach ?>
                </select>
                </div>
                <?php endif ?>
              </td>
            </dd>
            <?php endforeach ?>
          </dl>

          <section class="form-list-article">
          <h3 class="article-heading sub">お問い合わせ物件</h3>
          <?php echo $view->estatelist ?>
          </section>
          <p class="element-tx tx-annotation">
              ※営利目的・商用利用は固くお断りいたします。
          </p>
          
          <?php // 反響プラス ?>
          <?php if($item["item_key"] == 'hankyo_plus') :?>
            <p class="element-tx">
              <input type="checkbox" name="hankyo_plus[]" value="1" id="<?php echo htmlspecialchars($item['item_key'])?>" <?php if ($item["option_checked"]): ?> checked <?php endif ?>>
              <label for="<?php echo htmlspecialchars($item['item_key'])?>">希望条件に近い最適な物件の紹介を受けられるように、閲覧履歴（価格、エリアなど）を提供する。</label>
            </p>
          <?php endif ?>

          <p class="link-form-privacy">
            お問い合わせを行う前に、<a href="/<?= $view->policyFilename; ?>/" target="_blank">プライバシーポリシー</a>を必ずお読みください。<br>
            プライバシーポリシーに同意いただいた場合は「上記にご同意の上 確認画面へ進む」のボタンをクリックしてください。
          </p>

          <input type="hidden" name="next">
          <p class="btn-lv3 btn-confirm"><input type="submit" value="上記にご同意の上 確認画面へ進む" name="next" class="btn-lv1"></p>
          
        </form>

      </section>
  <?php include_once ("contact/{$view->elementTemplate}");?>
