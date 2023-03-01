(function () {
'use strict';

function _isEmptyElement(elem) {
	if (!elem.childNodes.length) {
		return !(elem.nodeType === wysihtml5.TEXT_NODE && !elem.data.match(/^\s*$/));
	}
	for (var i=0,l=elem.childNodes.length;i<l;i++) {
		if (!_isEmptyElement(elem.childNodes[i])) {
			return false;
		}
	}
	return true;
}

(function (wysihtml5) {
	wysihtml5.commands.customClassBlock = {
		exec: function(composer, command, value) {
			var splitted_value = value.split('|');
			var nodeName = splitted_value[0];
			var className = splitted_value[1];
			return wysihtml5.commands.formatBlock.exec(composer, command, nodeName, className, new RegExp(className, "g"));
		},
		state: function(composer, command, value) {
			var splitted_value = value.split('|');
			var nodeName = splitted_value[0];
			var className = splitted_value[1];
			return wysihtml5.commands.formatBlock.state(composer, command, nodeName, className, new RegExp(className, "g"));
		}
	};

}(wysihtml5));

(function (wysihtml5) {
	var VALUES = [1, 2, 3];
	var REG_EXP_PREFIX = 'tx-color';
	var REG_EXP = new RegExp(REG_EXP_PREFIX + '[' + (VALUES.join('')) + ']', 'g');
	wysihtml5.commands.customColor = {
		exec: function(composer, command, color) {
			var state;
			var isSame = false;
			for (var i=0,l=VALUES.length;i<l;i++) {
				state = this.state(composer, command, VALUES[i]);
				if (state && state[0]) {
					if (color == VALUES[i]) {
						isSame = true;
					}
					if (_isEmptyElement(state[0])) {
						state[0].parentNode.removeChild(state[0]);
					}
					else {
						wysihtml5.commands.formatInline.exec(composer, 'formatInline', "span", "", REG_EXP);
					}
				}
			}
			if (isSame) {
				return;
			}
			return wysihtml5.commands.formatInline.exec(composer, 'formatInline', "span", "tx-color" + color, REG_EXP);
		},
		
		state: function(composer, command, color) {
			return wysihtml5.commands.formatInline.state(composer, 'formatInline', "span", "tx-color" + color, REG_EXP);
		}
	};

}(wysihtml5));

(function (wysihtml5) {
	var VALUES = ['l','c','r'];
	var REG_EXP_PREFIX = 'ta';
	var REG_EXP = new RegExp(REG_EXP_PREFIX + '[' + (VALUES.join('')) + ']', 'g');
	
	wysihtml5.commands.customAlign = {
		exec: function(composer, command, pos) {
			var targetClass = REG_EXP_PREFIX + pos;
			var state = wysihtml5.commands.formatBlock.state(composer, 'formatBlock', 'p');
			if (state && _isEmptyElement(state)) {
				if (state.className.match(new RegExp(targetClass))) {
					wysihtml5.dom.replaceWithChildNodes(state);
				}
				else {
					state.className = targetClass;
				}
				return;
			}
			
			return wysihtml5.commands.formatBlock.exec(composer, 'formatBlock', "p", "ta" + pos, REG_EXP);
		},
		
		state: function(composer, command, pos) {
			return wysihtml5.commands.formatBlock.state(composer, 'formatBlock', "p", "ta" + pos, REG_EXP);
		}
	};

}(wysihtml5));

(function (wysihtml5) {
	var REG_EXP = /tx-stress/g;
	
	wysihtml5.commands.customStrong = {
		exec: function(composer, command, value) {
			var state = this.state(composer, command, value);
			if (state && state[0]) {
				if (_isEmptyElement(state[0])) {
					wysihtml5.dom.replaceWithChildNodes(state[0]);
				}
				else {
					return wysihtml5.commands.formatInline.exec(composer, command, "strong", "", REG_EXP);
				}
				return;
			}
			return wysihtml5.commands.formatInline.exec(composer, command, "strong", "tx-stress" + value, REG_EXP);
		},
		
		state: function(composer, command, value) {
			return wysihtml5.commands.formatInline.state(composer, command, "strong", "tx-stress" + value, REG_EXP);
		}
	};

}(wysihtml5));

(function (wysihtml5) {
	var REG_EXP = /tx-stress/g;
	
	wysihtml5.commands.customCreateLink = {
		exec: function(composer, command, value) {
			return;
		},
		
		state: function(composer, command, value) {
			return wysihtml5.commands.createLink.state(composer, 'createLink', "a");
		}
	};

}(wysihtml5));

})(wysihtml5);
