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
	retrieveCaptcha: function (formId, action) {
		var _this = this;
	    grecaptcha.execute(this.siteKey, { action: action }).then(function (token) {
	        document.getElementById(formId).querySelector("[name=_recaptcha_response]").value = token;
	    });
		setTimeout(function () {
			_this.retrieveCaptcha(formId, action);
		}, 120000);
	}
}