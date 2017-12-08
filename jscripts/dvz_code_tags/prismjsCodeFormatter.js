var prismjsCodeFormatter = {};

$.each(document.currentScript.attributes, function() {
    if (this.specified && this.name == 'data-options') {
        prismjsCodeFormatter.options = JSON.parse(this.value);
    } else if (this.specified && this.name == 'data-lang') {
        prismjsCodeFormatter.lang = JSON.parse(this.value);
    }
});

if (Prism.plugins.autoloader !== undefined) {
    Prism.plugins.autoloader.languages_path = prismjsCodeFormatter.options.components_directory;
}

if (Prism.plugins.toolbar !== undefined) {
    Prism.plugins.toolbar.registerButton("select-code", function(env) {
    	var button = document.createElement("button");
    	button.innerHTML = prismjsCodeFormatter.lang.dvz_code_tags_select_all;

    	button.addEventListener("click", function () {
    		if (document.body.createTextRange) {
    			var range = document.body.createTextRange();
    			range.moveToElementText(env.element);
    			range.select();
    		} else if (window.getSelection) {
    			var selection = window.getSelection();
    			var range = document.createRange();
    			range.selectNodeContents(env.element);
    			selection.removeAllRanges();
    			selection.addRange(range);
    		}
    	});

    	return button;
    });
}
