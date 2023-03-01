$(document).ready(function(){
    $(window).keyup(function(e){
        if($(':focus').attr('id') == "lock" ) {
            if(e.shiftKey == false && e.keyCode == 9){    
                $("#back").focus();
                return false;
            }else if(e.shiftKey == true  && e.keyCode == 9){
                $("#other-remarks").focus();
                return false;
            }
        }
    });

    $('.datepicker').datepicker({
        dateFormat: 'yy-mm-dd',
        showOn: 'button',
        buttonImage: '/images/common/icon_date.png',
        buttonImageOnly: true,
        buttonText: 'Select date'
    });

    $("#originalSetting-contract_staff_name").attr("disabled", "disabled").addClass("is-disable").addClass("is-lock");
    $("#originalSetting-contract_staff_department").attr("disabled", "disabled").addClass("is-disable").addClass("is-lock");
    $("#originalSetting-cancel_staff_name").attr("disabled", "disabled").addClass("is-lock");
    $("#originalSetting-cancel_staff_department").attr("disabled", "disabled").addClass("is-lock");

    $(".search_staff").click(function() {
        var search_name = $(this).val();
        if($("#originalSetting-"+ search_name + "_id").val() == "") {
            alert("担当者IDを入力してください。");
            return;
        }
        var asd = $(this);
        var staff_id = $("#originalSetting-"+ search_name + "_id").val();
        var obj = {
            'cd' : staff_id
        }
        var param = $.param(obj);
        $.ajax({
            type: "POST",
            dataType: "json",
            cache: false,
            url: $("#staff_api_url").val(),
            data: param,
            success: function(data){
                if(data != "" && Object.keys(data.data).length != 0 ) {
                    $("#originalSetting-"+ search_name + "_name").val(data.data.tantoName);
                    $("#originalSetting-"+ search_name + "_department").val(data.data.shozokuName);
                    $("#search_"+ search_name).parent("td").children("p").text("");

                }else{
                    adminApp.modal.alert("エラー", "担当者が存在しません。担当者ＩＤをお確かめください。");
                }
            },
            error: function(jqXHR, textStatus, errorThrown){
                adminApp.modal.alert("エラー", "担当者APIに接続できません。しばらく経ってから再度お試しください。");
            },
        });
    });

    $('#originalSetting-contract_staff_id').change(function() {
        $("#originalSetting-contract_staff_name").val("");
        $("#originalSetting-contract_staff_department").val("");
    });

    $('#originalSetting-cancel_staff_id').change(function() {
        $("#originalSetting-cancel_staff_name").val("");
        $("#originalSetting-cancel_staff_department").val("");
    });

    $("#sub_edit").click(function() {
        $("input[type=text]").removeAttr("disabled").removeClass("is-disable");
        $("input[type=radio]").removeAttr("disabled").removeClass("is-disable");
        document.form.submit();
    });
});
