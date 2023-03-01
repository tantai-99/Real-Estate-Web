<?php
use App\Repositories\HpPage\HpPageRepository;
use App\Repositories\MTheme\MThemeRepository;
if ($view->heading && is_null($view->element)) {
    $heading = h($view->heading);
} else {
    if ($view->heading) {
        $heading = h($view->heading);
    } else {
        $heading = $view->element->getValue('heading');
    }
}

if (strlen($heading) < 1) {
    return; // 空に設定された場合何も表示しない
}
if (!is_null($view->level) && is_null($view->element) || is_null($view->element->getValue('heading_type'))){
    $level = $view->level;
}else{
    $level = (int)$view->element->getValue('heading_type') ?: 1;
}

if ($view->registry('render:page_type_code') !== HpPageRepository::TYPE_TOP) {
    $level += 1;
}
$inside_division = $view->inside_division ? : false;

$tagName = 'h' . ($level + 1);
$cssClass = 'heading-lv' . $level;
if ($inside_division) {
    $cssClass = ' division-heading';
}

if (isset($view->isEstateContact) && $view->isEstateContact) {
    $cssClass = ' heading-lv1-1column';
}

//if(isset($view->hp->theme_id) && $view->hp->theme_id == 22 && 
if(isset($view->hp->theme_id) && MThemeRepository::getThemeName($view->hp->theme_id) == 'natural02_custom_color' && 
	((!is_null($view->element) && get_class($view->element) == "Library\Custom\Hp\Page\Parts\InfoList") || 
	$view->registry('render:page_type_code') == HpPageRepository::TYPE_INFO_INDEX || 
	$view->registry('render:page_type_code') == HpPageRepository::TYPE_INFO_DETAIL)) {
$cssClass = ' heading-lv' . $level. ' info';
}

$elementId = '';
if ($view->id){
    $elementId = 'id="' . $view->id . '"';
}

if ($level === 1) {
    $heading = "<span>{$heading}</span>";
}

if ($view->link) {
    $heading = "<a href='{$view->link}'>{$heading}</a>";
}

echo "<{$tagName} {$elementId} class='{$cssClass}'>{$heading}</{$tagName}>";
