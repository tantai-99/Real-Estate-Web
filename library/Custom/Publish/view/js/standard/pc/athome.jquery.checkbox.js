$.fn.athome_checkbox_engine=function(config,checkParents,callback){var option={parent_selector:config.parent_selector?config.parent_selector:null,wrapper_selector:config.wrapper_selector?config.wrapper_selector:null,target_selector:config.target_selector?config.target_selector:"li input"};checkParents=checkParents||typeof checkParents==="undefined"?true:false;var $elems=$(this);$elems.each(function(i,v){var $this=$(this);var $parent=$this.find(option.parent_selector);var $wrapper=$this.find(option.wrapper_selector);$parent.on("change",function(e){var checked=$(this).prop("checked");$wrapper.find(option.target_selector+":not(:disabled)").prop("checked",checked);if(checkParents){$elems.find(option.parent_selector).prop("checked",checked)}if(typeof callback!=="undefined"){callback.method(callback.arg)}});$wrapper.find(option.target_selector+'[type="checkbox"]').on("change",function(){$parent.prop("checked",$wrapper.find(option.target_selector+":not(:disabled)").size()===0)})});return this};