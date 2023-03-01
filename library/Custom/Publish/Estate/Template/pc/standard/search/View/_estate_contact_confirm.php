<div class="contents contents-form">
  <div class="inner">

    <div class="contents-main-1column">
      <section>
        <h2 class="heading-lv1-1column">物件のお問い合わせ</h2>

        <!-- 内容確認-->
        <p class="form-flow form-flow2">
          内容確認
        </p>

        <section>
          <h3 class="heading-lv2-1column">お問い合わせの物件</h3>
          <div class="element element-form">
            <p class="element-tx">
              お問い合わせありがとうございます。<br>
              この内容でよろしければ「送信する」を、やり直す場合は「入力画面に戻る」をクリックしてください。
            </p>
            <div class="form-error">
              <ul>
                <?php if (isset($view->errors)): ?>
                  <?php foreach ($view->errors as $error): ?>
                    <li>※<?php echo htmlspecialchars($error) ?></li>
                  <?php endforeach ?>
                <?php endif ?>
              </ul>
            </div>

            <form action="<?php echo '/inquiry/'.$view->urlName.'/edit/?window='. htmlspecialchars($view->window) ?>" method="post">
              <input type="hidden" name="token" value="<?php echo htmlspecialchars($view->token) ?>">
              <input type="hidden" name="target" value="<?php echo  htmlspecialchars($view->window) ?>">
              <input type="hidden" name="page" value="<?php echo htmlspecialchars($view->page) ?>">
              <input type="hidden" name="bukken_id_csv" value="<?php echo htmlspecialchars($view->bukken_id_csv) ?>">
              <input type="hidden" name="bukken_type" value="<?php echo htmlspecialchars($view->bukken_type) ?>">
              <input type="hidden" name="contact_type" value="<?php echo htmlspecialchars($view->contact_type) ?>">
              <input type="hidden" name="special_id" value="<?php echo htmlspecialchars($view->special_id) ?>">
              <input type="hidden" name="recommend_flg" value="<?php echo htmlspecialchars($view->recommend_flg) ?>">
              <input type="hidden" name="from_searchmap" value="<?php echo htmlspecialchars($view->from_searchmap) ?>">
              <?php foreach ($view->selectedEstate as $bukken_id): ?>
                <input type="hidden" name="selectedEstate[]" value="<?php echo htmlspecialchars($bukken_id) ?>">
              <?php endforeach ?>
              <input type="hidden" name="estateListElement" value="<?php echo htmlspecialchars($view->estateListElement) ?>">
              <input type="hidden" value="<?php echo '/inquiry/'.$view->urlName.'/complete/'?>" id="url">

              <?php echo $view->estatelist ?>

              <table class="form-table element-table6">
                <?php foreach ($view->contactItems as $item): ?>
                  <?php if ($item['item_key'] == 'contact_info') continue; ?>
                  <?php if ($item['item_key'] =='peripheral' && !$view->api->isFDP) continue;?>
                  <tr>
                    <th><?php echo(htmlspecialchars($item['label'])) ?></th>

                    <?php // お問い合わせ内容 ?>
                    <?php if ($item['item_key'] == 'subject'): ?>
                      <td>
                        <?php foreach ($item['option_checked'] as $checked_no): ?>

                          <?php echo(htmlspecialchars($item['option'][$checked_no - 1])) ?>
                          <input type="hidden" name="<?php echo htmlspecialchars($item['item_key']) ?>[]" value="<?php echo( htmlspecialchars($checked_no) ) ?>">
                          <?php if ($checked_no !== end($item['option_checked'])): ?><br/><?php endif ?>
                        <?php endforeach ?>
                        <br/>
                        <?php if (!is_null($item['subject_more_item_value']) && !empty($item['subject_more_item_value'])): ?>
                          <?php echo('<備考>'.'<br/>') ?>
                          <?php echo(nl2br(htmlspecialchars($item['subject_more_item_value']))) ?>
                          <input type="hidden" name="<?php echo(htmlspecialchars($item['subject_more_item_key'])) ?>" value="<?php echo(htmlspecialchars($item['subject_more_item_value'])) ?>">
                        <?php endif ?>
                      </td>

                    <?php // 周辺エリア情報 - Peripheral Information ?>
                    <?php elseif ($item['item_key']=='peripheral' && $item['option_checked']): ?>
                    <td>
                    エリア情報の提供を希望する
                      <input type="hidden" name="<?php echo( htmlspecialchars($item['item_key']) ) ?>" value="1">
                    </td>

                    <?php // 反響プラス ?>
                    <?php elseif ($item["item_key"] == 'hankyo_plus'): ?>
                      <td>
                        <?php $hankyo_plus = isset($_POST['hankyo_plus']) ? "提供する" : "提供しない";
                        echo $hankyo_plus; ?>
                        <?php if (isset($_POST['hankyo_plus'])): ?>
                          <input type="hidden" name="<?php echo(htmlspecialchars($item['item_key'])) ?>" value="1">
                        <?php endif; ?>
                      </td>
                      
                      <?php // 連絡先 ?>
                    <?php elseif ($item['item_key'] == 'connection'): ?>
                      <td>
                        <?php $no = 1;
                        foreach ($item['items'] as $connection_item): ?>
                          <?php if (is_null($connection_item['item_value'])) continue; ?>
                          <span class="bold"><?php echo htmlspecialchars($connection_item['label']) ?>：</span>
                          <?php echo(htmlspecialchars($connection_item['item_value'])) ?><br/>
                          <?php if($connection_item['item_key'] === 'person_tel'): ?>
                              <?php for($i=1; $i<=3; $i++ ): ?>
                                  <input type="hidden" name="<?php echo 'person_tel' . $i?>" value="<?php echo (htmlspecialchars($connection_item['item_value_' . $i])) ?>">
                              <?php endfor; ?>
                          <?php endif; ?>
                          <input type="hidden" name="<?php echo(htmlspecialchars($connection_item['item_key'])) ?>" value="<?php echo(htmlspecialchars($connection_item['item_value'])) ?>">
                          <?php $no++; endforeach ?>
                      </td>

                      <?php // 面積 ?>
                    <?php elseif ($item['item_key'] == 'property_exclusive_area' || $item['item_key'] == 'property_building_area' || $item['item_key'] == 'property_land_area'): ?>
                      <td><?php echo(nl2br(htmlspecialchars($item['item_value']))) ?><?php echo $item['sub_option'][$item['sub_option_checked'][0] - 1] ?>
                        <input type="hidden" name="<?php echo( nl2br(htmlspecialchars($item['item_key'])) )?>" value="<?php echo $item['item_value']?>">
                        <input type="hidden" name="<?php echo htmlspecialchars($item['item_key'].'_sub') ?>[]" value="<?php echo $item['sub_option_checked'][0]?>">
                      </td>

                      <?php // 築年数 ?>
                    <?php elseif ($item['item_key'] == 'property_age'): ?><?php if(!empty($item['item_value'])) : ?><?php echo $view->contact->getItemUnitWord($item['item_key']); ?><?php endif; ?>
                      <td><?php echo $item['sub_option'][$item['sub_option_checked'][0] - 1] ?><?php echo(nl2br(htmlspecialchars($item['item_value']))) ?>
                        <input type="hidden" name="<?php echo htmlspecialchars($item['item_key'].'_sub') ?>[]" value="<?php echo $item['sub_option_checked'][0]?>">
                        <input type="hidden" name="<?php echo( nl2br(htmlspecialchars($item['item_key'])) )?>" value="<?php echo $item['item_value']?>">
                      </td>

                      <?php // 間取り ?>
                    <?php elseif ($item['item_key'] == 'property_layout'): ?>
                      <td>
                        <?php if ($item['option_selected'] >= 2 && $item['sub_option_selected'] >= 2): ?>
                          <?php echo $item['option'][$item['option_selected'] - 1] ?><?php echo $item['sub_option'][$item['sub_option_selected'] - 1] ?>
                          <input type="hidden" name="<?php echo( htmlspecialchars($item['item_key']) ) ?>" value="<?php echo $item['option_selected']?>">
                          <input type="hidden" name="<?php echo (htmlspecialchars($item['item_key'].'_sub'))?>" value="<?php echo $item['sub_option_selected']?>">
                        <?php endif; ?>
                      </td>

                      <?php // テキスト ?>
                    <?php elseif ($item['type'] == 'text'): ?>
                      <td><?php echo(htmlspecialchars($item['item_value'])) ?><?php if(!empty($item['item_value'])) : ?><?php echo $view->contact->getItemUnitWord($item['item_key']); ?><?php endif; ?>
                        <input type="hidden" name="<?php echo( htmlspecialchars($item['item_key']) ) ?>" value="<?php echo( htmlspecialchars($item['item_value']) ) ?>">
                      </td>

                      <?php // テキストエリア ?>
                    <?php elseif ($item['type'] == 'textarea'): ?>
                      <td><?php echo(nl2br(htmlspecialchars($item['item_value']))) ?>
                        <input type="hidden" name="<?php echo( nl2br(htmlspecialchars($item['item_key'])) ) ?>" value="<?php echo( htmlspecialchars($item['item_value']) ) ?>">
                      </td>

                      <?php // チェックボックス・ラジオボタン ?>
                    <?php elseif ($item['type'] == 'checkbox' || $item['type'] == 'radio'): ?>
                      <td>
                        <?php foreach ($item['option_checked'] as $checked_no): ?>
                          <?php echo($item['option'][$checked_no - 1]) ?>
                          <?php if ($checked_no !== end($item['option_checked'])): ?><br/><?php endif ?>
                          <input type="hidden" name="<?php echo( nl2br(htmlspecialchars($item['item_key']))) ?>[]" value="<?php echo($checked_no) ?>">
                        <?php endforeach ?>
                      </td>

                      <?php // プルダウン ?>
                    <?php elseif ($item['type'] == 'select'): ?>
                      <td>
                        <?php if ($item['option_selected'] >= 2): ?>
                          <?php echo $item['option'][$item['option_selected'] - 1] ?>
                          <input type="hidden" name="<?php echo htmlspecialchars($item['item_key'])?>" value="<?php echo $item['option_selected']?>">
                        <?php endif; ?>
                      </td>
                    <?php endif ?>
                  </tr>
                <?php endforeach ?>

              </table>
              <p class="tac btn-goback">
                <input type="submit" name="back" value="入力画面に戻る" class="btn-lv4">
                <input type="submit" value="送信する" name="send" class="btn-lv3">
              </p>
            </form>
          </div>
        </section>
      </section>
    </div>
  </div>
</div>
