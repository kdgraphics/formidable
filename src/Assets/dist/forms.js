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
/******/ 	return __webpack_require__(__webpack_require__.s = 1);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./src/Assets/src/forms.js":
/*!*********************************!*\
  !*** ./src/Assets/src/forms.js ***!
  \*********************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _js_forms_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./js/forms.js */ \"./src/Assets/src/js/forms.js\");\n/* harmony import */ var _js_forms_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_js_forms_js__WEBPACK_IMPORTED_MODULE_0__);\n\n\n//# sourceURL=webpack:///./src/Assets/src/forms.js?");

/***/ }),

/***/ "./src/Assets/src/js/forms.js":
/*!************************************!*\
  !*** ./src/Assets/src/js/forms.js ***!
  \************************************/
/*! no static exports found */
/***/ (function(module, exports) {

eval("class Forms {\n  constructor() {\n    this.list = $('#form-list');\n    this.initDelete();\n  }\n\n  initDelete() {\n    var _this = this;\n\n    this.list.find('a.delete').click(function () {\n      let link = $(this);\n      let name = $(this).data('name');\n\n      if (confirm(Craft.t('inspireforms', 'Are you sure you want to delete the form {name}', {\n        name: name\n      }))) {\n        let data = {};\n        data[Craft.csrfTokenName] = Craft.csrfTokenValue;\n        let url = $(this).data('url');\n        $.post(url, data, null, 'json').done(function (data) {\n          if (data.success) {\n            Craft.cp.displayNotice(Craft.t('inspireforms', 'Form successfully deleted.'));\n            link.parent().parent().remove();\n\n            var count = _this.list.find('tbody tr').length;\n\n            if (length == 0) {\n              _this.list.remove();\n\n              $('#no-forms').show();\n            }\n          }\n        }).fail(function (data) {\n          Craft.cp.displayError(data.responseJSON.error);\n        });\n      }\n    });\n  }\n\n}\n\n$(document).ready(function () {\n  new Forms();\n});\n\n//# sourceURL=webpack:///./src/Assets/src/js/forms.js?");

/***/ }),

/***/ 1:
/*!***************************************!*\
  !*** multi ./src/Assets/src/forms.js ***!
  \***************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("module.exports = __webpack_require__(/*! /Users/boris/Web/vhosts-php7/craft/vendor/inspire/forms/src/Assets/src/forms.js */\"./src/Assets/src/forms.js\");\n\n\n//# sourceURL=webpack:///multi_./src/Assets/src/forms.js?");

/***/ })

/******/ });