<?php if (count($script_before_head_error) > 0) : ?>
    <div class="form-error">
        <ul>
            <?php foreach ($script_before_head_error as $msg) : ?>
                <li><?php echo htmlspecialchars($msg); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>