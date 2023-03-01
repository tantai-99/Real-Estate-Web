$(function(){$('input[name="next"]').on("click",function(e){var postData;var contactType;var estateClass="";resetErrors();e.preventDefault();postData=getElementValue();contactType=$(".contents form").attr("action").split("/")[1];if($(".contents form").attr("action").split("/")[2]!=="confirm"){estateClass="/"+$(".contents form").attr("action").split("/")[2]}$.ajax({type:"POST",url:"/"+contactType+estateClass+"/validate/",data:postData,timeout:120*1e3,dataType:"json"}).done(function(res){var msg;if(!res){msg="system error";alert(msg);return false}if(!res.isValid){$(".form-error ul").append("<li>入力内容に誤りがあります。下記項目をご確認の上、入力してください。</li>");$.each(res.data.errorMsg,function(i,val){$.each(String(val).split(","),function(j,errorMsg){$(".form-error."+i+"-err").append("<p>"+errorMsg+"</p>");if(i==="connection"){$(".person_mail-input").addClass("validate-error");$(".person_tel-input").addClass("validate-error");$(".person_other_connection-input").addClass("validate-error")}else{$("."+i+"-input").addClass("validate-error")}})});$("html, body").animate({scrollTop:0});return false}$("form").submit();return false}).fail(function(res){var msg;msg="システムエラーが発生しました。\nご迷惑をおかけしいたしますが、しばらく経ってから再度アクセスしてください。";alert(msg);return false})});$('input[name="send"]').click(function(){var url=$("#url").val();$("form").attr("action",url);$('input[name="send"]').prop("disabled",true);$("<input>").attr({type:"hidden",name:"send",value:"送信する"}).appendTo($("form"));var d=new Date;var h=d.getHours();var m=d.getMinutes();var s=d.getSeconds();var ms=d.getMilliseconds();var yy=d.getFullYear();var mm=d.getMonth()+1;var d=d.getDate();var date=yy+"-"+mm+"-"+d+" "+h+":"+m+":"+s+"."+ms;$("<input>").attr({type:"hidden",name:"click_date",value:date}).appendTo($("form"));$("form").submit()});function resetErrors(){$(".form-error ul li").remove();$(".form-error p").remove();$(".validate-error").removeClass("validate-error")}function getElementValue(){var elementName="";var elementValue={};$('input[type="text"]').each(function(){elementValue[this.name]=this.value});$(".form-textarea").each(function(){elementValue[this.name]=this.value});$("input:checked").each(function(){elementName=this.name.substr(0,this.name.length-2);if(elementName in elementValue){elementValue[elementName].push(this.value)}else{elementValue[elementName]=[this.value]}});$(":selected").each(function(){elementValue[this.parentElement.name]=this.value});return elementValue}$("input, textarea").on("paste",function(e){var paste=e.originalEvent.clipboardData.getData("text");var target=e.target.value;var max=parseInt($(e.target).attr("validatelength"));var selected=e.target.selectionEnd-e.target.selectionStart;var error="."+(e.target.name.indexOf("person_tel")?e.target.name:"person_tel")+"-err";$(error+" p").remove();if(paste.length+(target.length-selected)>max){$(error).append("<p>"+$(e.target).attr("label")+"の文字数がオーバーしています。"+max+"文字以内で入力してください。</p>")}})});