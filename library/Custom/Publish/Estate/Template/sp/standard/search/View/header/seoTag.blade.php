<?php
$tags = $view->seoTags;

$robotsContent = null;

if (in_array(SeoTags::NOINDEX, $tags)) {
  $robotsContent === null ? $robotsContent = SeoTags::NOINDEX : $robotsContent .= ','.SeoTags::NOINDEX;
};

if (in_array(SeoTags::NOFOLLOW, $tags)) {
  $robotsContent === null ? $robotsContent = SeoTags::NOFOLLOW : $robotsContent .= ','.SeoTags::NOFOLLOW;
};

if (in_array(SeoTags::NOARCHIVE, $tags)) {
  $robotsContent === null ? $robotsContent = SeoTags::NOARCHIVE : $robotsContent .= ','.SeoTags::NOARCHIVE;
};

$currentUrl   = "{$view->request->protcol}://{$view->request->domain}{$view->request->request_uri}";
$baseUrl      = "{$view->request->protcol}://{$view->request->domain}{$view->request->parse['path']}";
$canonicalUrl = "{$view->request->protcol}://{$view->request->domain}{$view->request->getCanonicalUri()}";

if (isset($view->api->info->current_page)) {
  $thisPage = $view->api->info->current_page;
  $next     = $thisPage + 1;
  $prev     = $thisPage - 1;
}

if (isset($view->api->info->total_page)) {
  $total = $view->api->info->total_page;
}
?>

<?php if ($robotsContent): ?>
  <meta name="robots" content="<?= $robotsContent; ?>">
<?php endif; ?>

<?php if (in_array(SeoTags::CANONICAL, $tags)): ?>
  <link rel="canonical" href="<?= $canonicalUrl; ?>">
<?php endif; ?>

<?php if (in_array(SeoTags::REL, $tags) && isset($thisPage) && isset($total) && $thisPage < $total): ?>
  <link rel="next" href="<?= "{$baseUrl}?page={$next}"; ?>">
<?php endif; ?>

<?php if (in_array(SeoTags::REL, $tags) && isset($thisPage) && isset($total) && 1 < $thisPage): ?>
  <link rel="prev" href="<?= ($prev > 1 ? "{$baseUrl}?page={$prev}" : $baseUrl); ?>">
<?php endif; ?>

<?php if (in_array(SeoTags::ALTERNATE, $tags)): ?>
  <link rel="alternate" media="only screen and (max-width: 640px)" href="<?= $currentUrl; ?>">
<?php endif; ?>
