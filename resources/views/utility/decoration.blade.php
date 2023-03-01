@extends('layouts.utility')

@section('title', __('お役立ち│ホームページ作成ツール'))

@section('style')
<link href="/css/utility.css" media="screen" rel="stylesheet" type="text/css">
<style>
.btn2 button {
  border    : 0px solid   ;
  padding   : 0px         ;
  cursor    : pointer     ;
}

.searchbt {
    display         : inline-block  ;
    width           : 150px         ;
    font-size       : 14px          ;
    line-height     : 40px          ;
    background-color: #000          ;
    color           : #FFF          ;
    text-align      : center        ;
    border-radius   : 4px           ;
    margin-bottom   : 30px          ;
    margin-top      : 30px          ;
    cursor          : pointer       ;
}
#templates-decoration {
    display: none;
}
</style>
@endsection

@section('script')
<script type="text/javascript" src="/js/libs/jquery-contained-sticky-scroll-min.js"></script>
<script type="text/javascript">
$(function () {
    $("#main-contents-body").addClass("contents-mainimg");
    $('#js-side').containedStickyScroll({
        duration:300
    });
});

$(function () {
    $('.step2').attr( 'onmouseover' , "this.style.opacity='0.7';" )   ;
    $('.step2').attr( 'onmouseout'  , "this.style.opacity='1.0';" )   ;
    var $flag = true  ;
    $('.all_check').click(function() {
        if( $flag ) {
            $flag = false ;
            $('.step1').prop({'checked':true})   ;
        } else {
            $flag = true  ;
            $('.step1').prop({'checked':false})  ;
        }
    });
	
	$('.step1').on('click', function() {
		if ( $('.step1:checked').length == $('.step1:input').length ) {
			$flag = false;
			$('.all_check').prop( {'checked':true } ) ;
		}else{
			$flag = true;
			$('.all_check').prop( {'checked':false} ) ;
		}
	});
	  
    $('#download').click( function() {
        if (!decorationError($('.list-result li').length > 0)) {
            return;
        }
        $('#result').submit()    ;
    });

    $('.step2').click( function() {
        var isCheck = false;
        var buttonVal = $(this).val();
        var data = {'step2' : buttonVal};
        var fields = $( "form :input" ).serializeArray();
        jQuery.each( fields, function( i, field ) {
            data[field.name] = field.value;
            if (field.value == '1') {
                isCheck = true;
            }
        });
        if (!decorationError(isCheck)) {
            return;
        }
        var $element = $('#templates-decoration div').clone();
        app.api('/utility/api-decoration', data, function(res) {
            var files = res.files;
            if (!decorationError(files.length > 0)) {
                return;
            }
            $('.list-result').removeAttr('style').empty();
            for(var i in files) {
                $element.find('img').attr('src', files[i]).attr('alt', files[i].substring(19, -4 ));
                $element.find('input').val(files[i]);
                $('.list-result').append($element.prop("outerHTML").replace('div', 'li'));
            }
            $('html,body').animate( { scrollTop:$('#download').offset().top }, '1' )	;
        })
    });

    function decorationError(condition) {
        var check = true;
        if (condition) {
            $('.error-decoration').hide();
        } else {
            $('.error-decoration').show();
            $('html,body').animate( { scrollTop: 0 }, '1' )	;
            check = false;
        }
        return check;
    }
});
<?php if ( ( count( @$view->files ) == 0 ) && ( count( @$view->prams ) > 4 ) ): ?>
$(function() {
    $('html,body').animate( { scrollTop: 0 }, '1' )	;
});
<?php endif ; ?>
</script>
@endsection

