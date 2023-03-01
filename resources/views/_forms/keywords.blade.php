<?php foreach ($this->element->getElementsByGroup() as $group): ?>
    <div class="inner input-keyword">
        <?php foreach ($group as $name => $element): ?>
            <span><?php $this->element->form($name) ?><span class="input-count"></span></span>
        <?php endforeach; ?>
    </div>
<?php endforeach; ?>

<div class="errors">
    <?php foreach ($this->element->getGroupErrors() as $message): ?>
        <p class="error"><?php echo $this->h($message) ?></p>
    <?php endforeach; ?>
</div>