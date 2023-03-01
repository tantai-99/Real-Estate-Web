/**
 * @license Copyright (c) 2003-2015, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here.
	// For complete reference see:
	// http://docs.ckeditor.com/#!/api/CKEDITOR.config

	config.colorButton_enableAutomatic			= false		;
	config.colorButton_colorsPerRow	= 8 ;
	config.colorButton_colors		= 
		'000000,633b14,c60019,dc7200,245d20,008363,174e9e,581e81,'	+
		'626262,903d00,e20101,ff9000,53a949,19d1b5,0074bf,9000ff,'	+
		'bfbfbf,ac8a59,ff0000,ffcc01,12cd12,34f9c4,019fe6,c415c2,'	+
		'e5e5e5,c3b19a,ff8eb8,fff001,a0c238,97fce3,7fc2ef,d589e4,'	+
		'FFFFFF,f9e9cd,ffd7e4,ffffaf,c3f5bc,d9fff3,b0d7f4,f3ddff'	;

	config.fontSize_sizes	= '12/12px;14/14px;16/16px;18/18px;20/20px;22/22px;24/24px;28/28px;36/36px;48/48px'	;

	config.language			= 'ja';
	
	config.toolbarLocation	= 'bottom'		;
	
	config.skin				= 'bootstrapck';
	
	config.contentsCss		= [ '/js/libs/ckeditor/style.css' ] ;
	
	// default plugins
	// config.plugins = 'dialogui,dialog,about,basicstyles,clipboard,button,toolbar,enterkey,entities,floatingspace,wysiwygarea,indent,indentlist,fakeobjects,link,list,undo';
	config.plugins = 'basicstyles,clipboard,button,toolbar,enterkey,entities,floatingspace,wysiwygarea,indent,indentlist,fakeobjects';
	config.extraPlugins = 'justify,custom_color,custom_link,colorbutton,colordialog,font,custom_copy'	;
	
	config.toolbar = 'Basic';
	
	config.toolbar_Basic = [
		[ 'custom_link'												] ,
		[ 'Bold'													] ,
		[ 'JustifyLeft', '-', 'JustifyCenter', '-', 'JustifyRight'	] ,
		[ 'TextColor', 'FontSize'									]
	] ;
	
	config.toolbar_Link = [
		[ 'custom_link'		]
    ] ;
    
    config.toolbar_ListTitle1 = [
        [ 'Bold'        ] ,
        [ 'TextColor'   ]
    ] ;
    
    config.toolbar_ListTitle2 = [
        [ 'Bold'        ] ,
        [ 'TextColor'   ],
        [ 'custom_copy' ]
	] ;
	
	config.coreStyles_bold = {
		element: 'strong',
		attributes: {
			'class': 'tx-stress'
		}
	} ;
	
	// Use the classes 'AlignLeft', 'AlignCenter', 'AlignRight', 'AlignJustify'
	config.justifyClasses = [
		'tal', 'tac', 'tar'
	] ;
};
