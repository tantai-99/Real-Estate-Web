<script type="text/javascript">
    $(function() {
        if ($('form #maintain20200102').length) {
            var html = $('form #maintain20200102').html();
            $('.contents-form .inner').remove();
            $('.contents-form').append(html).removeClass('contents-form');
        }
    });
</script>
<div class="contents" id="maintain20200102">
    <div class="inner">
        <br><br>
        <div class="contents-main-1column">
            <h2 class="heading-lv1-1column" >メンテナンス中</h2>
            <div class="element">
                <p>
                    <b>ただいまメンテナンス中です。</b><br><br>
                    ご利用のみなさまにはご不便をおかけし、たいへん申し訳ございません。<br>メンテナンス終了までお待ちください。
                </p>
            </div>
        </div>
    </div>
</div>