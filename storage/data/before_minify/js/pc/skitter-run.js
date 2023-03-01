var $skitter=null;
var listsrc=[];
var _index=0;
$(function() {
    function checkIE() 
    {
      var sAgent = window.navigator.userAgent;
      var Idx = sAgent.indexOf("MSIE");
      if (Idx > 0) 
        return parseInt(sAgent.substring(Idx+ 5, sAgent.indexOf(".", Idx)));
      else if (!!navigator.userAgent.match(/Trident\/7\./)) 
        return 11;
      else
        return 0; 
    }
    var isIE=checkIE();
    function runSkitter() {
        $class_thumbs  =    app.slider.thumbs==true? 'skitter-thumbs':''
        $class_bars    =    app.slider.bars==true? 'skitter-bar':($class_thumbs? $class_thumbs:'skitter-nobar');
        $class_time    =    app.slider.classSpeed;
        $('.skitter').addClass($class_bars).addClass($class_time);
        if((typeof app.slider.effect === "object") && (app.slider.effect !== null)){
            $('.skitter').skitter({
                // show_randomly:true,
                with_animations:$.map(app.slider.effect, function(el) { return el }),
                thumbs: app.slider.thumbs,
                dots: (app.slider.dots || app.slider.bars),
                numbers: app.slider.numbers,
                interval: app.slider.speed,
                navigation:app.slider.navigation,
                thumbs_align:'left',
            });
        }
        else{
            $('.skitter').skitter({
                animation: app.slider.effect,
                thumbs: app.slider.thumbs,
                dots: (app.slider.dots || app.slider.bars),
                numbers: app.slider.numbers,
                interval: app.slider.speed,
                navigation:app.slider.navigation,
                thumbs_align:'left',
            });
        }
    }
    var skitter_lenght = $(".skitter img").length;

    $(".skitter img").each(function () {
        listsrc[_index++]=this.src;
    });
    
    $(".skitter img").one("load", function(e) {
        if(!isIE){
            var canvas = document.createElement("canvas");
            var sWidth = this.width;
            var sHeight = this.height;
            var sRate = sWidth / sHeight;
            var dRate = app.slider.width / app.slider.height ;
            var dWidth = app.slider.width;
            var dHeight = app.slider.height;
                canvas.width = dWidth;
                canvas.height = dHeight;
            var dy = 0;
            var dx = 0;
                rate = (dWidth / sWidth);
                dHeight = sHeight * rate;
            var ctx = canvas.getContext("2d");
            ctx.drawImage(this, 0 , 0, sWidth, sHeight, dx, dy , dWidth, dHeight);
            var dataurl = canvas.toDataURL("image/png");
            this.src = dataurl;
        }
        else{

            if(app.isPreview){
                filename = this.src.replace(/^.*=/, '');
                this.src='/image/hp-image-resize?image_id='+filename+'&width='+app.slider.width+'&height='+app.slider.height+'&swidth='+this.width+'&sheight='+this.height;
            }
            else{
                filename = this.src.replace(/^.*[\\\/]/, '');
                this.src='/images/customize-image-auto-resize/'+app.slider.width+'/'+app.slider.height+'/'+filename;
            }
        }
       
        skitter_lenght--;
        if (!skitter_lenght) {
            runSkitter();
        }
    }).each(function() {
        if (this.complete) $(this).load();
    });

    if (!app.isPreview) {
        if (app.slider.effect == 'circles' || app.slider.effect == 'hideBars' || (typeof app.slider.effect === "object")) {
            var elementLink, linkImage;
            $(document).on('mousemove', '.skitter .box_clone', function (e) {
                $('.skitter .container_skitter > a').each(function () {
                    if ($(this).children().length < 1) {
                        $(this).remove();
                    }
                });
                if ($('.box_clone').parent().is('a')) {
                    $('.box_clone').unwrap();
                }
                elementLink = $(this).parent().find('.image >a');
                linkImage = elementLink.attr('href');
                target = elementLink.attr('target');
                if (linkImage != '#' && (typeof linkImage != 'undefined')) {
                    if (app.slider.effect == 'hideBars' || (typeof app.slider.effect === "object" && $(this).children().length > 0)) {
                        $('.box_clone').wrap('<a href="' + linkImage +'" target="'+ target +'"></a>');
                    } else {
                        $('.box_clone').wrapAll('<a href="' + linkImage +'" target="'+ target +'"></a>');
                    }
                }
            });
        }
        $(document).on('mousemove', '.skitter .image_main', function() {
            var image = $(this).closest('.image');
            var link = image.find('a');
            if (link.length > 0 && (link.attr('href') == '#' || typeof link.attr('href') == 'undefined')) {
                link.css({'cursor': ' default', 'pointer-events': 'none'});
            }
        });
    }
});