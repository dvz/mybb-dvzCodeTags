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

let highlightDeferredCode = function () {
	if (window.getSelection().toString().length === 0) {
		Prism.highlightElement(this.querySelector('code'));
		this.removeAttribute('data-deferred');
		this.removeEventListener('click', highlightDeferredCode);
	}
};

document.querySelectorAll('.block-code:not([data-deferred])').forEach(e => Prism.highlightElement(e.querySelector('code')));
document.querySelectorAll('.block-code[data-deferred]').forEach(e => e.addEventListener('click', highlightDeferredCode));