$(function () {

  $.fn.serializeObject  = function (data) {
    var els = $(this).find(':input').get();
    if (typeof data !== 'object') {
      // return all data
      data = {};
      $.each(els, function () {
        if (this.name && !this.disabled && (this.checked || /select|textarea/i.test(this.nodeName) || /text|hidden|password/i.test(this.type))) {
          data[this.name] = $(this).val();
        }
      });
      return data;
    }
  };

  //init

  function objectData(){
    var form = $('#housing_block');
    var obj = form.serializeObject();
    var regex = /\w+\[(\d+)\]\[(\w+)\]/;
    var objData = {};
    $.each(obj, function(k,v){
      var m = k.match(regex);
      var id = m[1];
      var group = m[2];
      if(!objData.hasOwnProperty(id)){
        objData[id] = {};
      }
      if(!objData[id].hasOwnProperty(group)){
        objData[id][group] = v;
      }
    });
    return objData;
  }

  var initData = objectData();

  function getDiffData(initData,data){
    var changes = {};
    $.each(data,function(k,v){
      if(initData.hasOwnProperty(k)){
        var initItem = initData[k];
        if(JSON.stringify(initItem) !== JSON.stringify(v)){
          changes[k] = v;
        }
      }
    });
    return changes;
  }


  // Settings Special Housing Block
  $('#submit.submit-special-setting').on('click', function (e) {
    e.preventDefault();
    var form = $('#housing_block');

    var currentData = objectData();

    var parts = getDiffData(initData,currentData);

    if(Object.keys(parts).length === 0){
      console.log('No New Data');
      return;
    }

    app.apiCustom('', {
      parts: parts
    }, {
      el: form,
      tag: 'span',
      onSuccess: function(res){
        initData = currentData;
        app.modal.tempo(2000,'',res.message,function(){
          location.reload(true);
        });
      },
      onError: function(err){}
    });
  });
  $('#main').css({'min-width': '1150px'});
  $(".d-flex-content .cms-disable-ui").css({'padding-left': '2px'});
  if ($("#contents").width() - $('#g-header').width() > 60) {
        $('#g-header').css({'width': $("#contents").width()-1});
  }
  var tab2 = $('#side');
  if (tab2.length) {
    $(window).scroll(function () {
      $('#g-header').css({'width': $("#contents").width()});
    });
  }
  $('select').addClass("ghostSelect");
  $('.sel-special select, .sel-special-sort').on('change',function() {
    var value = $(this).val();
    if ($(this).find('option:selected').text()) {
        value = $(this).find('option:selected').text();
    }
    $(this).parent().find('.sel-custom-special').html(value);
  });

  $('.read-special-btn').on('click', function(e){
    e.preventDefault();
    var company_id = $(this).attr('data-id');
    var url = '/admin/company/api-read-top-housing-block?company_id=' + company_id;
    app.apiCustom(url,{},{
      onSuccess: function(res){
        app.modal.tempo(2000,'',res.message,function(){
          location.reload(true);
        });
      },
      onError: function(err){
      }
    });
  });

  $("input[type='checkbox']").on('click', function(e){
    if ($(this).is(':checked')) {
      $(this).parent().find("input[type='hidden']").val(1);
    } else {
      $(this).parent().find("input[type='hidden']").val(0);
    }
  });

});