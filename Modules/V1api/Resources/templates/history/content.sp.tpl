   <div class="breadcrumb">
        <ul>
            <li itemscope itemtype="http://data-vocabulary.org/Breadcrumb"><a href="/">ホーム</a></li>
        </ul>
    </div>

    <section class="element-checklist">
        <h2 class="article-heading">
            最近見た物件一覧
        </h2>

        <div class="element element-search-tab">
            <ul>
                <li class="active chintai">
                    <a href="#">賃貸</a>
                </li>
                <li class="baibai">
                    <a href="#">売買</a>
                </li>
            </ul>
        </div>

        <section>
            <h3 class="heading-lv2-1column">最近見た物件を50件まで登録できます。</h3>
            <div class="tx-lead-keyword">
                <p>
                    ※物件情報は30日間保存されます。保存期限を経過した情報は削除されます。また、成約や公開期間満了などの理由により、公開が終了した物件については表示されません。
                </p>
            </div>

            <div class="element-search-tab4 chintai">
                <ul>
                    <li class="active rent">
                        <!--お問い合わせ先のURL-->
                        <a href="/inquiry/kasi-kyojuu/edit/">賃貸（4件）</a>
                    </li>
                    <li class="parking">
                        <a href="/inquiry/kasi-jigyou/edit/">駐車場（0件）</a>
                    </li>
                    <li class="office">
                        <a href="/inquiry/kasi-jigyou/edit/">店舗・事務所（0件）</a>
                    </li>
                    <li class="others">
                        <a href="/inquiry/kasi-jigyou/edit/">土地・その他（0件）</a>
                    </li>
                </ul>
            </div>

            <div class="element-search-tab4 baibai" style="display: none">
                <ul>
                    <li class="mansion">
                        <a href="/inquiry/uri-kyojuu/edit/">マンション（4件）</a>
                    </li>
                    <li class="house">
                        <a href="/inquiry/uri-kyojuu/edit/">一戸建て（0件）</a>
                    </li>
                    <li class="land">
                        <a href="/inquiry/uri-jigyou/edit/">土地（0件）</a>
                    </li>
                    <li class="business">
                        <a href="/inquiry/uri-jigyou/edit/">事業用（0件）</a>
                    </li>
                </ul>
            </div>

            <form method="POST" action="/personal/history/" id="personalsort">
            <input type="hidden" name="searchtab" value="" id="hide-search-tab">
            <input type="hidden" name="checklisttab" value="" id="hide-checklist-tab">
            <p class="sort-select">表示順：
                <select name="sort" cursort="asc">
                    <option value="asc" class="asc">保存した順</option>
                    <option value="kakaku:asc">賃料が安い順</option>
                    <option value="kakaku:desc">賃料が高い順</option>
                    <option value="ensen_eki">駅順</option>
                    <option value="shozaichi_kana">住所順</option>
                    <option value="eki_kyori">駅から近い順</option>
                    <option value="bukken_shumoku">間取り順</option>
                    <option value="senyumenseki:desc">面積が広い順</option>
                    <option value="chikunengetsu:desc">築年月が浅い順</option>
                    <option value="b_muke_c_muke_er_nomi_kokai_date:desc">新着順</option>
                </select>
            </p>
            </form>

            <div class="list-history rent">
                <div class="element">
                    <p class="element-tx">該当の物件がありません。
                    </p>
                </div>
            </div>

            <div class="list-history parking" style="display: none">
                <div class="element">
                    <p class="element-tx">該当の物件がありません。
                    </p>
                </div>
            </div>

            <div class="list-history office" style="display: none">
                <div class="element">
                    <p class="element-tx">該当の物件がありません。
                    </p>
                </div>
            </div>

            <div class="list-history others" style="display: none">
                <div class="element">
                    <p class="element-tx">該当の物件がありません。
                    </p>
                </div>
            </div>

            <div class="list-history mansion" style="display: none">
                <div class="element">
                    <p class="element-tx">該当の物件がありません。
                    </p>
                </div>
            </div>

            <div class="list-history house" style="display: none">
                <div class="element">
                    <p class="element-tx">該当の物件がありません。
                    </p>
                </div>
            </div>

            <div class="list-history land" style="display: none">
                <div class="element">
                    <p class="element-tx">該当の物件がありません。
                    </p>
                </div>
            </div>

            <div class="list-history business" style="display: none">
                <div class="element">
                    <p class="element-tx">該当の物件がありません。
                    </p>
                </div>
            </div>

            <!-- お気に入り一覧ページで使うボタン -->
            <div class="btn-checklist-action">
                <ul>
                    <li class="btn-fav-add"><a href="#">お気に入り<br>登録</a></li>
                    <li class="btn-lv3 btn-contact"><a href="#">チェックした物件を<br>まとめて問合せ</a></li>
                </ul>
            </div>
            <!-- end お気に入り一覧ページで使うボタン -->
        </section>
    </section>