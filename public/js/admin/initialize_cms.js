$(document).ready(function(){

	//CMSデータ削除ボタン押下時
	$("#del").click(function() {

        var memberNo = $(".form-basic").eq(0).find("td").eq(0).text();
        var memberName = $(".form-basic").eq(0).find("td").eq(1).text();
        var domainName = $(".form-basic").eq(0).find("td").eq(2).text();

		var tableArea = $('<div>').attr({
			style: 'text-align: center;width:100%;'
		}) 
        var innerTbl = $('<table>').attr({
			border: "1px",
			bordercolor: "#d2d6dc",
			style: 'margin:auto; width:80%;'
		}).appendTo(tableArea); 
    
		innerTbl.append(
			$('<tr>')
				.append($('<th>').attr({style: 'padding: 5px'}).text('会員No'))
				.append($('<td>').attr({style: 'padding: 5px'}).text(memberNo)),
			$('<tr>')
				.append($('<th>').attr({style: 'padding: 5px'}).text('会員名'))
				.append($('<td>').attr({style: 'padding: 5px'}).text(memberName)),
			$('<tr>')
				.append($('<th>').attr({style: 'padding: 5px'}).text('利用ドメイン'))
				.append($('<td>').attr({style: 'padding: 5px'}).text(domainName))
		);
		app.modal.popup({
			"title" : "下記会員のCMS情報を削除します。よろしいですか？",
			"modalBodyInnerClass" : 'align-top',
			"contents" : tableArea,
			"onClose" : deleteCompanyHandler
		}).show();
	});
	//削除する
	function deleteCompanyHandler(let) {
		if(let == true) {
			// $("#del").addClass('is-disable');
			// $("#del").prop('disabled', true);
			document.form.submit();
		}
	}
});
