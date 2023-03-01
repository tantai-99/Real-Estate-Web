      <div class="element">
        <p class="element-tx">
        お問い合わせありがとうございます。<br>
        この内容でよろしければ「送信する」を、やり直す場合は「入力画面に戻る」をクリックしてください。
        </p>
        <div class="form-flow form-flow2">
          <ul>
            <li><span>入力</span></li><li><span>確認</span></li><li><span>送信完了</span></li>
          </ul>
        </div>
        <form action="<?php echo '/'.$view->base['filename'].'/edit/'?>" method="post">
          <input type="hidden" value="<?php echo '/'.$view->base['filename'].'/confirm/'?>" id="url">
          <table class="form-table">
            <?php foreach($view->contactItems as $item): ?>
            <?php if($item['item_key']=='contact_info') continue; ?>
            <?php if ($item['item_key'] == 'hankyo_plus') continue; ?>
            <tr>
              <th><?php echo( htmlspecialchars($item['label']) ) ?></th>
            </tr>
            <tr>
              <?php // お問い合わせ内容 ?>
              <?php if($item['item_key']=='subject'): ?>
              <td>
              <?php foreach($item['option_checked'] as $checked_no): ?>
              <?php echo( htmlspecialchars($item['option'][$checked_no-1]) ) ?>
                <input type="hidden" name="subject[]" value="<?php echo( htmlspecialchars($checked_no) ) ?>">
              <?php if ($checked_no !== end($item['option_checked'])): ?><br/><?php endif ?>
              <?php endforeach ?>
              <br/>
              <?php if(!is_null($item['subject_more_item_value']) && !empty($item['subject_more_item_value'])): ?>
              <?php echo( '<備考>'.'<br/>' ) ?>
              <?php echo( nl2br(htmlspecialchars($item['subject_more_item_value']))) ?>
                <input type="hidden" name="<?php echo( nl2br(htmlspecialchars($item['subject_more_item_key']))) ?>" value="<?php echo(htmlspecialchars($item['subject_more_item_value'])) ?>">
              <?php endif ?>
              </td>

              <?php // リクエスト内容 ?>
              <?php elseif($item['item_key']=='request' && ($item['type'] == 'checkbox' || $item['type']=='radio' || $item['type']=='select')): ?>
              <td>
              <?php // チェックボックス・ラジオボタン ?>
              <?php if($item['type']=='checkbox' || $item['type']=='radio'): ?>
              <?php foreach($item['option_checked'] as $checked_no): ?>
              <?php echo( htmlspecialchars($item['option'][$checked_no-1]) ) ?>
                <input type="hidden" name="<?php echo( nl2br(htmlspecialchars($item['item_key']))) ?>[]" value="<?php echo($checked_no) ?>">
              <?php if ($checked_no !== end($item['option_checked'])): ?><br/><?php endif ?>
              <?php endforeach ?>

              <?php // プルダウン ?>
              <?php elseif($item['type']=='select'): ?>
              <?php echo $item['option'][$item['option_selected']-1] ?>
                <input type="hidden" name="<?php echo htmlspecialchars($item['item_key'])?>" value="<?php echo $item['option_selected']?>">
              <?php endif; ?>

              <br/>
              <?php if(!is_null($item['request_more_item_value']) && !empty($item['request_more_item_value'])): ?>
              <?php echo( '<リクエスト備考>'.'<br/>' ) ?>
              <?php echo( nl2br(htmlspecialchars($item['request_more_item_value']))) ?>
                <input type="hidden" name="<?php echo( nl2br(htmlspecialchars($item['request_more_item_key']))) ?>" value="<?php echo(htmlspecialchars($item['request_more_item_value'])) ?>">
              <?php endif ?>
              </td>

              <?php // 連絡先 ?>
              <?php elseif($item['item_key']=='connection'): ?>
              <td>
              <?php $no=1; foreach($item['items'] as $connection_item): ?>
              <?php if(is_null($connection_item['item_value'])) continue;?>
              <?php echo htmlspecialchars($connection_item['label']) ?>：
              <?php echo (htmlspecialchars($connection_item['item_value'])) ?><br/>
                <?php if($connection_item['item_key'] === 'person_tel'): ?>
                  <?php for($i=1; $i<=3; $i++ ): ?>
                    <input type="hidden" name="<?php echo 'person_tel' . $i?>" value="<?php echo (htmlspecialchars($connection_item['item_value_' . $i])) ?>">
                  <?php endfor; ?>
                <?php endif; ?>
                <input type="hidden" name="<?php echo( nl2br(htmlspecialchars($connection_item['item_key']))) ?>" value="<?php echo (htmlspecialchars($connection_item['item_value'])) ?>">
                <?php $no++; endforeach ?>
              </td>

              <?php // 面積 ?>
              <?php elseif($item['item_key']=='property_exclusive_area' || $item['item_key']=='property_building_area' || $item['item_key']=='property_land_area'): ?>
                <td><?php if(!empty($item['item_value'])) : ?><?php echo( nl2br(htmlspecialchars($item['item_value'])) )?><?php echo $item['sub_option'][$item['sub_option_checked'][0]-1]?><?php endif; ?>
                  <input type="hidden" name="<?php echo( nl2br(htmlspecialchars($item['item_key'])) )?>" value="<?php echo $item['item_value']?>">
                  <input type="hidden" name="<?php echo htmlspecialchars($item['item_key'].'_sub') ?>[]" value="<?php echo $item['sub_option_checked'][0]?>">
                </td>

              <?php // 築年数 ?>
              <?php elseif($item['item_key']=='property_age'): ?>
              <td><?php if(!empty($item['item_value'])) : ?><?php echo $item['sub_option'][$item['sub_option_checked'][0]-1]?><?php echo( nl2br(htmlspecialchars($item['item_value'])) )?><?php echo $contact->getItemUnitWord($item['item_key']); ?><?php endif; ?>
                <input type="hidden" name="<?php echo htmlspecialchars($item['item_key'].'_sub') ?>[]" value="<?php echo $item['sub_option_checked'][0]?>">
                <input type="hidden" name="<?php echo( nl2br(htmlspecialchars($item['item_key'])) )?>" value="<?php echo $item['item_value']?>">
              </td>

              <?php // 間取り ?>
              <?php elseif($item['item_key']=='property_layout'): ?>
              <td>
			  	<?php if ($item['option_selected']>=2 && $item['sub_option_selected']>=2): ?>
			  	<?php echo $item['option'][$item['option_selected']-1] ?><?php echo $item['sub_option'][$item['sub_option_selected']-1] ?>
                <input type="hidden" name="<?php echo( htmlspecialchars($item['item_key']) ) ?>" value="<?php echo $item['option_selected']?>">
                <input type="hidden" name="<?php echo (htmlspecialchars($item['item_key'].'_sub'))?>" value="<?php echo $item['sub_option_selected']?>">
				<?php endif; ?>
              </td>

              <?php // テキスト ?>
              <?php elseif($item['type']=='text'): ?>
              <td><?php echo( htmlspecialchars($item['item_value']) ) ?><?php if(!empty($item['item_value'])) : ?><span><?php echo $contact->getItemUnitWord($item['item_key']); ?></span><?php endif; ?>
                <input type="hidden" name="<?php echo( htmlspecialchars($item['item_key']) ) ?>" value="<?php echo( htmlspecialchars($item['item_value']) ) ?>">
              </td>
              
              <?php // テキストエリア ?>
              <?php elseif($item['type']=='textarea'): ?>
              <td><?php echo( nl2br(htmlspecialchars($item['item_value'])) ) ?>
                <input type="hidden" name="<?php echo( nl2br(htmlspecialchars($item['item_key'])) ) ?>" value="<?php echo(htmlspecialchars($item['item_value']) ) ?>">
              </td>

              <?php // チェックボックス・ラジオボタン ?>
              <?php elseif($item['type']=='checkbox' || $item['type']=='radio'): ?>
              <td>
              <?php foreach($item['option_checked'] as $checked_no): ?>
              <?php echo( $item['option'][$checked_no-1] ) ?>
                <input type="hidden" name="<?php echo( nl2br(htmlspecialchars($item['item_key']))) ?>[]" value="<?php echo($checked_no) ?>">
                <?php if ($checked_no !== end($item['option_checked'])): ?><br/><?php endif ?>
              <?php endforeach ?>
              </td>

              <?php // プルダウン ?>
              <?php elseif($item['type']=='select'): ?>
              <td>
              <?php if ($item['option_selected']>=2): ?>
              <?php echo $item['option'][$item['option_selected']-1] ?>
                <input type="hidden" name="<?php echo htmlspecialchars($item['item_key'])?>" value="<?php echo $item['option_selected']?>">
              <?php endif; ?>
              </td>
              <?php endif ?>
            </tr>
            <?php endforeach ?>

          </table>
          <p class="tac btn-goback"><input type="submit" value="入力画面に戻る" name="back" class="btn-lv2"><input type="submit" value="送信する" name="send" class="btn-lv1"></p>
        </form>
      </div>