@section('content')

      <h2><a href="../utility">お役立ちコンテンツ</a></h2>
      <h3 class="heading-lv1">ボタン画像素材</h3>

      <div class="contents">
        <div class="material-contents">
          <p class="error-decoration" style="display:none;"><font color="red">「STEP.1」、「STEP.2」の順に指定して下さい。</font>
          <h4 class="heading-lv2" id="house"><em class="Bold">STEP.1</em> 使用したいボタンの種類をチェックして下さい（複数可）</h4>
          <form id="result" method="post" action="/plainFile/decoration-files#download">
            @csrf
          	<input type="hidden" name="kind" value="<?= @$view->prams[ 'kind' ] ?>" />
            <ul class="list-mainimg other-check">
               <li class="FN">
                   <label><input type="checkbox" class="all_check" /><span>全て選択</span></label>
               </li>
              <li>
                  <label><?php $view->form->form( 'nochara' ) ?>文字なし</label>
              </li>
            </ul>
            <ul class="list-mainimg">
              <?php foreach ( $view->form->getElements() as $name => $element ): ?>
                <?php if( $element->getType() == "hidden" ) continue ; ?>
                <?php if( $name == 'nochara'                                ) continue ; ?>
                <li>
                  <label for="<?= $element->getId() ?>"><?php $view->form->form( $name ) ?><?= $element->getLabel() ?></label>
                </li>
              <?php endforeach ; ?>
            </ul>

            <h4 class="heading-lv2"><em class="Bold">STEP.2</em> 使用したいデザイン（カラー）のボタンをクリックしてください</h4>
            <ul class="list-mainimg btn2">
              <li><button type="button" name="step2" class="step2" value="luxury1,black"   ><img src="/images/decoration/luxury1_bk.png"       alt="luxury1,black"    /></button></li>
              <li><button type="button" name="step2" class="step2" value="luxury1,blue"    ><img src="/images/decoration/luxury1_bl.png"       alt="luxury1,blue"     /></button></li>
              <li><button type="button" name="step2" class="step2" value="luxury1,green"   ><img src="/images/decoration/luxury1_gr.png"       alt="luxury1,green"    /></button></li>
              <li><button type="button" name="step2" class="step2" value="luxury1,orange"  ><img src="/images/decoration/luxury1_or.png"       alt="luxury1,orange"   /></button></li>
              <li><button type="button" name="step2" class="step2" value="luxury1,red"     ><img src="/images/decoration/luxury1_red.png"      alt="luxury1,red"      /></button></li>
              <li><button type="button" name="step2" class="step2" value="luxury2,black"   ><img src="/images/decoration/luxury2_bk.png"       alt="luxury2,black"    /></button></li>
              <li><button type="button" name="step2" class="step2" value="luxury2,blue"    ><img src="/images/decoration/luxury2_bl.png"       alt="luxury2,blue"     /></button></li>
              <li><button type="button" name="step2" class="step2" value="luxury2,green"   ><img src="/images/decoration/luxury2_gr.png"       alt="luxury2,green"    /></button></li>
              <li><button type="button" name="step2" class="step2" value="luxury2,orange"  ><img src="/images/decoration/luxury2_or.png"       alt="luxury2,orange"   /></button></li>
              <li><button type="button" name="step2" class="step2" value="luxury2,red"     ><img src="/images/decoration/luxury2_red.png"      alt="luxury2,red"      /></button></li>
              <li><button type="button" name="step2" class="step2" value="standard1,black" ><img src="/images/decoration/standard1_black.png"  alt="standard1,black"  /></button></li>
              <li><button type="button" name="step2" class="step2" value="standard1,blue"  ><img src="/images/decoration/standard1_blue.png"   alt="standard1,blue"   /></button></li>
              <li><button type="button" name="step2" class="step2" value="standard1,bg"    ><img src="/images/decoration/standard1_bg.png"     alt="standard1,bg"     /></button></li>
              <li><button type="button" name="step2" class="step2" value="standard1,green" ><img src="/images/decoration/standard1_green.png"  alt="standard1,green"  /></button></li>
              <li><button type="button" name="step2" class="step2" value="standard1,orange"><img src="/images/decoration/standard1_orange.png" alt="standard1,orange" /></button></li>
              <li><button type="button" name="step2" class="step2" value="standard1,red"   ><img src="/images/decoration/standard1_red.png"    alt="standard1,red"    /></button></li>
              <li><button type="button" name="step2" class="step2" value="standard2,black" ><img src="/images/decoration/standard2_black.png"  alt="standard2,black"  /></button></li>
              <li><button type="button" name="step2" class="step2" value="standard2,blue"  ><img src="/images/decoration/standard2_blue.png"   alt="standard2,blue"   /></button></li>
              <li><button type="button" name="step2" class="step2" value="standard2,bg"    ><img src="/images/decoration/standard2_bg.png"     alt="standard2,bg"     /></button></li>
              <li><button type="button" name="step2" class="step2" value="standard2,green" ><img src="/images/decoration/standard2_green.png"  alt="standard2,green"  /></button></li>
              <li><button type="button" name="step2" class="step2" value="standard2,orange"><img src="/images/decoration/standard2_orange.png" alt="standard2,orange" /></button></li>
              <li><button type="button" name="step2" class="step2" value="standard2,red"   ><img src="/images/decoration/standard2_red.png"    alt="standard2,red"    /></button></li>
              <li><button type="button" name="step2" class="step2" value="standard3,black" ><img src="/images/decoration/standard3_black.png"  alt="standard3,black"  /></button></li>
              <li><button type="button" name="step2" class="step2" value="standard3,blue"  ><img src="/images/decoration/standard3_blue.png"   alt="standard3,blue"   /></button></li>
              <li><button type="button" name="step2" class="step2" value="standard3,bg"    ><img src="/images/decoration/standard3_bg.png"     alt="standard3,bg"     /></button></li>
              <li><button type="button" name="step2" class="step2" value="standard3,green" ><img src="/images/decoration/standard3_green.png"  alt="standard3,green"  /></button></li>
              <li><button type="button" name="step2" class="step2" value="standard3,orange"><img src="/images/decoration/standard3_orange.png" alt="standard3,orange" /></button></li>
              <li><button type="button" name="step2" class="step2" value="standard3,red"   ><img src="/images/decoration/standard3_red.png"    alt="standard3,red"    /></button></li>
              <li><button type="button" name="step2" class="step2" value="simple1,black"   ><img src="/images/decoration/simple1_black.png"    alt="simple1,black"    /></button></li>
              <li><button type="button" name="step2" class="step2" value="simple1,blue"    ><img src="/images/decoration/simple1_blue.png"     alt="simple1,blue"     /></button></li>
              <li><button type="button" name="step2" class="step2" value="simple1,green"   ><img src="/images/decoration/simple1_green.png"    alt="simple1,green"    /></button></li>
              <li><button type="button" name="step2" class="step2" value="simple1,orange"  ><img src="/images/decoration/simple1_orange.png"   alt="simple1,orange"   /></button></li>
              <li><button type="button" name="step2" class="step2" value="simple1,red"     ><img src="/images/decoration/simple1_red.png"      alt="simple1,red"      /></button></li>
              <li><button type="button" name="step2" class="step2" value="simple2,black"   ><img src="/images/decoration/simple2_black.png"    alt="simple2,black"    /></button></li>
              <li><button type="button" name="step2" class="step2" value="simple2,blue"    ><img src="/images/decoration/simple2_blue.png"     alt="simple2,blue"     /></button></li>
              <li><button type="button" name="step2" class="step2" value="simple2,green"   ><img src="/images/decoration/simple2_green.png"    alt="simple2,green"    /></button></li>
              <li><button type="button" name="step2" class="step2" value="simple2,orange"  ><img src="/images/decoration/simple2_orange.png"   alt="simple2,orange"   /></button></li>
              <li><button type="button" name="step2" class="step2" value="simple2,red"     ><img src="/images/decoration/simple2_red.png"      alt="simple2,red"      /></button></li>
              <li><button type="button" name="step2" class="step2" value="pop1,black"      ><img src="/images/decoration/pop1_bk.png"          alt="pop1,black"       /></button></li>
              <li><button type="button" name="step2" class="step2" value="pop1,blue"       ><img src="/images/decoration/pop1_bl.png"          alt="pop1,blue"        /></button></li>
              <li><button type="button" name="step2" class="step2" value="pop1,green"      ><img src="/images/decoration/pop1_gr.png"          alt="pop1,green"       /></button></li>
              <li><button type="button" name="step2" class="step2" value="pop1,orange"     ><img src="/images/decoration/pop1_or.png"          alt="pop1,orange"      /></button></li>
              <li><button type="button" name="step2" class="step2" value="pop1,red"        ><img src="/images/decoration/pop1_red.png"         alt="pop1,red"         /></button></li>
              <li><button type="button" name="step2" class="step2" value="pop2,black"      ><img src="/images/decoration/pop2_bk.png"          alt="pop2,black"       /></button></li>
              <li><button type="button" name="step2" class="step2" value="pop2,blue"       ><img src="/images/decoration/pop2_bl.png"          alt="pop2,blue"        /></button></li>
              <li><button type="button" name="step2" class="step2" value="pop2,green"      ><img src="/images/decoration/pop2_gr.png"          alt="pop2,green"       /></button></li>
              <li><button type="button" name="step2" class="step2" value="pop2,orange"     ><img src="/images/decoration/pop2_or.png"          alt="pop2,orange"      /></button></li>
              <li><button type="button" name="step2" class="step2" value="pop2,red"        ><img src="/images/decoration/pop2_red.png"         alt="pop2,red"         /></button></li>
            </ul>

            <p class="Subbt"><span id="download" class="searchbt">一括ダウンロード</span></p>
            <p class="Setsumei">画像を「右クリック」⇒「名前をつけて画像を保存」でご自分のフォルダに保存することができます。</p>
            <ul class="list-mainimg">
              <li class="fsize14">2列用</li>
              <li class="fsize14">3列用</li>
              <li class="fsize14">サイドエリア用</li>
              <ul class="list-mainimg list-result" style="display:none">
                <?php foreach ( $view->files as $key => $url ): ?>
                   <li>
                    <label><img src="<?= $url ?>" alt="<?= substr( $url, 19, -4 ) ?>" /></label>
                    <input type="hidden" name="url[]" value="<?= $url ?>" />
                  </li>
                <?php endforeach ; ?>
              </ul>
            </ul>
          </form>           
        </div>

        <div class="side-contents" id="js-side">
          <ul class="link-page-inner">
            <li>
              <a href="?kind=_housing" class="DecoBorder">物件用</a>
            </li>
            <li>
              <a href="?kind=_misc">その他</a>
            </li>
          </ul>
          <p class="link-toppage">
            <a href="../utility">お役立ち<br>コンテンツTOP</a>
          </p>
		</div><!--Side-->
      </div><!-- /contents -->
      <div id="templates-decoration">
        <div>
            <label><img src="" alt="" /></label>
            <input type="hidden" name="url[]" value="" />
        </div>
      </div>
@endsection
