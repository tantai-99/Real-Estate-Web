
<div class="section notification">
    <div class="section-title">お知らせタグ</div>


            <table class="tb-basic tb-th-2 tb-body-bordered">
                <thead>
                <tr>
                    <th >要素名</th>
                    <th >オリジナルタグ</th>

                    <th >要素名</th>
                    <th >オリジナルタグ</th>

                    <th >要素名</th>
                    <th >オリジナルタグ</th>
                </tr>
                </thead>
                <tbody>

                <?php foreach($tags as $arr): ?>
                    <tr>
                        <?php foreach($arr as $tag => $tagTitle): ?>
                            <td><?php echo $tagTitle;?></td>
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