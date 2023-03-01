(function (app) {

    'use strict';

    app.page.top = {};

    app.page.top.info = {};

    app.page.top._changed = false;

    app.page.top.load = function() {
        $('#slideshow').each(function () {
            var $section = $(this);
            var $input = $section.find('input[type="hidden"]');

            $section.on('click', '[data-id]', function () {
                var $this = $(this);
                $section.find('.is-active').removeClass('is-active');
                $this.addClass('is-active');
                $input.val($this.attr('data-id')).change();
            });

            if (!$section.find('[data-id].is-active:not(.is-hide)').length) {
                $section.find('[data-id]:not(.is-hide)').click();
            }
        });
        // Set width when setting init
        if (window.location.pathname.substring(1, window.location.pathname.lastIndexOf('/')) == "initialize") {
            $('.section-main-image').css('width', '943px');
        }
    }

    app.page.top.loadSlide = function (slideFlg, slideImgCount) {
        $('#main_image-count_slide').val(app.page.top.countImage());
        if (slideFlg && (app.page.top.countImage() > 1)) {
            $("[for=slide_show_flg_1]").click();
            $('.item-add .item-view-thumb').find('figcaption').remove();
            app.page.top.showSlide();
            $("#slideshow" ).resize();
        } else {
            $("[for=slide_show_flg_0]").click();
            app.page.top.hideSlide();
            if (app.page.top.countImage() == 1) {
                $('input[name="main_image[slide_show_flg]"]').attr('disabled', 'disabled');
            }
        }
        app.page.top.updateFigcaption();
    }

    app.page.top.addImgSlide = function (image) {
        $('#main_image-count_slide').val(app.page.top.countImage());

        if ($('#main_image-count_slide').val() > 1) {
            $('input[name="main_image[slide_show_flg]"]').removeAttr('disabled');
        }
        app.page.top.updateFigcaption();
    }

    app.page.top.removeImgSlide = function () {
        var slide_count = app.page.top.countImage();
        $('#main_image-count_slide').val(slide_count);
        if (slide_count < 2) {
            $("[for=slide_show_flg_0]").click();
            $('input[name="main_image[slide_show_flg]"]').attr('disabled', 'disabled');
            app.page.top.hideSlide();
        }
        app.page.top.updateFigcaption();
    }

    app.page.top.updateFigcaption = function() {
        var slider = $('input[name="main_image[slide_show_flg]"]:checked').val();
        if (slider == 0) {
            $('.item-add .item-view').each(function(index) {
                $(this).find('.item-view-thumb figcaption').remove();
                if (index > 0) {
                    var isDelete = $(this).find('.item-overlay a:last').hasClass("i-e-delete");
                    var isDisable = $(this).find('.item-overlay a:last').hasClass("is-disable")
                    if (isDelete && !isDisable) {
                        $(this).find('.item-view-thumb').append('<figcaption></figcaption>');
                    }
                }
            });
        }
    }

    app.page.top.hideSlide = function () {
        $('#slideshow').hide();
        $('#tooltip-slideshow').hide();
    }

    app.page.top.showSlide = function () {
        $('#slideshow').show();
        $('#tooltip-slideshow').show();
    }

    app.page.top.countImage = function () {
        var countImage = 0;
        $('.item-add .item-view').each(function() {
            $(this).find('.item-view-thumb figcaption').remove();
            var isDelete = $(this).find('.item-overlay a:last').hasClass("i-e-delete");
            var isDisable = $(this).find('.item-overlay a:last').hasClass("is-disable")
            if (isDelete && !isDisable) {
                countImage++;
            }
        });
        return countImage;
    }

})(app);

$(function () {

    'use strict';

    var slideFlg = $('input[name="main_image[slide_show_flg]"]');

    slideFlg.click(function() {
        var slide_count = app.page.top.countImage();
        if (slide_count < 2) {
            $("[for=slide_show_flg_0]").click();
            slideFlg.attr('disabled', 'disabled');
        } else {
            var slider = $('input[name="main_image[slide_show_flg]"]:checked').val();
            if (slider == 0) {
                app.page.top.updateFigcaption();
                app.page.top.hideSlide();
            } else {
                $('.item-add .item-view-thumb').find('figcaption').remove();
                app.page.top.showSlide();
                $("#slideshow" ).resize();
            }
        }
    });

});