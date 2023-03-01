<script type="text/javascript">
    $(function() {
        $('.fixed-pagefooter').remove();
        $('.cr').css({'padding-bottom': '0'});
        if ($('section').hasClass('article-contact')) {
            var html = $('.article-contact #maintain20210102').html();
            $('.contents section').remove();
            $('.contents').append(html);
        }
    });
</script>
<style type="text/css">
    .maintain {
        font-size: 11px;
        margin: 10px;
    }
</style>
<br><br>
<section class="element-checklist" id="maintain20210102">
    <h2 class="article-heading">メンテナンス中</h2>
    <section>
        <div class="maintain">
            <p>
                <b>ただいまメンテナンス中です。</b><br>
                【メンテナンス日時】2021年1月2日(土） 9:00～13:00<br>
                ※メンテナンスの終了時刻は前後する場合がございます。<br><br>
                ご利用のみなさまにはご不便をおかけし、たいへん申し訳ございません。<br>メンテナンス終了までお待ちください。
            </p>
        </div>
    </section>
</section>