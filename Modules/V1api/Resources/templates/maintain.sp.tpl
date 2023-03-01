<script type="text/javascript">
    $(function() {
        $('.fixed-pagefooter').remove();
        $('.cr').css({'padding-bottom': '0'});
        if ($('section').hasClass('article-contact')) {
            var html = $('.article-contact #maintain').html();
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
<section class="element-checklist" id="maintain">
    <h2 class="article-heading">メンテナンス中</h2>
    <section>
        <div class="maintain">
            <p>
                <b>ただいまメンテナンス中です。</b><br><br>
                ご利用のみなさまにはご不便をおかけし、たいへん申し訳ございません。<br>メンテナンス終了までお待ちください。
            </p>
        </div>
    </section>
</section>