<script src="/pc/js/jquery-1.10.1.min.js"></script>
<script src="/pc/js/jquery.tile.js"></script>
<script src="/pc/js/jquery.lazyload.min.js"></script>
<script src="/pc/js/jquery.cookie.min.js"></script>
<!--<script src="//code.jquery.com/jquery-migrate-1.2.1.min.js"></script>-->
<!--<script src="//maps.googleapis.com/maps/api/js"></script>-->
<script src="/pc/js/slick.min.js"></script>
<!--<script src="/pc/js/gmaps.js"></script>-->
<script src="/pc/js/athome.jquery.checkbox.js"></script>
<script src="/pc/js/blowfish.js"></script>
<script src="/pc/js/fulltext-search.js"></script>
<script src="/pc/js/siggest.fixed.jquery.js"></script>
<script src="/pc/js/common.js"></script>
<?php if(strpos($this->getTheme(), '_custom_color') !== false) : ?>
<script src="/pc/js/add_common.js"></script>
<?php endif; ?>
<script src="/pc/js/contact.js"></script>
<script src="/pc/js/searchmap.js"></script>
<!--object-fit（IE対策）-->
<script src="/pc/js/ofi.min.js"></script>
<script>
  window.addEventListener('load', function() {
    objectFitImages();
  });
</script>

<!--[if lt IE 9]>
<script src="/pc/js/html5.js"></script>
<script src="/pc/js/ie9.js"></script>
<![endif]-->

<!--[if (gte IE 9)|!(IE)]><!-->
<script type="text/javascript" src="//webfont.fontplus.jp/accessor/script/fontplus.js?eqiZ9eRgtMA%3D&box=P6jMsAp9OdA%3D&aa=1" charset="utf-8"></script>
<!--<![endif]-->

<?php if ($view->apiConfig->get('dev')): ?>
  <script>
    console.log("----- access url start -----");
    console.log("<?= $view->devUrl?>");
    console.log("----- access url end -----");
    var devMode = true;
  </script>
<?php endif; ?>
<?php if (isset($view->api->breadCrumb)) :?>
<?php  $domain = $view->request->parse['scheme'].'://'.$view->request->parse['host']; ?>
<script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "BreadcrumbList",
        "itemListElement":
        [
            {
                "@type": "ListItem",
                "position": 1,
                "name": "ホーム",
                "item": "<?php echo $domain;?>"
            },
            <?php
            $i = 2;
            foreach ((array)$view->api->breadCrumb as $url=>$name) {
            ?>
            {
                <?php if ($url == '_empty_') :?>
                "@type": "ListItem",
                "position": <?php echo $i;?>,
                "name": "<?php echo $name;?>"
            }
                <?php else :?>
                "@type": "ListItem",
                "position": <?php echo $i;?>,
                "name": "<?php echo $name;?>",
                "item": "<?php echo $domain.$url;?>"
            },
                <?php endif; ?>
            <?php
            $i++;
            }   
            ?>
        ]
    }
</script>
<?php endif; ?>