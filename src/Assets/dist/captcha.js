window.googleV3Captcha = {
	siteKey: '',
	inited: false,
	init: function (siteKey) {
		if (this.inited) {
			return;
		}
		this.inited = true;
		this.siteKey = siteKey;
	},
	getCaptcha: function (action) {
		return grecaptcha.execute(this.siteKey, {action: action});
	},
	retrieveCaptcha: function (formId, action) {
		var _this = this;
	    this.getCaptcha(formId).then(function (token) {
	        document.getElementById(formId).querySelector("[name=_recaptcha_response]").value = token;
	    });
		setTimeout(function () {
			_this.retrieveCaptcha(formId, action);
		}, 120000);
	}
}