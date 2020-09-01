import PreviewModal from './js/modal.js';
import Spams from './js/spam.js';

import './css/spam.scss';

$(document).ready(function(){
	window.InspireForms = {
		Spams: new Spams,
		Modal: new PreviewModal({autoShow: false})
	};
});