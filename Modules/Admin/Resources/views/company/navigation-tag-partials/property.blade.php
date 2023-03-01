
<div class="section koma">
    <div class="section-title">
        <div class="left">物件コマタグ</div>
        <div class="right">
            <a href="<?php echo $params['redirectEditHousingBlock'];?>" class="underline">
                物件特集コマの編集はこちら
            </a>
        </div>
    </div>



    <table class="tb-basic tb-th-2 tb-body-bordered">
            <thead>
            <tr>
                <th>要素名</th>
                <th>オリジナルタグ</th>

                <th >要素名</th>
                <th >オリジナルタグ</th>

                <th >要素名</th>
                <th >オリジナルタグ</th>

            </tr>
            </thead>
            <tbody>

            <?php $keyTags = array("price3"); ?>
            <?php foreach($tags as $arr): ?>
                <tr>
                    <?php foreach($arr as $tag => $tagTitle): ?>
                        <td <?php if (in_array($tag, $keyTags)) :?>class="navigation-table-padding"<?php endif; ?>><?php echo $tagTitle;?></td>
                        <td>
                            <input type="hidden" value="{<?php echo $tag;?>}" />
                            <span><?php echo $tag;?></span>
                            <span class="copy-icon"><i class="fa fa-files-o" aria-hidden="true"></i></span>
                        </td>
                    <?php endforeach;?>
                    <?php if (count($arr) < 3): ?>
                        <?php for ($index =  count($arr); $index < 3; $index++) { ?>
                            <td></td>
                            <td></td>
                        <?php } ?>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>




            </tbody>
        </table>


</div>

<div class="clearfix"></div>