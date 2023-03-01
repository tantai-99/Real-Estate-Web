<div class="section socials">
    <div class="section-title">広告タグ</div>



            <table class="tb-basic tb-th-2 tb-body-bordered">
                <thead>
                <tr>
                    <th>要素名</th>
                    <th>オリジナルタグ</th>

                    <th>要素名</th>
                    <th>オリジナルタグ</th>

                    <th>要素名</th>
                    <th>オリジナルタグ</th>
                </tr>
                </thead>
                <tbody>

                <?php foreach($tags as $arr): ?>
                    <tr>
                        <?php foreach($arr as $tag => $tagTitle): ?>
                            <td><?php echo htmlspecialchars($tagTitle);?></td>
                            <td>
                                <input type="hidden" value="{<?php echo $tag;?>}" />
                                <span><?php echo $tag;?></span>
                                <span class="copy-icon"><i class="fa fa-files-o" aria-hidden="true"></i></span>
                            </td>
                        <?php endforeach;?>
                    </tr>
                <?php endforeach; ?>

                </tbody>
            </table>


</div>


<div class="clearfix"></div>


