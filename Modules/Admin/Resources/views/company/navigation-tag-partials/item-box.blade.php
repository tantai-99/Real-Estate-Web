<div class="section">
    <div class="section-title">ボックスエレメントタグ</div>
    <table class="tb-basic tb-th-left ">
        <thead>
        <tr>
            <th width="30%">要素名</th>
            <th width="20%">オリジナルタグ</th>
            <th width="50%">作業更新日</th>

        </tr>
        </thead>
        <tbody>

        <?php foreach($tags as $tag=>$name): ?>
        <tr>
            <td><?php echo $name;?></td>
            <td>
                <input type="hidden" value="{<?php echo $tag;?>}" />
                <span><?php echo $tag;?></span>
                <span class="copy-icon"><i class="fa fa-files-o" aria-hidden="true"></i></span>
            </td>
            <td>
                <?php
                $file = $di->getFile($tag);
                if($file && isset($file->updatedAt)){
                    echo $file->updatedAt;
                }
                ?>
            </td>
        </tr>
        <?php endforeach;?>

        </tbody>
    </table>
</div>

<div class="clearfix"></div>