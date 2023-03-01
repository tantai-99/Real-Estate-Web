// design function (add 201710 by fuku)
$(function(){
    // add span to display designed icons
    if($('.contents-left').find('.side-others-heading').length){
        $('.side-others-heading').wrapInner('<span>')
    }
    if($('.contents-left').find('.side-others-qr-heading').length){
        $('.side-others-qr-heading').wrapInner('<span>')
    }
    // asset list design =====================================================
    // fit image
    $('body.top .recommend-ph a').each(function(){
        var img = $(this).find('img').attr('src')
        $(this).find('img').css('opacity',0);
        $(this).css('background-image','url('+ img +')');
    });
    // match height
    $('body.top .element-recommend .recommend-item').matchHeight();
    // price design
    $('body.top .element-recommend .recommend-item .tx-price').each(function(){
        var str = $(this).text();
        var output = str.replace('万円','<small>万円</small>');
        $(this).html(output);
    });
    // wrap form table =====================================================
    if($('body').find('.form-table.element-table6').length){
        $('.form-table.element-table6').wrap('<div class="form-table-wrap"></div>');
    }
    // blank footer ========================================================
    if($('.guide-nav .inner').find('.guide-nav-element').length == 0){
        $('.guide-nav .inner').remove();
    }
    // empty element ========================================================
    if($('.element-btn-search').length){
        $('.element-btn-search input[type=submit]').each(function(){
            if($(this).hasClass('no')){
                $(this).wrap('<span class="add-icn disabled"></span>');
            }else{
                $(this).wrap('<span class="add-icn"></span>');
            }
        });
    }
    
    // half tone
        var elms = $('.element-error,   .contents-main-1column .heading-area label, .element-search-area-item.from-map .heading-area, .contents-main-1column .element-recommend, .contents-article .section-company .company-body, .page-special .element-recommend, .howto-see .heading-table, .element-comment, .quote, .element-login, .element-qa dt,.element-firstletter');
        $(elms).each(function(){
            var pdgTop = $(this).css('padding-top');
            var pdgLeft = $(this).css('padding-left');
            $(this).prepend('<div class="halftone"></div>')
            //$(this).prepend('<div class="halftone" style="top:-' + pdgTop  + '; left:-' + pdgLeft + '"></div>')
            console.log(pdgTop,pdgLeft);    
        });
        

});
/*
* jquery-match-height 0.7.2 by @liabru
* http://brm.io/jquery-match-height/
* License MIT
*/
!function(t){"use strict";"function"==typeof define&&define.amd?define(["jquery"],t):"undefined"!=typeof module&&module.exports?module.exports=t(require("jquery")):t(jQuery)}(function(t){var e=-1,o=-1,n=function(t){return parseFloat(t)||0},a=function(e){var o=1,a=t(e),i=null,r=[];return a.each(function(){var e=t(this),a=e.offset().top-n(e.css("margin-top")),s=r.length>0?r[r.length-1]:null;null===s?r.push(e):Math.floor(Math.abs(i-a))<=o?r[r.length-1]=s.add(e):r.push(e),i=a}),r},i=function(e){var o={
byRow:!0,property:"height",target:null,remove:!1};return"object"==typeof e?t.extend(o,e):("boolean"==typeof e?o.byRow=e:"remove"===e&&(o.remove=!0),o)},r=t.fn.matchHeight=function(e){var o=i(e);if(o.remove){var n=this;return this.css(o.property,""),t.each(r._groups,function(t,e){e.elements=e.elements.not(n)}),this}return this.length<=1&&!o.target?this:(r._groups.push({elements:this,options:o}),r._apply(this,o),this)};r.version="0.7.2",r._groups=[],r._throttle=80,r._maintainScroll=!1,r._beforeUpdate=null,
r._afterUpdate=null,r._rows=a,r._parse=n,r._parseOptions=i,r._apply=function(e,o){var s=i(o),h=t(e),l=[h],c=t(window).scrollTop(),p=t("html").outerHeight(!0),u=h.parents().filter(":hidden");return u.each(function(){var e=t(this);e.data("style-cache",e.attr("style"))}),u.css("display","block"),s.byRow&&!s.target&&(h.each(function(){var e=t(this),o=e.css("display");"inline-block"!==o&&"flex"!==o&&"inline-flex"!==o&&(o="block"),e.data("style-cache",e.attr("style")),e.css({display:o,"padding-top":"0",
"padding-bottom":"0","margin-top":"0","margin-bottom":"0","border-top-width":"0","border-bottom-width":"0",height:"100px",overflow:"hidden"})}),l=a(h),h.each(function(){var e=t(this);e.attr("style",e.data("style-cache")||"")})),t.each(l,function(e,o){var a=t(o),i=0;if(s.target)i=s.target.outerHeight(!1);else{if(s.byRow&&a.length<=1)return void a.css(s.property,"");a.each(function(){var e=t(this),o=e.attr("style"),n=e.css("display");"inline-block"!==n&&"flex"!==n&&"inline-flex"!==n&&(n="block");var a={
display:n};a[s.property]="",e.css(a),e.outerHeight(!1)>i&&(i=e.outerHeight(!1)),o?e.attr("style",o):e.css("display","")})}a.each(function(){var e=t(this),o=0;s.target&&e.is(s.target)||("border-box"!==e.css("box-sizing")&&(o+=n(e.css("border-top-width"))+n(e.css("border-bottom-width")),o+=n(e.css("padding-top"))+n(e.css("padding-bottom"))),e.css(s.property,i-o+"px"))})}),u.each(function(){var e=t(this);e.attr("style",e.data("style-cache")||null)}),r._maintainScroll&&t(window).scrollTop(c/p*t("html").outerHeight(!0)),
this},r._applyDataApi=function(){var e={};t("[data-match-height], [data-mh]").each(function(){var o=t(this),n=o.attr("data-mh")||o.attr("data-match-height");n in e?e[n]=e[n].add(o):e[n]=o}),t.each(e,function(){this.matchHeight(!0)})};var s=function(e){r._beforeUpdate&&r._beforeUpdate(e,r._groups),t.each(r._groups,function(){r._apply(this.elements,this.options)}),r._afterUpdate&&r._afterUpdate(e,r._groups)};r._update=function(n,a){if(a&&"resize"===a.type){var i=t(window).width();if(i===e)return;e=i;
}n?o===-1&&(o=setTimeout(function(){s(a),o=-1},r._throttle)):s(a)},t(r._applyDataApi);var h=t.fn.on?"on":"bind";t(window)[h]("load",function(t){r._update(!1,t)}),t(window)[h]("resize orientationchange",function(t){r._update(!0,t)})});
