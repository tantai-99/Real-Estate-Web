
    <div class="breadcrumb">
        <ul>
            <li itemscope itemtype="http://data-vocabulary.org/Breadcrumb"><a href="/">ホーム</a></li>
        </ul>
    </div>
 
    <section>
        <h2 class="article-heading">
            <span class="article-name">{$市区名}の{$物件種目}の物件一覧</span>
        </h2>

        <!--登録0の場合 -->
        <p class="tx-nohit">申し訳ございません。<br>
            お探しの条件に該当する物件は、現在のところ登録がありません。<br>
            検索条件を変更して、再検索をお願いします。<br><br>
            <span class="btn-request"></span>
        </p>
       <!--end 登録0の場合 -->

        <p class="total-count"><span class="bold">該当件数</span> <span class="total-num">30,000件</span>
            10,000～10,000件を表示</p>
        <div class="list-change">
            <p class="sort-select">表示順：
                <select>
                    <option value="1">賃料が安い順</option>
                    <option value="2">築年年数が浅い順</option>
                </select>
            </p>
            <ul class="btn-narrow-down">
                <li><a href="/chintai/tokyo/condition/">条件を絞り込む</a></li>
                <li><a href="/chintai/tokyo/">市区郡を変更</a></li>
            </ul>
        </div>

        <div class="search-freeword element-input-search-result">
            <datalist class="suggesteds" id="suggesteds"></datalist>
            <input type="text" placeholder="例： 12.2万円以下 和室" name="search_filter[fulltext_fields]" value="" autocomplete="off" list="suggesteds">
            <a href="/chintai/tokyo/result/" class="search-freeword">検索</a>
        </div>

        <div class="article-object" data-bukken-no="12345">
            <label class="object-check"><input type="checkbox"></label>
            <div class="object-body">
                <a href="#">
                    <div class="comment-pro">
                        <dl>
                            <dt>おすすめコメント</dt>
                            <dd>ここに「おすすめコメント」を掲載します。</dd>
                        </dl>
                    </div>
                    <div class="object-l">
                        <figure class="object-thumb">
                            <img src="http://{＄img_server}/image_files/index/bukken/6954262274/2.jpeg?width=320&amp;height=320" alt="">
                        </figure>
                        <ul class="icon-condition">
                            <li><img src="/sp/imgs/icon_not_person.png" alt=""></li>
                        </ul>
                    </div>
                    <div class="object-r">
                        <p class="object-price">4.5万円</p>
                        <dl class="object-data">
                            <dt>管理費等：</dt>
                            <dd>9,000円</dd>
                            <dt>敷/礼：</dt>
                            <dd>1ヶ月/1ヶ月</dd>
                            <dt>間取り：</dt>
                            <dd>ワンルーム</dd>
                            <dt>面積：</dt>
                            <dd>15.8m<sub>2</sub></dd>
                            <dt>築年月：</dt>
                            <dd>1975年5月(築40年1ヶ月)</dd>
                        </dl>
                        <p class="object-traffic">JR山手線 渋谷 徒歩10分</p>
                        <p class="object-address">千代田区東神田2丁目千代田区東神田2丁目</p>
                    </div>
                </a>
                <!-- /object-body --></div>
            <!-- /article-object --></div>

        <p class="btn-all-contact"><a href="/inquiry/uri-kyojuu/edit/">チェックした物件をまとめて問合せ</a></p>

        <div class="article-pager">
            <ul>
                <li class="pager-prev"><a href="#">前へ</a></li>
                <li class="count-num"><span>10,000～10,000件/30,000件</span></li>
                <li class="pager-next"><span>次へ</span></li>
            </ul>
        </div>

        <div class="btn-term-change">
            <ul>
                <li><a href="/chintai/tokyo/condition/">条件を絞り込む</a></li>
                <li><a href="/chintai/tokyo/">市区郡を変更</a></li>
            </ul>
        </div>

        <div class="btn-request-txt">
        </div>
    </section>