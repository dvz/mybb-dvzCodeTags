let prismjsCodeFormatter = {
	options: JSON.parse(document.currentScript.dataset.options),
	lang: JSON.parse(document.currentScript.dataset.lang),
};

if (Prism.plugins.autoloader !== undefined) {
    Prism.plugins.autoloader.languages_path = prismjsCodeFormatter.options.components_directory;
}

if (Prism.plugins.toolbar !== undefined) {
	Prism.plugins.toolbar.registerButton("select-code", function (env) {
		let button = document.createElement("button");
		button.innerHTML = prismjsCodeFormatter.lang.dvz_code_tags_select_all;

		button.addEventListener("click", function () {
			if (document.body.createTextRange) {
				let range = document.body.createTextRange();
				range.moveToElementText(env.element);
				range.select();
			} else if (window.getSelection) {
				let selection = window.getSelection();
				let range = document.createRange();
				range.selectNodeContents(env.element);
				selection.removeAllRanges();
				selection.addRange(range);
			}
		});

		return button;
	});
}
