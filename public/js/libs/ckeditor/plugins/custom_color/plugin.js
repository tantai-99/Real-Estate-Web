(function () {

	function command( name, cls ) {
		this.name = name;
		this.context = 'span';
		
		this.allowedContent = {};
		this.allowedContent['span'] = {
			classes: cls
		};
		
		this.otherStyles = [];
		
		var classes = ['tx-color1', 'tx-color2', 'tx-color3'];
		var _style;
		for (var i=0,l=classes.length;i<l;i++) {
			_style = new CKEDITOR.style({
				element: 'span',
				attributes: {
					'class': classes[i]
				}
			});
			
			if (classes[i] === cls) {
				this.style = _style;
			}
			else {
				this.otherStyles.push(_style);
			}
		}
	}
	
	command.prototype.exec = function (editor) {
		editor.focus();
		editor.fire( 'saveSnapshot' );
		
		this._toggleStyle(editor);
		
		setTimeout( function() {
			editor.fire( 'saveSnapshot' );
		}, 0 );
	};
	
	command.prototype.refresh = function (editor, path) {
		this.setState( this.style.checkActive( path, editor ) ? CKEDITOR.TRISTATE_ON : CKEDITOR.TRISTATE_OFF );
	};
	
	command.prototype._toggleStyle = function (editor) {
		if (this.style.checkActive( editor.elementPath(), editor )) {
			editor.removeStyle( this.style );
		}
		else {
			var selection = editor.getSelection();
			var range = selection.getRanges()[ 0 ];
			
			for (var i=0,l=this.otherStyles.length;i<l;i++) {
				this.otherStyles[i].removeFromRange( range, editor );
				range.select();
			}
			editor.applyStyle( this.style );
		}
	};

	CKEDITOR.plugins.add( 'custom_color', {
		lang: 'ja',
		icons: 'r,b,g',
		hidpi: false,
		init: function( editor ) {
			var config = editor.config,
			lang = editor.lang.custom_color;
			
			var commands = [
				['赤', 'r', 'tx-color1'],
				['青', 'b', 'tx-color2'],
				['緑', 'g', 'tx-color3']
			];
			
			for (var i=0,l=commands.length;i<l;i++) {
				editor.addCommand(commands[i][1], new command(commands[i][1], commands[i][2]));
				editor.ui.addButton(commands[i][1], {
					label: commands[i][0],
					command: commands[i][1],
					toolbar: 'custom_style,' + i + '0'
				});
			}
		}
	});

})();
