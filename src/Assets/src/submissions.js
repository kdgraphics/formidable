import PreviewModal from './js/modal.js';
import Submissions from './js/submissions.js';

import './css/submissions.scss';

$(document).ready(function(){
	window.InspireForms = {
		Submissions: new Submissions,
		Modal: new PreviewModal({autoShow: false})
	};
});