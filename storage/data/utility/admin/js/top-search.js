/*
 TOPオリジナル専用フリーワード検索スクリプト
 */
$(function() {
    var suggestIds = {};	// 設置form毎の提案TimerID
    var resetChr = '-';		// 件数取得前にカウンターフィルする文字列(1byte)

    // フリーワード検索機能にイベントを追加
    // 種別変更イベント
    $(".top_freewords_wrap .top-parts-search-type").on('change', function() {
        var formObj = getFreewordForm(this);
        if(!formObj) {
            return false;
        }
        var fwObj = new FreeWord(formObj);
        fwObj.changeType();
        return false;
    });
    // テキスト変更イベント
    $(".top_freewords_wrap .freeword-top-parts-suggested").on('input', function() {
        var fwFormIdx = $(".top_freewords_wrap").index($(this).closest('.top_freewords_wrap'));       
        // 検索待ちをリセットする
        if(suggestIds[ fwFormIdx ] !== undefined) {
            clearTimeout(suggestIds[ fwFormIdx ]);
        }
        var thisObj = $(this);
        // Timerで候補ワードAPIを呼び出すようにする
        suggestIds[ fwFormIdx ] = setTimeout(function() {
            $(thisObj).trigger('change');	// changeイベント発生
        }, 1500);
    });
    // テキスト変更確定イベント
    $(".top_freewords_wrap .freeword-top-parts-suggested").on('change', function() {
        var formObj = getFreewordForm(this);
        if(!formObj) {
            return false;
        }
        var fwObj = new FreeWord(formObj);
        fwObj.changeWord();
        return false;
    });
    // 検索ボタン押下イベント
    $(".top_freewords_wrap .top-parts-btn-search").on('click', function() {
        var formObj = getFreewordForm(this);
        if(!formObj) {
            return false;
        }
        var fwObj = new FreeWord(formObj);
        fwObj.submit();
        return false;
    });

    // テキストボックスで改行を無効化
    $(".top_freewords_wrap .freeword-top-parts-suggested").on("keypress", function(){
        return event.which !== 13;
    });
    // フリーワードフォームの取得
    var getFreewordForm = function(eventObj) {
        var formObj = $(eventObj).closest(".top_freewords_wrap");
        if($(formObj)[0] === undefined) {
            console.log("検索フォームが見つかりません");
            return false;
        }
        return formObj;
    };

    // 全フォームを初期化する
    var init = function() {
        // オプションの並べ替え・取捨選択
        $(".top_freewords_wrap").each(function() {
            var shubetsuSort = $(this).attr('shubetsu-sort');
            if(shubetsuSort === undefined) {
                // とりあえず0番目を選択状態にする
                $(this).find(".top-parts-search-type").eq(0).prop("selectedIndex", 0);
                return true;
            }
            var enableTypes = {};
            var allType = null;
            var searchType = $(this).find(".top-parts-search-type").eq(0);
            $(searchType).find('option').each(function() {
                var typeAlias = $(this).attr("alias_name");
                switch(typeAlias) {
                    case '居住用賃貸':
                        enableTypes['1'] = this;
                        break;
                    case '事業用賃貸':
                        enableTypes['2'] = this;
                        break;
                    case '居住用売買':
                        enableTypes['3'] = this;
                        break;
                    case '事業用売買':
                        enableTypes['4'] = this;
                        break;
                    default:
                        allType = this;
                        break;
                }
            });
            var sortedTypes = [];
            shubetsuSort.split(',').forEach(function(val) {
                if(enableTypes[val] !== undefined) {
                    sortedTypes.push(enableTypes[val]);
                }
            });
            if(sortedTypes.length > 1) {
                // 複数種別がある場合は先頭に『選択してください』を設定
                if(allType !== null) {
                    sortedTypes.unshift(allType);
                }
            } else {
                // 1種別の時はリストボックスを隠す
                $(searchType).hide();
            }
            $(searchType).empty();
            sortedTypes.forEach(function(opt) {
                $(searchType).append(opt);
            });
            // とりあえず0番目を選択状態にする
            $(this).find(".top-parts-search-type").eq(0).prop("selectedIndex", 0);
        });
        // テキストボックス処理
        var listNo = 0;
        $(".top_freewords_wrap").each(function() {
            listNo++;
            var searchText = $(this).find(".freeword-top-parts-suggested").eq(0);
            if($(searchText)[0] === undefined) {
                // テキストが無い場合はhiddenでテキストボックス設定
                $("<input>").attr({
                    type: 'hidden',
                    class: 'freeword-top-parts-suggested',
                    name: 'search_filter[fulltext_fields]',
                    value: ''
                }).appendTo($(this));
            } else {
                // テキストがある場合は、候補用のdatalistを作成
                $(searchText).val('');
                $(searchText).attr('list', 'suggesteds-top-' + listNo);
                $("<datalist>").attr({
                    id: 'suggesteds-top-' + listNo,
                    class: "suggesteds"
                }).appendTo($(this));
            }
        });
        // 全フォームファセット取得: 輻輳を避けるため 500ms間隔で実施する
        var timer = 500;
        var counter = 1;
        $(".top_freewords_wrap .top-parts-search-type").each(function() {
            setTimeout(function(obj) { $(obj).trigger('change'); }, timer * counter, $(this));
        });
    };
    init();

    // FreeWord コンストラクタ
    function FreeWord(fObj) {
        this.mode_preview = false;

        if(location.pathname.indexOf('/publish/preview-page/') == 0) {
            this.mode_preview = true;
        }
        this.formObj = fObj;
        this.apiTimeout = 10000;
        this.countMinLen = 5;
    }
    // 検索結果に移動
    FreeWord.prototype.submit = function() {
        // 種別取得
        var shubetsuMainparts = $(this.formObj).find(".top-parts-search-type").eq(0).val();
        if(shubetsuMainparts === undefined) {
            console.log("種目が見つかりません");
            return false;
        }
        if(shubetsuMainparts == "all") {
            console.log("種目を選択してください");
            return false;
        }
        var searchUrl = location.protocol + "//" + location.host + "/" + shubetsuMainparts + "/result/"
        var searchText = $(this.formObj).find(".freeword-top-parts-suggested").eq(0);
        var searchPost = $("<form>").attr({
            method: 'POST',
            action: searchUrl
        });
        var detailObj = $("<input>").attr({
            type: 'hidden',
            name: 'detail',
            value: $(searchText).serialize()
        }).appendTo(searchPost);
        var fulltextObj = $("<input>").attr({
            type: 'hidden',
            name: 'fulltext',
            value: $(searchText).serialize()
        }).appendTo(searchPost);
        $("body").append(searchPost);
        setTimeout(function(searchPost) { $(searchPost).submit(); }, 100, searchPost);
        return false;
    }
    // 種別変更処理
    FreeWord.prototype.changeType = function() {
        // 種別取得
        var shubetsuMain = $(this.formObj).find(".top-parts-search-type").eq(0);
        if(shubetsuMain === undefined) {
            console.log("種目が見つかりません");
            return false;
        }
        var shubetsuMainparts = $(shubetsuMain).val();
        var phval = "";

        var selTypeObj = $(shubetsuMain).find("option:selected");
        var typeAlias = $(selTypeObj).attr('alias_name');
        var phval = $(selTypeObj).attr('type_placeholder');
        
        if(phval === undefined || phval == "") {
            switch(typeAlias) {
                case "居住用賃貸":
                    phval = "例：12.2万円以下 和室";
                    break;
                case "事業用賃貸":
                    phval = "例：12.2万円以下 駐車場あり";
                    break;
                case "居住用売買":
                    phval = "例：2000万円以下 南向き";
                    break;
                case "事業用売買":
                    phval = "例：2000万円以下 駐車場あり";
                    break;
                default:
                    phval = "種別を選択してください"
                    break;
            }
        }
        var searchText = $(this.formObj).find(".freeword-top-parts-suggested").eq(0);
        $(searchText).attr("placeholder", phval);
        var fulltextCount = this.useCounter();
        if(fulltextCount == null) {
            return false;
        }
        // allだったら一旦0にする
        if(shubetsuMainparts == "all") {
            this.facetSearchAll(fulltextCount, searchText);
            return false;
        }
        // previewだったら検索不要で0にする
        if(this.mode_preview) {
            this.setCounter(0);
            return false;
        }
        // ファセット検索パラメータ生成
        var cdata = this.makeCdata(shubetsuMainparts, searchText);
        this.facetSearch(cdata, fulltextCount);
        return false;
    }
    // テキスト変更処理
    FreeWord.prototype.changeWord = function() {
        if(this.mode_preview) {
            return false;
        }
        // 種別取得
        var shubetsuMainparts = $(this.formObj).find(".top-parts-search-type").eq(0).val();
        if(shubetsuMainparts === undefined) {
            console.log("種目が見つかりません");
            return false;
        }
        var searchText = $(this.formObj).find(".freeword-top-parts-suggested").eq(0);
        // 候補取得パラメータ生成
        var cdata = this.makeCdata(shubetsuMainparts, searchText);
        if($(searchText).val() != "") {
            this.suggestSearch(cdata, searchText);
        } else {
            var dataListId = $(searchText).attr('list');
            $("#" + dataListId).empty();
        }
        return false;
    }
    // テキスト確定処理
    FreeWord.prototype.commitWord = function() {
        if(this.mode_preview) {
            return false;
        }
        // 種別取得
        var shubetsuMainparts = $(this.formObj).find(".top-parts-search-type").eq(0).val();
        if(shubetsuMainparts === undefined) {
            console.log("種目が見つかりません");
            return false;
        }
        var searchText = $(this.formObj).find(".freeword-top-parts-suggested").eq(0);
        var fulltextCount = this.useCounter();
        if(fulltextCount == null) {
            return false;
        }
        // allだったら一旦0にする
        if(shubetsuMainparts == "all") {
            this.facetSearchAll(fulltextCount, searchText);
            return false;
        }
        // ファセット検索パラメータ生成
        var cdata = this.makeCdata(shubetsuMainparts, searchText);
        this.facetSearch(cdata, fulltextCount);
        return false;
    }
    // 候補ワード取得-Ajax-
    FreeWord.prototype.suggestSearch = function(cdata, searchText) {
        $.ajax({
            url: "/api/suggest/",
            type:'POST',
            dataType: 'json',
            data : cdata,
            timeout: this.apiTimeout,
            context: this
        }).done(function(data) {
            var dataListId = $(searchText).attr('list');
            $("#" + dataListId).empty();

            if(data.suggestions === undefined) {
                console.log('No Suggestion Word');
            } else {
                data.suggestions.forEach(function(val) {
                    $("<option>").text( $(searchText).val() + val ).appendTo($("#" + dataListId));
                });
            }
            this.commitWord();
        }).fail(function(XMLHttpRequest, textStatus, errorThrown) {
            console.log('候補ワード取得に失敗しました');
        });
    }
    // 件数取得-Ajax-
    FreeWord.prototype.facetSearch = function(cdata, fulltextCount) {
        // カウンターリセット
        this.resetCounter();

        $.ajax({
            url: "/api/count/",
            type:'POST',
            dataType: 'json',
            data : cdata,
            timeout: this.apiTimeout,
            context: this
        }).done(function(data) {
            var num = data.total_count;
            this.setCounter(num);
        }).fail(function(XMLHttpRequest, textStatus, errorThrown) {
            console.log('件数取得に失敗しました');
        });
    }
    // 件数取得 for ALL-Ajax-
    FreeWord.prototype.facetSearchAll = function(fulltextCount, searchText) {
        // カウンターリセット
        this.resetCounter();

        var shumokuList = [];   // プルダウンの種目
        var totalCounts = [];   // 各種目毎の件数
        $(this.formObj).find(".top-parts-search-type").eq(0).find("option").each(function() {
            if($(this).val() == 'all') return true;

            shumokuList.push($(this).val());
            totalCounts.push(null);
        });

        for(var sno = 0; sno < shumokuList.length; sno++) {
            // 種別ごとにパラメータを作成し、Ajaxで件数取得を実行(各種別を非同期で行う)
            // 成功時には、各種別に割り当てられたtotalCountsのスロットに件数を書き出す
            var cdata = this.makeCdata(shumokuList[sno], searchText);
            $.ajax({
                url: "/api/count/",
                type:'POST',
                dataType: 'json',
                data : cdata,
                timeout: this.apiTimeout,
                context: [totalCounts, sno]
            }).done(function(data) {
                this[0][ this[1] ] = data.total_count;
            }).fail(function(XMLHttpRequest, textStatus, errorThrown) {
                console.log('件数取得に失敗しました');
            });
        }

        // 各種別の件数取得が完了したかをintervalTimerを利用して200ms毎/30回行う
        // いずれかが失敗 or 時間内(4s)に終了しなかった場合は無視(前段で0を設定済)
        var intCount = 0;
        var intTimer = setInterval(function(_this) {
            intCount++;
            if(totalCounts.indexOf(null) < 0) { 
                // 全種別の件数取得完了した場合はそれらを合算する
                var tcount = 0;
                for(var tc=0; tc < totalCounts.length; tc++) {
                    tcount+= totalCounts[tc];
                }
                // カウンターに件数を表示し、タイマー解除のため、intCountに上限(30)以上を設定
                _this.setCounter(tcount);
                intCount = 999;
            }
            if(intCount > 75) {
                clearInterval(intTimer);
            }
        }, 200, this);
    }
    // 件数取得・候補取得のAPIパラメータ作成
    FreeWord.prototype.makeCdata = function(shubetsuMainparts, searchText) {
        var cdata = {
            fulltext: $(searchText).val(),
            fulltext_type: 'morpho',
            s_type: 12,
            condition_side: $(searchText).serialize(),
            type_freeword: shubetsuMainparts,
            shumoku: shubetsuMainparts
        };
        return cdata;
    }
    // リアルタイムカウンターの要否チェック
    FreeWord.prototype.useCounter = function() {
        var fulltextCount = $(this.formObj).find(".fulltext-count").eq(0);
        if($(fulltextCount)[0] === undefined) {
            console.log("カウンター不要");
            return null;
        }
        var countLen = $(fulltextCount).find('i').length;
        if(countLen == 0) {
            console.log("カウンター不要");
            return null;
        }
        return fulltextCount;
    }
    // カウンターを変更する
    FreeWord.prototype.setCounter = function(num) {
        var fulltextCount = this.useCounter();
        var countLen = $(fulltextCount).find('i').length;
        var numLen = parseInt(num).toString(10).length;

        if(numLen != countLen) {
            if(numLen > countLen) {
                for(cno = 0; cno < (numLen - countLen); cno++) {
                    $(fulltextCount).append("<i/>");
                }
            } else if(countLen > numLen) {
                if(countLen != this.countMinLen) {
                    $(fulltextCount).empty();
                    if(numLen > this.countMinLen) {
                        for(cno = 0; cno < numLen; cno++) {
                            $(fulltextCount).append("<i>0</i>");
                        }
                    } else if(numLen <= this.countMinLen) {
                        for(cno = 0; cno < this.countMinLen; cno++) {
                            $(fulltextCount).append("<i>0</i>");
                        }
                    }
                }
            }
            countLen = $(fulltextCount).find('i').length;
        }
        var counterStr = (Array(countLen + 1).join('0') + num).slice(-countLen);
        var counterStrArray = counterStr.split('');
        for(var ino=0; ino < countLen; ino++) {
            $(fulltextCount).find('i').eq(ino).text(counterStrArray[ino]);
        }
    }
    // カウンターをリセットする
    FreeWord.prototype.resetCounter = function() {
        // 1byte以外は無視
        if(encodeURIComponent(resetChr).replace(/%../g,"x").length != 1) {
            return;
        }

        var fulltextCount = this.useCounter();
        var countLen = $(fulltextCount).find('i').length;
        for(var ino=0; ino < countLen; ino++) {
            $(fulltextCount).find('i').eq(ino).text(resetChr);
        }
    }
});
