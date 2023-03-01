<div class="map-wrap">
    <div class="map-main">
      <div id="parts_map_canvas1" class="parts-gmap"></div>
    <!-- /map-main --></div>

    <div class="map-change">
      <div class="btn__map-change"></div>
      <div class="toggle__body_l">
        <div class="toggle__inner">
          <div class="map-change__inner articlelist-side contents-left">
            <div class="map-change__scroll">
          <div class="map-change__scroll_inner">
            <section class="articlelist-side-section change-area">
              <h3 class="articlelist-side-heading">地域から探す</h3>
              <div class="change-area1">
                <p class="area">◯◯</p>
                <ul class="btn-change">
                  <li><a href="#" class="js-modal" data-target="search-modal-prefecture">変更</a></li>
                </ul>
              </div>
              <div class="change-area2">
                <p class="area-detail">◯◯区・◯◯市（他◯地域）</p>
                <ul class="btn-change">
                  <li><a href="#" class="js-modal" data-target="search-modal-area">変更</a></li>
                </ul>
              </div>
            </section>

            <section class="articlelist-side-section">
              <h3 class="articlelist-side-heading">絞り込み条件を指定する</h3>
              <section class="select-term">
                <h4 class="articlelist-side-heading2">物件種別</h4>
                <ul>
                  <li><input type="checkbox" value="hoge" name="hoge" id="kind-type1"><label for="kind-type1">アパート<span class="count">(9,999)</span></label></li>
                  <li><input type="checkbox" checked value="hoge" name="hoge" id="kind-type2"><label for="kind-type2" class="checked">マンション<span class="count">(9,999)</span></label></li>
                  <li><input type="checkbox" value="hoge" name="hoge" id="kind-type3" disabled><label for="kind-type3" class="tx-disable">一戸建て<span class="count">(0)</span></label></li>
                </ul>
              </section>
              <section class="select-term">
                <h4 class="articlelist-side-heading2">賃料</h4>
                <div class="select-price">
                  <select name="">
                    <option value="kc001" selected="selected">下限なし</option>
                    <option value="kc041">2万円</option>
                    <option value="kc042">2.5万円</option>
                    <option value="kc002">3万円</option>
                    <option value="kc003">3000万円</option>
                  </select>
                  〜
                  <select name="PRICETO" class="dev-jokenList dev-postItemData">
                    <option value="kc141">2万円</option>
                    <option value="kc142">2.5万円</option>
                    <option value="kc101">3万円</option>
                    <option value="kc102">3.5万円</option>
                    <option value="kc103">4万円</option>
                    <option value="kc137">3000万円</option>
                    <option value="kc138" selected="selected">上限なし</option>
                  </select>
                </div>
                <ul>
                  <li><input type="checkbox" value="hoge" name="hoge" id="price-type1"><label for="price-type1">管理費等を含む<span class="count">(9,999)</span></label></li>
                  <li><input type="checkbox" value="hoge" name="hoge" id="price-type2"><label for="price-type2">駐車場料金を含む<span class="count">(9,999)</span></label></li>
                  <li><input type="checkbox" value="hoge" name="hoge" id="price-type3"><label for="price-type3">礼金なし<span class="count">(9,999)</span></label></li>
                  <li><input type="checkbox" value="hoge" name="hoge" id="price-type4"><label for="price-type4">敷金/保証金なし<span class="count">(9,999)</span></label></li>
                </ul>
              </section>
              <section class="select-term">
                <h4 class="articlelist-side-heading2">契約条件
                  <span class="tooltip">
                    <i class="tooltip-icon"></i>
                    <div class="tooltip-body">
                      <p><span class="tooltip-heading">定期建物賃貸借のこと</span>
                        dummy一般の賃貸契約とは異なり契約期間満了によって契約が終了し、契約更新は行われません。<br>
                        契約期間は物件によって異なります。貸主との合意があれば再契約は可能ですが、賃料等の賃貸条件の変更や、敷金・礼金・仲介手数料等があらためて発生する場合がございます。お問合せの際に十分ご確認ください。
                      </p>
                    </div>
                  </span>
                </h4>
                <select name="KEIYAKU">
                  <option value="ki001">定期借家除く</option>
                  <option value="ki002" selected="selected">定期借家含む</option>
                  <option value="ki003">定期借家のみ</option>
                </select>
              </section>
              <section class="select-term">
                <h4 class="articlelist-side-heading2">間取り</h4>
                <ul class="floor-type">
                  <li><input type="checkbox" id="floor-type1"><label for="floor-type1">1R<span class="count">(9,999)</span></label></li>
                  <li><input type="checkbox" id="floor-type2"><label for="floor-type2">1K<span class="count">(9,999)</span></label></li>
                  <li><input type="checkbox" id="floor-type3"><label for="floor-type3">1DK<span class="count">(9,999)</span></label></li>
                  <li><input type="checkbox" id="floor-type4"><label for="floor-type4">1LDK<span class="count">(9,999)</span></label></li>
                  <li><input type="checkbox" id="floor-type5"><label for="floor-type5">2K<span class="count">(9,999)</span></label></li>
                  <li><input type="checkbox" id="floor-type6"><label for="floor-type6">2DK<span class="count">(9,999)</span></label></li>
                  <li><input type="checkbox" id="floor-type7"><label for="floor-type7">2LDK<span class="count">(9,999)</span></label></li>
                  <li><input type="checkbox" id="floor-type8"><label for="floor-type8">3K<span class="count">(9,999)</span></label></li>
                  <li><input type="checkbox" id="floor-type9"><label for="floor-type9">3DK<span class="count">(9,999)</span></label></li>
                  <li><input type="checkbox" id="floor-type10"><label for="floor-type10">3LDK<span class="count">(9,999)</span></label></li>
                  <li><input type="checkbox" id="floor-type11"><label for="floor-type11">4K<span class="count">(9,999)</span></label></li>
                  <li><input type="checkbox" id="floor-type12"><label for="floor-type12">4DK<span class="count">(9,999)</span></label></li>
                  <li><input type="checkbox" id="floor-type13"><label for="floor-type13">4LDK以上<span class="count">(9,999)</span></label></li>
                </ul>
              </section>
              <section class="select-term">
                <h4 class="articlelist-side-heading2">面積</h4>

                <select name="">
                  <option value="kt001" selected="selected">指定なし</option>
                  <option value="kt002">20m²以上</option>
                  <option value="kt003">25m²以上</option>
                  <option value="kt004">30m²以上</option>
                  <option value="kt005">35m²以上</option>
                  <option value="kt006">40m²以上</option>
                  <option value="kt007">45m²以上</option>
                  <option value="kt008">50m²以上</option>
                  <option value="kt009">55m²以上</option>
                  <option value="kt010">60m²以上</option>
                  <option value="kt011">65m²以上</option>
                  <option value="kt012">70m²以上</option>
                  <option value="kt013">75m²以上</option>
                  <option value="kt014">80m²以上</option>
                  <option value="kt015">85m²以上</option>
                  <option value="kt016">90m²以上</option>
                  <option value="kt017">95m²以上</option>
                  <option value="kt018">100m²以上</option>
                </select>
              </section>
              <section class="select-term">
                <h4 class="articlelist-side-heading2">建築構造
                  <span class="tooltip">
                    <i class="tooltip-icon"></i>
                    <div class="tooltip-body">
                      <p><span class="tooltip-heading">建物構造のこと</span>
                        <span class="bold">●鉄筋系</span><br>
                        「RC（鉄筋コンクリート）」「SRC（鉄骨鉄筋コンクリート）」「PC（プレキャストコンクリート）」の建物を検索します。<br>
                        <span class="bold">●鉄骨系</span><br>
                        「軽量鉄骨」「鉄骨造」「重量鉄骨造」「HPC（鉄骨プレキャストコンクリート造）」「ALC（軽量気泡コンクリート）」の建物を検索します。<br>
                        <span class="bold">●木造</span><br>
                        「木造」の建物を検索します。<br>
                        <span class="bold">●その他</span><br>
                        「ブロック」「鉄筋ブロック造」「CFT（コンクリート充鎮鋼管造）」「その他」の建物を検索します。
                      </p>
                    </div>
                  </span>
                </h4>
                <ul class="structure-type">
                  <li><input type="checkbox" id="structure-type1"><label for="structure-type1">鉄筋系<span class="count">(9,999)</span></label></li>
                  <li><input type="checkbox" id="structure-type2"><label for="structure-type2">鉄骨系<span class="count">(9,999)</span></label></li>
                  <li><input type="checkbox" id="structure-type3"><label for="structure-type3">木造<span class="count">(9,999)</span></label></li>
                  <li><input type="checkbox" id="structure-type4"><label for="structure-type4">その他<span class="count">(9,999)</span></label></li>
                </ul>
              </section>
              <section class="select-term">
                <h4 class="articlelist-side-heading2">駅からの徒歩</h4>
                <select name="EKITOHO">
                  <option value="ke001" selected="selected">指定なし</option>
                  <option value="ke002">3分以内</option>
                  <option value="ke003">5分以内</option>
                  <option value="ke004">10分以内</option>
                  <option value="ke005">15分以内</option>
                  <option value="ke006">20分以内</option>
                </select>
              </section>
              <section class="select-term">
                <h4 class="articlelist-side-heading2">築年数</h4>
                <select name="CHIKUNENSU">
                  <option value="kn001" selected="selected">指定なし</option>
                  <option value="kn002">新築</option>
                  <option value="kn003">3年以内</option>
                  <option value="kn004">5年以内</option>
                  <option value="kn005">10年以内</option>
                  <option value="kn006">15年以内</option>
                  <option value="kn007">20年以内</option>
                  <option value="kn021">25年以内</option>
                  <option value="kn022">30年以内</option>
                  <option value="kn023">35年以内</option>
                  <option value="kn024">40年以内</option>
                </select>
              </section>
              <section class="select-term">
                <h4 class="articlelist-side-heading2">リフォーム・リノベーション</h4>
                <ul>
                  <li><input type="checkbox" id="renovation-type1"><label for="renovation-type1">リフォーム・リノベーション<span class="count">(9,999)</span></label></li>
                </ul>
              </section>
              <section class="select-term">
                <h4 class="articlelist-side-heading2">情報公開日</h4>
                <ul>
                  <li><input type="radio" checked name="hoge-open" id="open-type1"><label for="open-type1" class="checked">指定なし<span class="count">(9,999)</span></label></li>
                  <li><input type="radio" name="hoge-open" id="open-type2"><label for="open-type2">本日公開<span class="count">(9,999)</span></label></li>
                  <li><input type="radio" name="hoge-open" id="open-type3"><label for="open-type3">3日以内に公開<span class="count">(9,999)</span></label></li>
                  <li><input type="radio" name="hoge-open" id="open-type4"><label for="open-type4">1週間以内に公開<span class="count">(9,999)</span></label></li>
                </ul>
              </section>
              <section class="select-term">
                <h4 class="articlelist-side-heading2">アピール</h4>
                <ul>
                  <li><input type="checkbox" id="appeal-type1"><label for="appeal-type1">「おすすめコメント」あり<span class="count">(9,999)</span></label></li>
                </ul>
              </section>
              <section class="select-term">
                <h4 class="articlelist-side-heading2">画像</h4>
                <ul>
                  <li><input type="checkbox" id="img-type1"><label for="img-type1">間取り図あり<span class="count">(9,999)</span></label></li>
                  <li><input type="checkbox" id="img-type2"><label for="img-type2">写真あり<span class="count">(9,999)</span></label></li>
                  <li><input type="checkbox" id="img-type3"><label for="img-type3">パノラマ・ムービーあり<span class="count">(9,999)</span></label></li>
                </ul>
              </section>
              <section class="select-term">
                <h4 class="articlelist-side-heading2">こだわり条件</h4>
                <ul>
                  <li><input type="checkbox" id="good-term1"><label for="good-term1">バス・トイレ別<span class="count">(9,999)</span></label></li>
                  <li><input type="checkbox" id="good-term2"><label for="good-term2">追い焚き機能<span class="count">(9,999)</span></label></li>
                  <li><input type="checkbox" id="good-term3"><label for="good-term3">2階以上<span class="count">(9,999)</span></label></li>
                  <li><input type="checkbox" id="good-term4"><label for="good-term4">駐車場（近隣含む）<span class="count">(9,999)</span></label></li>
                  <li><input type="checkbox" id="good-term5"><label for="good-term5">洗濯機置き場<span class="count">(9,999)</span></label></li>
                  <li><input type="checkbox" id="good-term6"><label for="good-term6">フローリング<span class="count">(9,999)</span></label></li>
                  <li><input type="checkbox" id="good-term7"><label for="good-term7">エアコン<span class="count">(9,999)</span></label></li>
                  <li><input type="checkbox" id="good-term8"><label for="good-term8">ペット相談<span class="count">(9,999)</span></label></li>
                  <li><input type="checkbox" id="good-term9"><label for="good-term9">オートロック<span class="count">(9,999)</span></label></li>
                  <li><input type="checkbox" id="good-term10"><label for="good-term10">日当たり良好<span class="count">(9,999)</span></label></li>

                </ul>
              </section>
              <p class="link-more-term"><a class="js-modal" href="iframe1.html" data-target="search-modal-detail">すべてのこだわり条件を表示</a></p>
              <p class="btn-request"></p>
            </section>
            <section class="element-input-search-result">
            <form class="form-search-freeword">
                <h3 class="articlelist-side-heading">さらに絞り込む</h3>
                <div>
                    <ul>
                        <li class="freeword-text">
                            <datalist class="suggesteds" id="suggesteds"></datalist>
                            <input type="text" placeholder="例： 12.2万円以下 和室" name="search_filter[fulltext_fields]" value="" autocomplete="off" list="suggesteds">
                        </li>
                        <li><p><a href="#" class="search-freeword">検索</a></p></li>
                    </ul>
                </div>
            </form>
            </section>
          <!-- /map-change__scroll_inner --></div>
        <!-- /map-change__scroll --></div>
        </div>
        </div>
      <!-- /toggle__body_l --></div>
    <!-- /map-change --></div>

<!-- /map-wrap --></div>