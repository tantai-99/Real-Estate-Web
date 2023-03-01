<script src="/sp/js/jquery-1.10.1.min.js"></script>
<script src="/sp/js/jquery.cookie.min.js"></script>
<script src="/sp/js/jquery.tile.js"></script>
<script src="/sp/js/slick.min.js"></script>
<!--<script src="//maps.googleapis.com/maps/api/js"></script>-->
<!--<script src="/sp/js/gmaps.js"></script>-->
<script src="/sp/js/fulltext-search.js"></script>
<script src="/sp/js/siggest.fixed.jquery.js"></script>
<script src="/sp/js/common.js"></script>
<script src="/sp/js/contact.js"></script>
<?php if(strpos($this->getTheme(), '_custom_color') !== false) : ?>
<script src="/sp/js/add_common.js"></script>
<?php endif; ?>
<script src="/sp/js/searchmap.js"></script>
<script type="text/javascript" src="//webfont.fontplus.jp/accessor/script/fontplus.js?eqiZ9eRgtMA%3D&box=P6jMsAp9OdA%3D&aa=1" charset="utf-8"></script>

<?php if ($view->apiConfig->get('dev')): ?>
  <script>
    console.log("----- access url start -----");
    console.log("<?= $view->devUrl?>");
    console.log("----- access url end -----");
    var devMode = true;
  </script>
<?php endif; ?>
<?php if($this->getTheme() == luxury01) : ?>
    <script>
        var luxury01 = true;
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

<!--object-fit（IE対策）-->
<script src="/sp/js/ofi.min.js"></script>
<script>
    window.addEventListener('load', function() {
        objectFitImages();
    });
</script>