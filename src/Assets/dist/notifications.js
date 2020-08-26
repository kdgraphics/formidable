/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 0);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./src/Assets/src/js/notification.js":
/*!*******************************************!*\
  !*** ./src/Assets/src/js/notification.js ***!
  \*******************************************/
/*! no static exports found */
/***/ (function(module, exports) {

eval("class Notification {\n  constructor() {\n    this.form = $('.notification-form');\n    this.list = $('#notification-list');\n    this.initMethodField();\n    this.initDelete();\n  }\n\n  initDelete() {\n    var _this = this;\n\n    this.list.find('a.delete').click(function () {\n      let link = $(this);\n      let name = $(this).data('name');\n\n      if (confirm(Craft.t('inspireforms', 'Are you sure you want to delete the notification {name}', {\n        name: name\n      }))) {\n        let data = {};\n        data[Craft.csrfTokenName] = Craft.csrfTokenValue;\n        let url = $(this).data('url');\n        $.post(url, data, null, 'json').done(function (data) {\n          if (data.success) {\n            Craft.cp.displayNotice(Craft.t('inspireforms', 'Notification successfully deleted.'));\n            link.parent().parent().remove();\n\n            var count = _this.list.find('tbody tr').length;\n\n            if (length == 0) {\n              _this.list.remove();\n\n              $('#no-notification').show();\n            }\n          }\n        }).fail(function (data) {\n          Craft.cp.displayError(data.responseJSON.error);\n        });\n      }\n    });\n  }\n\n  initMethodField() {\n    this.changeMethodField();\n\n    let _this = this;\n\n    this.form.find('#method').change(function () {\n      _this.changeMethodField();\n    });\n  }\n\n  changeMethodField() {\n    let show = 'html';\n    let hide = 'template';\n\n    if (this.form.find('#method').val() == 'template') {\n      show = 'template';\n      hide = 'html';\n    }\n\n    this.form.find('#' + show).attr('required', 'required');\n    this.form.find('#' + show + '-field').show();\n    this.form.find('#' + hide).removeAttr('required');\n    this.form.find('#' + hide + '-field').hide();\n  }\n\n}\n\n$(document).ready(function () {\n  new Notification();\n});\n\n//# sourceURL=webpack:///./src/Assets/src/js/notification.js?");

/***/ }),

/***/ "./src/Assets/src/notifications.js":
/*!*****************************************!*\
  !*** ./src/Assets/src/notifications.js ***!
  \*****************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _js_notification_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./js/notification.js */ \"./src/Assets/src/js/notification.js\");\n/* harmony import */ var _js_notification_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_js_notification_js__WEBPACK_IMPORTED_MODULE_0__);\n\n\n//# sourceURL=webpack:///./src/Assets/src/notifications.js?");

/***/ }),

/***/ 0:
/*!***********************************************!*\
  !*** multi ./src/Assets/src/notifications.js ***!
  \***********************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("module.exports = __webpack_require__(/*! /Users/boris/Web/vhosts-php7/craft/vendor/inspire/forms/src/Assets/src/notifications.js */\"./src/Assets/src/notifications.js\");\n\n\n//# sourceURL=webpack:///multi_./src/Assets/src/notifications.js?");

/***/ })

/******/ });