$(document).ready(function(){
	'use strict';

	$('.datepicker').datepicker({
		dateFormat: 'yy-mm-dd',
		showOn: 'button',
		buttonImage: '/images/common/icon_date.png',
		buttonImageOnly: true,
		buttonText: 'Select date'
    });

});

