<?php
$elementClass = 'element-parts-list';
$outside_header = $view->area->getColumnCount() === 1;
if ($outside_header) {
    echo '<section>';
    echo $view->partial('main-parts/heading.blade.php', array('element' => $view->element));
    $elementClass = 'element';
}
?>
    <div class="<?php echo $elementClass ?>">
        <?php if (!$outside_header) {
            echo '<section>';
            echo $view->partial('main-parts/heading.blade.php', array('element' => $view->element, 'inside_division' => true));
        }
        ?>

        <?php if ($view->area->getColumnCount() == 1) { ?>
        <div class="panorama" style="text-align: center;">
            <?php echo $view->element->getValue('code') ?>
        </div>
        <?php } else if ($view->area->getColumnCount() == 2) { ?>
        <div class="side-panorama" style="text-align: center;">
            <?php echo $view->element->getValue('code') ?>
        </div>
		<?php } else { ?>
        <div class="side-panorama" style="text-align: center;">
			<?php
				$valueString = $view->element->getValue('code');
				$dom = str_get_html($valueString);
				$iframes = $dom->find("iframe['@height']");
				if(count($iframes) == 1) {
					foreach ($iframes as $node) {
						$pheight = null;
						$pheight = $node->getAttribute('height');
						if(!is_null($pheight)) $node->setAttribute("style", "height:" . $pheight);
					}
					$valueString = $iframes->getDocument()->saveHTML();
				}
			?>
            <?php echo $valueString; ?>
        </div>
        <?php } ?>

        <?php if (!$outside_header) {
            echo '</section>';
        }
        ?>
    </div>
<?php
if ($outside_header)
    echo '</section>';
?>
