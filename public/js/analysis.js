$(function(){
	'use strict';

	function commafy(n) {
	  if (typeof n == 'undefined') {
		  return 0;
	  }
	  var parts = n.toString().split('.');
	  parts[0] = parts[0].replace(/(\d)(?=(\d\d\d)+(?!\d))/g, '$1,');
	  return parts.join('.');
	}

	/* サマリー */
	var summary = (function() {

		// constructor
		var _conmanyId;
		var _ajaxReq=null;
		var summary = function() {
			_ajaxReq = null;
			_conmanyId = $('#company_id').val();
		};

		var _baseYearMonth = null;
		summary.yearMonthChange = function(year,month) {
			_baseYearMonth = year+"-"+month;
			var summaryObj = new summary();
			summaryObj.update();
		};

		summary.resetAjaxReq = function() {
			if(_ajaxReq!=null){
				_ajaxReq.abort();
			}
		}

		var p = summary.prototype;

		p.update = function() {
			this.api();
		};

		p.api = function() {
			var url = '/diacrisis/api-get-analysis-summary';
            var param = null;

            if(_baseYearMonth!=null){
            	param = {baseYearMonth:_baseYearMonth, companyId:_conmanyId};
            }else{
            	param = {companyId:_conmanyId};	
            }
        
			_ajaxReq = app.api(url, param, function (res) {
				if (res.errors) {
					alert(res.errors);
				} else {
					var instance = new summary();
					instance.apiResult(res.items);
				}
			});
		};

		p.apiResult = function(items) {

			var table = document.getElementById("summary-table"); 

			// 当月期間
			$('#summary-base-period').text('当月期間：'+items['summary']['basePeriod']);
			
			var mapRowId = {
				visits              : 1,
				newVisits           : 2,
				visitors            : 3,
				pageviews           : 4,
				pageviewsPerVisits  : 5,
				bounceRate         : 6,
				mailCount           : 7,
			};

			var mapCellId = {
				baseMonth         : 1,
				prevMonth         : 2,
				prevMonthRate     : 3,
				prevYearMonth     : 4,
				prevYearMonthRate : 5,
			};

			var mapDirection = {
				'up'     : 'is-up',
				'down'   : 'is-down',
				'same'   : 'is-unchanged',
				'none'   : 'is-none',
			};

			var row; var key; var direction;
			var data = items['summary']['data'];
			var rateValue;
			for(var rowId=mapRowId.visits; rowId<=mapRowId.mailCount; rowId++){
				switch(rowId)
				{
					case mapRowId.visits:
						key = 'visits';
						break;
					case mapRowId.newVisits:
						key = 'newVisits';
						break;
					case mapRowId.visitors:
						key = 'visitors';
						break;
					case mapRowId.pageviews:
						key = 'pageviews';
						break;
					case mapRowId.pageviewsPerVisits:
						key = 'pageviewsPerVisits';
						break;
					case mapRowId.bounceRate:
						key = 'bounceRate';
						break;
					case mapRowId.mailCount:
						key = 'mailCount';
						break;
				}
				row = table.rows[rowId];
				row.cells[mapCellId.baseMonth].innerHTML = commafy(data[key]['base-month']);
				row.cells[mapCellId.prevMonth].innerHTML = commafy(data[key]['prev-month']);
				direction = mapDirection[data[key]['prev-month-rate']['direction']];

				if (direction == 'is-none') {
					rateValue = '';
				} else if (data[key]['prev-month-rate']['value'] === 0) {
					rateValue = '0.00％';
				}
				else if (data[key]['prev-month-rate']['value']) {
					rateValue = data[key]['prev-month-rate']['value']+"％";
				}
				else {
					rateValue = '';
				}
				row.cells[mapCellId.prevMonthRate].firstChild.className = direction;
				row.cells[mapCellId.prevMonthRate].firstChild.innerHTML = rateValue;
				
				row.cells[mapCellId.prevYearMonth].innerHTML = commafy(data[key]['prev-year-month']);
				direction = mapDirection[data[key]['prev-year-month-rate']['direction']];
				
				if (direction == 'is-none') {
					rateValue = '';
				} else if (data[key]['prev-year-month-rate']['value'] === 0) {
					rateValue = '0.00％';
				}
				else if (data[key]['prev-year-month-rate']['value']) {
					rateValue = data[key]['prev-year-month-rate']['value']+"％";
				}
				else {
					rateValue = '';
				}
				row.cells[mapCellId.prevYearMonthRate].firstChild.className = direction;
				row.cells[mapCellId.prevYearMonthRate].firstChild.innerHTML = rateValue;
			}
		};
		return summary;
	})();




	/* アクセス状況推移 */
	var access = (function() {

		// constructor
		var _conmanyId;
		var _ajaxReq;
		var access = function() {
			_conmanyId = $('#company_id').val();
		};

		var _baseYearMonth = null;
		access.yearMonthChange = function(year,month) {
			_baseYearMonth = year+"-"+month;
			var accessObj = new access();
			accessObj.update();
		};
		access.resetAjaxReq = function() {
			if(_ajaxReq!=null){
				_ajaxReq.abort();
			}
		}

		var p = access.prototype;


		p.update = function() {
			this.api();
		};

		p.api = function() {
			var url = '/diacrisis/api-get-analysis-access';
            var param = null;
            if(_baseYearMonth!=null){
            	param = {baseYearMonth:_baseYearMonth, companyId:_conmanyId};
            }else{
            	param = {companyId:_conmanyId};	
            }

			_ajaxReq = app.api(url, param, function (res) {
				if (res.errors) {
					alert(res.errors);
				} else {
					var instance = new access();
					instance.apiResult(res.items);
				}
			});
		};

		p.apiResult = function(items) {
			$('#access-base-period').text('当月期間：'+items['access']['basePeriod']);
			this.updateTable(items);
			this.updateGraph(items);
		};

		p.updateTable = function(items) {

			var data = items['access']['data'];
			var table = document.getElementById("access-table"); 

			var mapRowId = {
				visits              : 1,
				newVisits           : 2,
				visitors            : 3,
				pageviews           : 4,
				pageviewsPerVisits  : 5,
				bounceRate         : 6,
//				mailCount           : 7,
				contactCount           : 7,
			};

			var row; var key;
			row = table.rows[0];
			var cellId=1;

			//テーブルヘッダ
			for( var date in data['date']){
				row.cells[cellId].innerHTML = date.replace('-','/');
				cellId++;
			}
			for(var rowId=mapRowId.visits; rowId<=mapRowId.contactCount; rowId++){
				switch(rowId)
				{
					case mapRowId.visits:
						key = 'visits';
						break;
					case mapRowId.newVisits:
						key = 'newVisits';
						break;
					case mapRowId.visitors:
						key = 'visitors';
						break;
					case mapRowId.pageviews:
						key = 'pageviews';
						break;
					case mapRowId.pageviewsPerVisits:
						key = 'pageviewsPerVisits';
						break;
					case mapRowId.bounceRate:
						key = 'bounceRate';
						break;
					case mapRowId.contactCount:
						key = 'contactCount';
						break;
				}
				row = table.rows[rowId];
				var cellId=1;
				for( var elKey in data[key]){
					row.cells[cellId].innerHTML = commafy(data[key][elKey]);
					cellId++;
				}

			}			


			p.updateGraph = function(items) {

				var data = items['access']['data'];

				var idx=0;
				var dateList = [];
				var accessData = [];
				var contactData = [];
				for( var date in data['date']){
					dateList[idx] = date;
					accessData[idx]= parseInt(data['visits'][date]);
					contactData[idx]= parseInt(data['contactCount'][date]);
					idx++;
				}

			    var jqplot = jQuery.jqplot(
			        'access-graph',
			        [
			            [ [ dateList[0], accessData[0] ],  [ dateList[1], accessData[1] ],  [ dateList[2], accessData[2] ],  [ dateList[3], accessData[3] ],  [ dateList[4], accessData[4] ], [ dateList[5],  accessData[5] ] ]
			            /*
			            [ [ dateList[0], accessData[0] ],  [ dateList[1], accessData[1] ],  [ dateList[2], accessData[2] ],  [ dateList[3], accessData[3] ],  [ dateList[4], accessData[4] ], [ dateList[5],  accessData[5] ] ],
			            [ [ dateList[0], contactData[0] ], [ dateList[1], contactData[1] ], [ dateList[2], contactData[2] ], [ dateList[3], contactData[3] ], [ dateList[4], contactData[4] ], [ dateList[5], contactData[5] ] ]
			            */
			        ],
			        {
			            series:[
			                {
			                    label: 'セッション数',
			                    renderer: jQuery . jqplot . BarRenderer,
			                },
			                {
			                    label: 'お問い合わせ数',
			                    xaxis: 'x2axis',
			                    yaxis: 'y2axis',
			                }
			            ],
			            seriesColors: [ "#82baf1", "#e5bf26"],
			            seriesDefaults : {
			                shadow: false,
			              
			                markerOptions: {shadow: false},

			                rendererOptions: {
			                    barMargin: 120,
			                    barWidth: 15
			                }

			            },
			            grid:{background: "#fff",gridLineColor: '#eee',shadow: false , borderWidth: 1},
			            axes: {
			                xaxis: {
			                    renderer: jQuery . jqplot . CategoryAxisRenderer,
			                },
			                x2axis: {
			                    renderer: jQuery . jqplot . CategoryAxisRenderer,
			                    tickOptions: { 
			                     	showLabel: false,
								}
			                },
			                yaxis: {
		                    	numberTicks: 5,  
			                    min:0,
			                },
			                y2axis: {
			                    numberTicks: 5,  
			                    min:0,
			                }
			            },
			            legend:{
			            	show: true,
            			    placement: 'outsideGrid',
			                location: 's',
                			renderer: jQuery . jqplot . EnhancedLegendRenderer,
                			rendererOptions: {
                    			numberRows: 5,
                			}
			            },
			        }

			    );
			    jqplot.replot();
			}
		}
		return access;
	})();



	/* デバイス別アクセス解析 */
	var accessDevice = (function() {

		// constructor
		var _conmanyId;		
		var _ajaxReq;
		var  accessDevice = function() {
			_conmanyId = $('#company_id').val();
		};

		var _baseYearMonth = null;
		accessDevice.yearMonthChange = function(year,month) {
			_baseYearMonth = year+"-"+month;
			var  accessDeviceObj = new accessDevice();
			 accessDeviceObj.update();
		};
		accessDevice.resetAjaxReq = function() {
			if(_ajaxReq!=null){
				_ajaxReq.abort();
			}
		}

		var p = accessDevice.prototype;


		p.update = function() {
			this.api();
		};

		p.api = function() {
			var url = '/diacrisis/api-get-analysis-access-device';
            var param = null;
            if(_baseYearMonth!=null){
            	param = {baseYearMonth:_baseYearMonth, companyId:_conmanyId};
            }else{
            	param = {companyId:_conmanyId};	
            }
			_ajaxReq = app.api(url, param, function (res) {
				if (res.errors) {
					alert(res.errors);
				} else {
					var instance = new accessDevice();
					instance.apiResult(res.items);
				}
			});
		};

		p.apiResult = function(items) {
			$('#access-device-base-period').text('当月期間：'+items['accessDevice']['basePeriod']);
			this.updateDeviceVisitGraph(items);
			this.updateDevicePageVisitGraph(items);
			this.updateDeviceBouncesRateGraph(items);
		};

		p.updateDeviceVisitGraph = function(items) {

			var data = items['accessDevice']['data'];

			if ( data.length<=0){
				var desktop = 0; 
				var mobile  = 0; 
				var tablet  = 0; 
			}else{
				var desktop = 0; 
				if(('desktop' in data['visits']) && (data['visits']['desktop'])) {
					desktop = parseInt(data['visits']['desktop']); 
				}
				var mobile = 0; 
				if(('mobile' in data['visits']) && (data['visits']['mobile'])) {
					mobile = parseInt(data['visits']['mobile']); 
				}
				var tablet = 0; 
				if(('tablet' in data['visits']) && (data['visits']['tablet'])) {
					tablet = parseInt(data['visits']['tablet']); 
				}
			}

		    var jqplot = jQuery . jqplot(
		        'access-device-visit-graph',
		        [
		            [
		                [ 'desktop', desktop ],
		                [ 'mobile',  mobile ],
		                [ 'tablet',  tablet ],
		            ]
		        ],
		        {
		            seriesColors: [ "#82baf1", "#e5bf26", "#8dca57"],
					seriesDefaults: {
		                renderer: jQuery . jqplot . DonutRenderer,
		                shadow: false,
						rendererOptions: {
		                    padding: 0,
		                    showDataLabels: true,
		                    dataLabels: 'value',
		                    startAngle: -90,
		                    dataLabelThreshold: 0,
							dataLabelPositionFactor:1.2
						}
		            },
					axesDefaults: {
						show: true,
					},

					grid:{drawGridLines: false ,background: "#fff",shadow: false, borderWidth: 0 },
		            legend: {
		                show: true,
		                location: 's',
		                rendererOptions: {
		                    numberRows: 1
		                },
					},
	                title: {
    			        text: 'セッション',
            			show: true,
		                textAlign: 'left',
		                fontSize: '14px',
		            },					
		        }
		    );
			jqplot.replot();

			if(desktop == 0 && mobile  == 0 && tablet  == 0) {
				$(".jqplot-series-canvas").attr("id", "device-visit-graph");
				$("#access-device-visit-graph #device-visit-graph").hide();
			}

		}
		p.updateDevicePageVisitGraph = function(items) {
			var data = items['accessDevice']['data'];

			if ( data.length<=0){
				var desktop = 0; 
				var mobile  = 0; 
				var tablet  = 0; 
			}else{
				var desktop = 0; 
				if(('desktop' in data['pageviewsPerVisits']) && (data['pageviewsPerVisits']['desktop'])) {
					desktop = parseFloat(data['pageviewsPerVisits']['desktop']); 
				}
				var mobile = 0; 
				if(('mobile' in data['pageviewsPerVisits']) && (data['pageviewsPerVisits']['mobile'])) {
					mobile = parseFloat(data['pageviewsPerVisits']['mobile']); 
				}
				var tablet = 0; 
				if(('tablet' in data['pageviewsPerVisits']) && (data['pageviewsPerVisits']['tablet'])) {
					tablet = parseFloat(data['pageviewsPerVisits']['tablet']); 
				}
			}

		    var jqplot = jQuery . jqplot(
		        'access-device-page-visit-graph',
		        [
		            [ [ tablet, 'tablet' ], [ mobile, 'mobile' ], [ desktop, 'desktop' ] ]
		        ],
		        {
		            seriesColors:[ '#8dca57','#e5bf26','#82baf1', ],
		            seriesDefaults: {
		                renderer: jQuery . jqplot . BarRenderer,

		                shadow: false,
		              
		                markerOptions: {shadow: false},

		                rendererOptions: {
		                    barDirection: 'horizontal',
		                    barMargin: 25,
		                    varyBarColor: true,
		                    barWidth: 15
		                },
		                pointLabels: {
		                    show: true,
		                    location: 'sw',
		                    escapeHTML: false,
		                    formatString:'%.2f',
		                    edgeTolerance: -100
		                }
		            },
		            grid:{background: "#fff",gridLineColor: '#eee',shadow: false , borderWidth: 1},
		            axes: {
		                yaxis: {
		                    renderer: jQuery . jqplot . CategoryAxisRenderer,
		                }
		            },
	                title: {
    			        text: 'ページ/セッション(期間平均)',
            			show: true,
		                textAlign: 'left',
		                fontSize: '14px',
		            },					

		        }
		    );
			jqplot.replot();

		}
		p.updateDeviceBouncesRateGraph = function(items) {

			var data = items['accessDevice']['data'];
			if ( data.length<=0){
				var desktop = 0; 
				var mobile  = 0; 
				var tablet  = 0; 
			}else{
				var desktop = 0; 
				if(('desktop' in data['bounceRate']) && (data['bounceRate']['desktop'])) {
					desktop = parseFloat((data['bounceRate']['desktop']).slice(0,-1)); 
				}

				var mobile = 0; 
				if(('mobile' in data['bounceRate']) && (data['bounceRate']['mobile'])) {
					mobile = parseFloat((data['bounceRate']['mobile']).slice(0,-1)); 
				}
				var tablet = 0; 
				if(('tablet' in data['bounceRate']) && (data['bounceRate']['tablet'])) {
					tablet = parseFloat((data['bounceRate']['tablet']).slice(0,-1)); 
				}
			}
		    var jqplot = jQuery . jqplot(
		        'access-device-bounces-rate-graph',
		        [
		            [ [ tablet, 'tablet' ], [ mobile, 'mobile' ], [ desktop, 'desktop' ] ]
		        ],
		        {
					seriesColors:[ '#8dca57','#e5bf26','#82baf1', ],
					seriesDefaults: {
		                renderer: jQuery . jqplot . BarRenderer,

		                shadow: false,
		              
		                markerOptions: {shadow: false},

		                rendererOptions: {
		                    barDirection: 'horizontal',
		                    barMargin: 25,
		                    varyBarColor: true,
		                    barWidth: 15
		                },
		                pointLabels: {
		                    show: true,
		                    location: 'sw',
		                    escapeHTML: false,
		                    formatString:'%.2f%',
		                    edgeTolerance: -100
		                }
		            },
		            grid:{background: "#fff",gridLineColor: '#eee',shadow: false , borderWidth: 1},
		            axes: {
		                yaxis: {
		                    renderer: jQuery . jqplot . CategoryAxisRenderer,
		                }
		            },
	                title: {
    			        text: '直帰率(期間平均)',
            			show: true,
		                textAlign: 'left',
		                fontSize: '14px',
		            },					
		        }
		    );
			jqplot.replot();
		}
		return accessDevice;
	})();


	/* メディア別アクセス解析 */
	var accessMedia = (function() {
		// constructor
		var _conmanyId;		
		var _ajaxReq;		
		var  accessMedia = function() {
			_conmanyId = $('#company_id').val();
		};

		var _baseYearMonth = null;
		accessMedia.yearMonthChange = function(year,month) {
			_baseYearMonth = year+"-"+month;
			var  accessMediaObj = new accessMedia();
			accessMediaObj.update();
		};
		accessMedia.resetAjaxReq = function() {
			if(_ajaxReq!=null){
				_ajaxReq.abort();
			}
		}

		var p = accessMedia.prototype;


		p.update = function() {
			this.api();
		};

		p.api = function() {
			var url = '/diacrisis/api-get-analysis-access-media';
            var param = null;
            if(_baseYearMonth!=null){
            	param = {baseYearMonth:_baseYearMonth, companyId:_conmanyId};
            }else{
            	param = {companyId:_conmanyId};	
            }
			_ajaxReq = app.api(url, param, function (res) {
				if (res.errors) {
					alert(res.errors);
				} else {
					var instance = new accessMedia();
					instance.apiResult(res.items);
				}
			});
		};

		p.apiResult = function(items) {
			$('#access-media-base-period').text('当月期間：'+items['accessMedia']['basePeriod']);
			this.updateGraph(items);
			this.updateTable(items);
		};


		p.updateGraph = function(items) {
			var data = items['accessMedia']['data'];
			var baseYearMonth = items['accessMedia']['baseYearMonth'];
			var mediaVal=[];
			var idx=0;

			for ( idx=0; idx<9; idx++){
				var sourceMediaData=[];
				sourceMediaData['sourceMedia']=null;
				sourceMediaData['val']=null;
				mediaVal[idx] = sourceMediaData;
			}

			idx=0;
			for ( var sourceMediaKey in data){

				if (sourceMediaKey=='date') {
					continue;
				}
				if(baseYearMonth in data[sourceMediaKey]['visits']){
					var sourceMediaData=[];
					sourceMediaData['sourceMedia']=sourceMediaKey;
					sourceMediaData['val'] = parseInt(data[sourceMediaKey]['visits'][baseYearMonth]);
					mediaVal[idx] = sourceMediaData;				
					idx++;
				}
			}
		    var jqplot = jQuery . jqplot(
		        'access-media-graph',
		        [
		            [
		                [ mediaVal[0]['sourceMedia'], mediaVal[0]['val'] ],
		                [ mediaVal[1]['sourceMedia'], mediaVal[1]['val'] ],
		                [ mediaVal[2]['sourceMedia'], mediaVal[2]['val'] ],
		                [ mediaVal[3]['sourceMedia'], mediaVal[3]['val'] ],
		                [ mediaVal[4]['sourceMedia'], mediaVal[4]['val'] ],
		                [ mediaVal[5]['sourceMedia'], mediaVal[5]['val'] ],
		                [ mediaVal[6]['sourceMedia'], mediaVal[6]['val'] ],
		                [ mediaVal[7]['sourceMedia'], mediaVal[7]['val'] ],
		                [ mediaVal[8]['sourceMedia'], mediaVal[8]['val'] ],
		            ],
		        ],
		        {
		            seriesColors: [ "#82baf1", "#e5bf26", "#8dca57", "#82cddd" , "#ffec47", "#b8d200", "#bed3ca", "#ffff9e", "#98fb98"],
		            seriesDefaults: {
		                renderer: jQuery . jqplot . DonutRenderer,
		                shadow: false,
		                rendererOptions: {
		                    padding: 0,
		                    showDataLabels: true,
		                    dataLabels: 'value',
		                    startAngle: -90,
							dataLabelPositionFactor:1.1
		                }
		            },
		            // axesDefaults: {
		            //     show: true,
		            // },

		            grid:{drawGridLines: false ,background: "#fff",shadow: false, borderWidth: 0 },
		            legend: {
		                show: true,
		                location: 'e',
		                placement: 'outsideGrid'
		            },
	                title: {
    			        text: 'セッション',
            			show: true,
		                textAlign: 'left',
		                fontSize: '14px',
		            },					
		        }
		    );
		   jqplot.replot();

		};

		/*ATHOME_HP_DEV-4181 アクセスログcpc ＜広告経由での流入＞にbannerを含める*/
		/**
		 * Formula getBounceRate
		 * @param {object} bounceRate
		 * @return {string}
		 */ 
		p.getBounceRate= function(bounceRate){
			var value = (bounceRate.visits==0) ? 0 : bounceRate.bounces/bounceRate.visits*100;
        	return Math.round(value,2)+"%";
		}

		/*ATHOME_HP_DEV-4181 アクセスログcpc ＜広告経由での流入＞にbannerを含める*/
		/**
		 * Formula getPageviewsPerVisits
		 * @param {object} pageviewsPerVisits.
		 * @return {numeric}
		 */ 
		p.getPageviewsPerVisits= function(pageviewsPerVisits){
			var value = (pageviewsPerVisits.visits==0) ? 0 : pageviewsPerVisits.pageviews/pageviewsPerVisits.visits;
        	return Math.round(value,2);
		}

		p.updateTable = function(items) {
			var data = items['accessMedia']['data'];
			var mapRowId = {
				visits              : 1,
				newVisits           : 2,
				visitors            : 3,
				pageviews           : 4,
				pageviewsPerVisits  : 5,
				bounceRate         : 6,
			};
//http://www.kagua.biz/api/gaapijp.html
			var table; var row; var key; var sourceMediaKey; var cellId;
			var sourceMediaList={
//				'gorg'     : 'google/organic',
//				'gcpc'     : 'google/cpc',
//				'yorg'     : 'yahoo/organic',
//				'ycpc'     : 'yahoo/cpc',
				'cpc'     : ['cpc','banner'], // bound access media "cpc" and "banner".
				'org'     : 'organic',
				'ref'     : 'referral',
				'(none)'     : '(none)',
			};

			for(var sourceMediaId in sourceMediaList){

				table = document.getElementById("access-media-table-"+sourceMediaId); 
				row = table.rows[0];
				sourceMediaKey = sourceMediaList[sourceMediaId];

				//テーブルヘッダ
				cellId = 1;
				for( var date in data['date']){
					row.cells[cellId].innerHTML = data['date'][date].replace('-','/');
					cellId++;
				}

				for(var rowId=mapRowId.visits; rowId<=mapRowId.bounceRate; rowId++){
					switch(rowId)
					{
						case mapRowId.visits:
							key = 'visits';
							break;
						case mapRowId.newVisits:
							key = 'newVisits';
							break;
						case mapRowId.visitors:
							key = 'visitors';
							break;
						case mapRowId.pageviews:
							key = 'pageviews';
							break;
						case mapRowId.pageviewsPerVisits:
							key = 'pageviewsPerVisits';
							break;
						case mapRowId.bounceRate:
							key = 'bounceRate';
							break;
					}
					row = table.rows[rowId];
					cellId = 1;
					for( var dateKey in data['date']){

						if( !(sourceMediaList[sourceMediaId] in data) ){
							row.cells[cellId].innerHTML = 0;

							/*begin ATHOME_HP_DEV-4181 アクセスログcpc ＜広告経由での流入＞にbannerを含める*/
							if(Array.isArray(sourceMediaList[sourceMediaId])){
								date = data['date'][dateKey];

								/*init pageviewsPerVisits*/
								var pageviewsPerVisits={
									visits : 0,
									pageviews : 0
								};

								/*init bounceRate*/
								var bounceRate={
									visits : 0,
									bounces : 0
								};

								/*summary access multiple*/
								for(var indexKey in sourceMediaKey){
									var mediaKey = sourceMediaKey[indexKey];
									if(mediaKey in data){
										if(date in data[mediaKey][key]){
											switch(key){
												/* case bound value pageviewsPerVisits*/
												case 'pageviewsPerVisits' : 
													pageviewsPerVisits.visits += data[mediaKey]['visits'][date];
													pageviewsPerVisits.pageviews += data[mediaKey]['pageviews'][date];
													row.cells[cellId].innerHTML  =  commafy(this.getPageviewsPerVisits(pageviewsPerVisits));
													break;
												/* case bound value bounceRate*/
												case 'bounceRate' : 
													bounceRate.visits += data[mediaKey]['visits'][date];
													bounceRate.bounces += data[mediaKey]['bounces'][date];
													row.cells[cellId].innerHTML  =  commafy(this.getBounceRate(bounceRate));
													break;
												default:
													row.cells[cellId].innerHTML  = 
													commafy(parseInt(row.cells[cellId].innerHTML.replace(/,/g, '')) + data[mediaKey][key][date]);
													break;
											}
										}
									}
								}
							}
							/*end ATHOME_HP_DEV-4181 アクセスログcpc ＜広告経由での流入＞にbannerを含める*/

						} else{
							date = data['date'][dateKey];
							if(date in data[sourceMediaKey][key]){
								row.cells[cellId].innerHTML = commafy(data[sourceMediaKey][key][date]);
							}else{
								row.cells[cellId].innerHTML = 0;
							}
						}
						cellId++;
					}
				}
			}
		};
		return accessMedia;
	})();


	/* 月間キーワードTOP20 */
	var accessKeywordRanking = (function() {

		// constructor
		var _conmanyId;		
		var _ajaxReq;
		var  accessKeywordRanking = function() {
			_conmanyId = $('#company_id').val();
		};

		var _baseYearMonth = null;
		accessKeywordRanking.yearMonthChange = function(year,month) {
			_baseYearMonth = year+"-"+month;
			var  accessMediaObj = new accessKeywordRanking();
			 accessMediaObj.update();
		};
		accessKeywordRanking.resetAjaxReq = function() {
			if(_ajaxReq!=null){
				_ajaxReq.abort();
			}
		}

		var p = accessKeywordRanking.prototype;


		p.update = function() {
			this.api();
		};

		p.api = function() {
			var url = '/diacrisis/api-get-analysis-access-keyword-ranking';
            var param = null;
            if(_baseYearMonth!=null){
            	param = {baseYearMonth:_baseYearMonth, companyId:_conmanyId};
            }else{
            	param = {companyId:_conmanyId};	
            }
			_ajaxReq = app.api(url, param, function (res) {
				if (res.errors) {
					alert(res.errors);
				} else {
					var instance = new accessKeywordRanking();
					instance.apiResult(res.items);
				}
			});
		};

		p.apiResult = function(items) {
			$('#access-keyword-ranking-base-period').text('当月期間：'+items['accessKeywordRanking']['basePeriod']);
			this.updateTable(items);
		};


		p.updateTable = function(items) {
			var data = items['accessKeywordRanking']['data'];

			var table = document.getElementById("access-keyword-ranking-table"); 

			var mapColId = {
				'keyword'             : 1,
				'visits'              : 2,
				'newVisits'           : 3,
				'visitors'     : 4,
				'pageviews'           : 5,
				'pageviewsPerVisits'  : 6,
				'bounceRate'         : 7,
			};

			for(var rank=1; rank<=20; rank++){
				if (rank<=data.length){
					table.rows[rank].cells[mapColId['keyword']].innerHTML=data[rank-1]['keyword'];
					table.rows[rank].cells[mapColId['visits']].innerHTML=commafy(data[rank-1]['visits']);
					table.rows[rank].cells[mapColId['newVisits']].innerHTML=commafy(data[rank-1]['newVisits']);
					table.rows[rank].cells[mapColId['visitors']].innerHTML=commafy(data[rank-1]['visitors']);
					table.rows[rank].cells[mapColId['pageviews']].innerHTML=commafy(data[rank-1]['pageviews']);
					table.rows[rank].cells[mapColId['pageviewsPerVisits']].innerHTML=data[rank-1]['pageviewsPerVisits'];
					table.rows[rank].cells[mapColId['bounceRate']].innerHTML=data[rank-1]['bounceRate'];
				}else{
					table.rows[rank].cells[mapColId['keyword']].innerHTML="-";
					table.rows[rank].cells[mapColId['visits']].innerHTML="-";
					table.rows[rank].cells[mapColId['newVisits']].innerHTML="-";
					table.rows[rank].cells[mapColId['visitors']].innerHTML="-";
					table.rows[rank].cells[mapColId['pageviews']].innerHTML="-";
					table.rows[rank].cells[mapColId['pageviewsPerVisits']].innerHTML="-";
					table.rows[rank].cells[mapColId['bounceRate']].innerHTML="-";
				}
			}
		};
		return accessKeywordRanking;
	})();


	/* 月間ページアクセスTOP20 ⇒ ページ別セッション数 TOP20 */
	var accessPageRanking = (function() {

		// constructor
		var _conmanyId;		
		var _ajaxReq;		
		var  accessPageRanking = function() {
			_conmanyId = $('#company_id').val();
		};

		var _baseYearMonth = null;
		accessPageRanking.yearMonthChange = function(year,month) {
			_baseYearMonth = year+"-"+month;
			var  accessMediaObj = new accessPageRanking();
			 accessMediaObj.update();
		};
		accessPageRanking.resetAjaxReq = function() {
			if(_ajaxReq!=null){
				_ajaxReq.abort();
			}
		}

		var p = accessPageRanking.prototype;


		p.update = function() {
			this.api();
		};

		p.api = function() {
			var url = '/diacrisis/api-get-analysis-access-page-ranking';
            var param = null;
            if(_baseYearMonth!=null){
            	param = {baseYearMonth:_baseYearMonth, companyId:_conmanyId};
            }else{
            	param = {companyId:_conmanyId};	
            }
			_ajaxReq = app.api(url, param, function (res) {
				if (res.errors) {
					alert(res.errors);
				} else {
					var instance = new accessPageRanking();
					instance.apiResult(res.items);
				}
			});
		};

		p.apiResult = function(items) {
			$('#access-page-ranking-base-period').text('当月期間：'+items['accessPageRanking']['basePeriod']);
			this.updateTable(items);
		};


		p.updateTable = function(items) {

			var data = items['accessPageRanking']['data'];

			var table = document.getElementById("access-page-ranking-table"); 

			var mapColId = {
				'pagePath'            : 1,
				'visits'              : 2,
				'newVisits'           : 3,
				'visitors'            : 4,
				'pageviews'           : 5,
				'pageviewsPerVisits'  : 6,
				'bounceRate'         : 7,
			};

			for(var rank=1; rank<=20; rank++){
				if (rank<=data.length){

					var anchor = document.createElement("a");
					anchor.href   = "http://"+ $("#domain").val() + data[rank-1]['pagePath'];
					anchor.target = "_blank";
					anchor.text   = data[rank-1]['pagePath'];
					table.rows[rank].cells[mapColId['pagePath']].innerHTML="";
					table.rows[rank].cells[mapColId['pagePath']].appendChild(anchor);
					table.rows[rank].cells[mapColId['visits']].innerHTML=commafy(data[rank-1]['visits']);
					table.rows[rank].cells[mapColId['newVisits']].innerHTML=commafy(data[rank-1]['newVisits']);
					table.rows[rank].cells[mapColId['visitors']].innerHTML=commafy(data[rank-1]['visitors']);
					table.rows[rank].cells[mapColId['pageviews']].innerHTML=commafy(data[rank-1]['pageviews']);
					table.rows[rank].cells[mapColId['pageviewsPerVisits']].innerHTML=data[rank-1]['pageviewsPerVisits'];
					table.rows[rank].cells[mapColId['bounceRate']].innerHTML=data[rank-1]['bounceRate'];
				}else{
					table.rows[rank].cells[mapColId['pagePath']].innerHTML="-";
					table.rows[rank].cells[mapColId['visits']].innerHTML="-";
					table.rows[rank].cells[mapColId['newVisits']].innerHTML="-";
					table.rows[rank].cells[mapColId['visitors']].innerHTML="-";
					table.rows[rank].cells[mapColId['pageviews']].innerHTML="-";
					table.rows[rank].cells[mapColId['pageviewsPerVisits']].innerHTML="-";
					table.rows[rank].cells[mapColId['bounceRate']].innerHTML="-";
				}
			}
		};
		return accessPageRanking;
	})();

	/* ページ別ページビュー数 TOP20 */
	var accessPageView = (function() {

		// constructor
		var _conmanyId;		
		var _ajaxReq;		
		var  accessPageView = function() {
			_conmanyId = $('#company_id').val();
		};

		var _baseYearMonth = null;
		accessPageView.yearMonthChange = function(year,month) {
			_baseYearMonth = year+"-"+month;
			var  accessMediaObj = new accessPageView();
			 accessMediaObj.update();
		};
		accessPageView.resetAjaxReq = function() {
			if(_ajaxReq!=null){
				_ajaxReq.abort();
			}
		}

		var p = accessPageView.prototype;


		p.update = function() {
			this.api();
		};

		p.api = function() {
			var url = '/diacrisis/api-get-analysis-access-page-view';
            var param = null;
            if(_baseYearMonth!=null){
            	param = {baseYearMonth:_baseYearMonth, companyId:_conmanyId};
            }else{
            	param = {companyId:_conmanyId};	
            }
			_ajaxReq = app.api(url, param, function (res) {
				if (res.errors) {
					alert(res.errors);
				} else {
					var instance = new accessPageView();
					instance.apiResult(res.items);
				}
			});
		};

		p.apiResult = function(items) {
			$('#access-page-view-base-period').text('当月期間：'+items['accessPageView']['basePeriod']);
			this.updateTable(items);
		};


		p.updateTable = function(items) {

			var data = items['accessPageView']['data'];

			var table = document.getElementById("access-page-view-table"); 

			var mapColId = {
				'pagePath'        : 1,
				'pageviews'       : 2,
				'visits' 		  : 3,
				'avgTimeOnPage'   : 4,
				'exitsRate'       : 5,
			};

			for(var rank=1; rank<=20; rank++){
				if (rank<=data.length){

					var anchor = document.createElement("a");
					anchor.href   = "http://"+ $("#domain").val() + data[rank-1]['pagePath'];
					anchor.target = "_blank";
					anchor.text   = data[rank-1]['pagePath'];
					table.rows[rank].cells[mapColId['pagePath']].innerHTML="";
					table.rows[rank].cells[mapColId['pagePath']].appendChild(anchor);

					table.rows[rank].cells[mapColId['pageviews']].innerHTML=commafy(data[rank-1]['pageviews']);
					table.rows[rank].cells[mapColId['visits']].innerHTML=commafy(data[rank-1]['visits']);
					table.rows[rank].cells[mapColId['avgTimeOnPage']].innerHTML=data[rank-1]['avgTimeOnPage'];
					table.rows[rank].cells[mapColId['exitsRate']].innerHTML=data[rank-1]['exitsRate'] + "%";
				}else{
					table.rows[rank].cells[mapColId['pagePath']].innerHTML="-";
					table.rows[rank].cells[mapColId['pageviews']].innerHTML="-";
					table.rows[rank].cells[mapColId['visits']].innerHTML="-";
					table.rows[rank].cells[mapColId['avgTimeOnPage']].innerHTML="-";
					table.rows[rank].cells[mapColId['exitsRate']].innerHTML="-";
				}
			}
		};
		return accessPageView;
	})();

	// onLoad
	$(window).on("beforeunload",function(){
		// もとの通信を強制終了する
		summary.resetAjaxReq();
		access.resetAjaxReq();
		accessDevice.resetAjaxReq();
		accessMedia.resetAjaxReq();
		//accessKeywordRanking.resetAjaxReq();
		accessPageRanking.resetAjaxReq();
		accessPageView.resetAjaxReq();
	});

	// onLoad
	$(window).load(function() {
		// アナリティクス設定がなければなにもしない
		if ( $('#has_analytics_tag').val() == false){
			return;
		}
		

		// サマリ
	    updateYearMonthOption('summary-year','summary-month',null,null);
		var summaryObj = new summary();
		summaryObj.update();

		// アクセス
	    updateYearMonthOption('access-year','access-month',null,null);
		var accessObj = new access();
		accessObj.update();

		// デバイス別アクセス
	    updateYearMonthOption('access-device-year','access-device-month',null,null);
		var accessDeviceObj = new accessDevice();
		accessDeviceObj.update();

		// メディア別アクセス
	    updateYearMonthOption('access-media-year','access-media-month',null,null);
		var accessMediaObj = new accessMedia();
		accessMediaObj.update();

		// 月刊キーワードベスト２０
	    //updateYearMonthOption('access-keyword-ranking-year','access-keyword-ranking-month',null,null);
		//var accessKeywordRankingObj = new accessKeywordRanking();
		//accessKeywordRankingObj.update();

		// 月刊ページベスト２０
	    updateYearMonthOption('access-page-ranking-year','access-page-ranking-month',null,null);
		var accessPageRankingObj = new accessPageRanking();
		accessPageRankingObj.update();

		// 月刊ページベスト２０
	    updateYearMonthOption('access-page-view-year','access-page-view-month',null,null);
		var accessPageViewObj = new accessPageView();
		accessPageViewObj.update();

	});


	// サマリーの年月変更イベントハンドラ
	$(function($) {
	  $('#summary-year,#summary-month').change(function() {
		// アナリティクスタグなければなにもしない
		if ( $('#has_analytics_tag').val() == false){
			return;
		}
	    var year  = $('#summary-year option:selected').val();
	    var month = $('#summary-month option:selected').val();
	    var yearMonth = updateYearMonthOption('summary-year','summary-month',year,month);
	    summary.yearMonthChange(yearMonth['year'],yearMonth['month']);
	  });
	});

	// アクセス状況の年月変更イベントハンドラ
	$(function($) {
	  $('#access-year,#access-month').change(function() {
		// アナリティクスタグなければなにもしない
		if ( $('#has_analytics_tag').val() == false){
			return;
		}
	    var year  = $('#access-year option:selected').val();
	    var month = $('#access-month option:selected').val();
	    var yearMonth = updateYearMonthOption('access-year','access-month',year,month);
	    access.yearMonthChange(yearMonth['year'],yearMonth['month']);
	  });
	});

	// デバイス別アクセス状況の年月変更イベントハンドラ
	$(function($) {
	  $('#access-device-year,#access-device-month').change(function() {
		// アナリティクスタグなければなにもしない
		if ( $('#has_analytics_tag').val() == false){
			return;
		}
	    var year  = $('#access-device-year option:selected').val();
	    var month = $('#access-device-month option:selected').val();
	    var yearMonth = updateYearMonthOption('access-device-year','access-device-month',year,month);
	    accessDevice.yearMonthChange(yearMonth['year'],yearMonth['month']);
	  });
	});

	// メディア別アクセスの年月変更イベントハンドラ
	$(function($) {
	  $('#access-media-year,#access-media-month').change(function() {
		// アナリティクスタグなければなにもしない
		if ( $('#has_analytics_tag').val() == false){
			return;
		}
	    var year  = $('#access-media-year option:selected').val();
	    var month = $('#access-media-month option:selected').val();
	    var yearMonth = updateYearMonthOption('access-media-year','access-media-month',year,month);
	    accessMedia.yearMonthChange(yearMonth['year'],yearMonth['month']);

	  });
	});

	// 月間キーワードTOP20の年月変更イベントハンドラ
	/*
	$(function($) {
	  $('#access-keyword-ranking-year,#access-keyword-ranking-month').change(function() {
		// アナリティクスタグなければなにもしない
		if ( $('#has_analytics_tag').val() == false){
			return;
		}
	    var year  = $('#access-keyword-ranking-year option:selected').val();
	    var month = $('#access-keyword-ranking-month option:selected').val();
	    var yearMonth = updateYearMonthOption('access-keyword-ranking-year','access-keyword-ranking-month',year,month);
	    accessKeywordRanking.yearMonthChange(yearMonth['year'],yearMonth['month']);

	  });
	});
	*/

	// 月刊ページアクセスTOP20の年月変更イベントハンドラ
	$(function($) {
	  $('#access-page-ranking-year,#access-page-ranking-month').change(function() {
		// アナリティクスタグなければなにもしない
		if ( $('#has_analytics_tag').val() == false){
			return;
		}
	    var year  = $('#access-page-ranking-year option:selected').val();
	    var month = $('#access-page-ranking-month option:selected').val();
	    var yearMonth = updateYearMonthOption('aaccess-page-ranking-year','access-page-ranking-month',year,month);
	    accessPageRanking.yearMonthChange(yearMonth['year'],yearMonth['month']);
	  });
	});

	// 月刊ページビューTOP20の年月変更イベントハンドラ
	$(function($) {
	  $('#access-page-view-year,#access-page-view-month').change(function() {
		// アナリティクスタグなければなにもしない
		if ( $('#has_analytics_tag').val() == false){
			return;
		}
	    var year  = $('#access-page-view-year option:selected').val();
	    var month = $('#access-page-view-month option:selected').val();
	    var yearMonth = updateYearMonthOption('aaccess-page-view-year','access-page-view-month',year,month);
	    accessPageView.yearMonthChange(yearMonth['year'],yearMonth['month']);
	  });
	});

	// 年月選択肢を更新する
	var updateYearMonthOption = function(yearId,monthId,year,month) {

		// 月の選択肢を更新
		var nowDate = new Date();
		var nowYear = nowDate.getFullYear();
		var nowMonth = nowDate.getMonth()+1;

		$('#'+monthId+' > option').remove();
		var optionMonthStart = 1;
		var optionMonthEnd = 12;

		if(year==null && month==null){
			year  = nowYear;
			month = nowMonth;
			$('#'+yearId).val(year);
			$('#'+monthId).val(month);
		}
		if (year==nowYear){
			optionMonthEnd = nowMonth;
		}
		if (month>optionMonthEnd){
			month = optionMonthEnd;
		}
	    for(var option=optionMonthStart;  option<=optionMonthEnd; option++){
	    	$('#'+monthId).append($('<option>').html(option+"月").val(option));
	    }
		$('#'+yearId).val(year);
	    $('#'+monthId).val(month);

	    var yearMonth={'year':('0000'+year).slice(-4),'month':('00'+month).slice(-2)};
	    return yearMonth;
	    
	};
});
