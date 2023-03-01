var $skitter=null;
var listsrc=[];
var _index=0;
$(function() {
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
                stop_over: false
            }).find('.info_slide_thumb .image_number').each(function(index,element){
                setHeight();
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
                stop_over: false
            }).find('.info_slide_thumb .image_number').each(function(index,element){
                setHeight();
            });
        }
    }
    var skitter_lenght = $(".skitter img").length;
    
    $(".skitter img").each(function () {
        listsrc[_index++]=this.src;
    });

    $(".skitter img").one("load", function(e) {
        var canvas = document.createElement("canvas");
        var sWidth = this.width;
        var sHeight = this.height;
        var sRate = sWidth / sHeight;
        var dRate = app.slider.width / app.slider.height ;
        var dWidth = $(window).width() + 200;
        if (app.natural2) {
            dWidth = $(window).width() + 170;
        }
        var dHeight = dWidth/dRate;
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
        skitter_lenght--;
        if (!skitter_lenght) {
            runSkitter();
        }
    }).each(function() {
        if (this.complete) $(this).load();
    });
    if (!window.app.isPreview) {
        if (app.slider.effect == 'circles' || app.slider.effect == 'hideBars' || (typeof app.slider.effect === "object")) {
            $(document).on('click', '.skitter .box_clone', function () {
                if ($(this).find('a').length <= 0 || $(this).find('.box_clone').length > 0) {
                    var linkImage = $(this).parent().find('.image >a').attr('href');
                    var target = $(this).parent().find('.image >a').attr('target');
                    if (linkImage != '#') {
                        if(target == '_blank') { 
                            window.open(linkImage, target);
                        } else {
                            window.location = linkImage;
                        }
                    }
                }
            });
        }
    }
    function setHeight() {
        var rate = app.slider.height/app.slider.width;
        var width = $(window).width()/5;
        if (app.natural2) {
            width = ($(window).width() - 30)/5;
        }
        var height = width * rate;
        $('.container_thumbs').css({'width': $(window).width(), 'height': height}).find('.info_slide_thumb').css('height', height).find('.image_number').css({'height': height, 'width': width});
    }
    $(window).on('resize', function() {
        setHeight();
    });
});