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
/******/ 	return __webpack_require__(__webpack_require__.s = 3);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./node_modules/css-loader/dist/cjs.js!./node_modules/sass-loader/dist/cjs.js!./src/Assets/src/css/submissions.scss":
/*!**************************************************************************************************************************!*\
  !*** ./node_modules/css-loader/dist/cjs.js!./node_modules/sass-loader/dist/cjs.js!./src/Assets/src/css/submissions.scss ***!
  \**************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../../../node_modules/css-loader/dist/runtime/api.js */ \"./node_modules/css-loader/dist/runtime/api.js\");\n/* harmony import */ var _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0__);\n// Imports\n\nvar ___CSS_LOADER_EXPORT___ = _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0___default()(false);\n// Module\n___CSS_LOADER_EXPORT___.push([module.i, \"#submissions-view .filters .submitted {\\n  display: flex; }\\n  #submissions-view .filters .submitted .field {\\n    margin: 0;\\n    width: 115px; }\\n\\n#submissions-view .submission.unread {\\n  font-weight: bold; }\\n\\n#submissions-view .submission td:not(.thin) {\\n  cursor: pointer; }\\n\\n#submissions-view .bottom {\\n  display: flex;\\n  justify-content: space-between; }\\n  #submissions-view .bottom .field {\\n    display: flex; }\\n    #submissions-view .bottom .field .heading {\\n      margin-right: 5px;\\n      margin-bottom: 0px;\\n      margin-top: 0px; }\\n\\n#submissions-view #pagination {\\n  margin-top: 10px; }\\n  #submissions-view #pagination ul {\\n    display: flex; }\\n  #submissions-view #pagination li {\\n    background: #0B69A3;\\n    width: 30px;\\n    height: 28px;\\n    text-align: center;\\n    border: 2px solid white;\\n    border-radius: 5px;\\n    margin-right: 5px;\\n    font-size: 15px;\\n    transition: all 0.3s; }\\n    #submissions-view #pagination li a, #submissions-view #pagination li span {\\n      color: white;\\n      text-decoration: none;\\n      line-height: 28px; }\\n    #submissions-view #pagination li.active a, #submissions-view #pagination li.disabled a {\\n      cursor: default; }\\n    #submissions-view #pagination li:not(.active):not(.disabled) {\\n      cursor: pointer; }\\n      #submissions-view #pagination li:not(.active):not(.disabled):hover {\\n        background: white;\\n        border: 2px solid #0B69A3; }\\n        #submissions-view #pagination li:not(.active):not(.disabled):hover a, #submissions-view #pagination li:not(.active):not(.disabled):hover span {\\n          color: #0B69A3; }\\n    #submissions-view #pagination li.active {\\n      opacity: 0.25; }\\n\\n.modal.submissionmodal .close {\\n  position: absolute;\\n  top: 5px;\\n  right: 5px;\\n  font-size: 20px;\\n  z-index: 2; }\\n\\n.modal.submissionmodal .header {\\n  padding: 10px;\\n  margin-bottom: 0; }\\n\\n.modal.submissionmodal .group {\\n  display: flex;\\n  margin-bottom: 5px; }\\n  .modal.submissionmodal .group:first-child {\\n    margin-top: 5px; }\\n  .modal.submissionmodal .group label {\\n    margin-right: 5px; }\\n\\n.modal.submissionmodal .header-values {\\n  margin-top: 0; }\\n\", \"\"]);\n// Exports\n/* harmony default export */ __webpack_exports__[\"default\"] = (___CSS_LOADER_EXPORT___);\n\n\n//# sourceURL=webpack:///./src/Assets/src/css/submissions.scss?./node_modules/css-loader/dist/cjs.js!./node_modules/sass-loader/dist/cjs.js");

/***/ }),

/***/ "./node_modules/css-loader/dist/runtime/api.js":
/*!*****************************************************!*\
  !*** ./node_modules/css-loader/dist/runtime/api.js ***!
  \*****************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
eval("\n\n/*\n  MIT License http://www.opensource.org/licenses/mit-license.php\n  Author Tobias Koppers @sokra\n*/\n// css base code, injected by the css-loader\n// eslint-disable-next-line func-names\nmodule.exports = function (useSourceMap) {\n  var list = []; // return the list of modules as css string\n\n  list.toString = function toString() {\n    return this.map(function (item) {\n      var content = cssWithMappingToString(item, useSourceMap);\n\n      if (item[2]) {\n        return \"@media \".concat(item[2], \" {\").concat(content, \"}\");\n      }\n\n      return content;\n    }).join('');\n  }; // import a list of modules into the list\n  // eslint-disable-next-line func-names\n\n\n  list.i = function (modules, mediaQuery, dedupe) {\n    if (typeof modules === 'string') {\n      // eslint-disable-next-line no-param-reassign\n      modules = [[null, modules, '']];\n    }\n\n    var alreadyImportedModules = {};\n\n    if (dedupe) {\n      for (var i = 0; i < this.length; i++) {\n        // eslint-disable-next-line prefer-destructuring\n        var id = this[i][0];\n\n        if (id != null) {\n          alreadyImportedModules[id] = true;\n        }\n      }\n    }\n\n    for (var _i = 0; _i < modules.length; _i++) {\n      var item = [].concat(modules[_i]);\n\n      if (dedupe && alreadyImportedModules[item[0]]) {\n        // eslint-disable-next-line no-continue\n        continue;\n      }\n\n      if (mediaQuery) {\n        if (!item[2]) {\n          item[2] = mediaQuery;\n        } else {\n          item[2] = \"\".concat(mediaQuery, \" and \").concat(item[2]);\n        }\n      }\n\n      list.push(item);\n    }\n  };\n\n  return list;\n};\n\nfunction cssWithMappingToString(item, useSourceMap) {\n  var content = item[1] || ''; // eslint-disable-next-line prefer-destructuring\n\n  var cssMapping = item[3];\n\n  if (!cssMapping) {\n    return content;\n  }\n\n  if (useSourceMap && typeof btoa === 'function') {\n    var sourceMapping = toComment(cssMapping);\n    var sourceURLs = cssMapping.sources.map(function (source) {\n      return \"/*# sourceURL=\".concat(cssMapping.sourceRoot || '').concat(source, \" */\");\n    });\n    return [content].concat(sourceURLs).concat([sourceMapping]).join('\\n');\n  }\n\n  return [content].join('\\n');\n} // Adapted from convert-source-map (MIT)\n\n\nfunction toComment(sourceMap) {\n  // eslint-disable-next-line no-undef\n  var base64 = btoa(unescape(encodeURIComponent(JSON.stringify(sourceMap))));\n  var data = \"sourceMappingURL=data:application/json;charset=utf-8;base64,\".concat(base64);\n  return \"/*# \".concat(data, \" */\");\n}\n\n//# sourceURL=webpack:///./node_modules/css-loader/dist/runtime/api.js?");

/***/ }),

/***/ "./node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js":
/*!****************************************************************************!*\
  !*** ./node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js ***!
  \****************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
eval("\n\nvar isOldIE = function isOldIE() {\n  var memo;\n  return function memorize() {\n    if (typeof memo === 'undefined') {\n      // Test for IE <= 9 as proposed by Browserhacks\n      // @see http://browserhacks.com/#hack-e71d8692f65334173fee715c222cb805\n      // Tests for existence of standard globals is to allow style-loader\n      // to operate correctly into non-standard environments\n      // @see https://github.com/webpack-contrib/style-loader/issues/177\n      memo = Boolean(window && document && document.all && !window.atob);\n    }\n\n    return memo;\n  };\n}();\n\nvar getTarget = function getTarget() {\n  var memo = {};\n  return function memorize(target) {\n    if (typeof memo[target] === 'undefined') {\n      var styleTarget = document.querySelector(target); // Special case to return head of iframe instead of iframe itself\n\n      if (window.HTMLIFrameElement && styleTarget instanceof window.HTMLIFrameElement) {\n        try {\n          // This will throw an exception if access to iframe is blocked\n          // due to cross-origin restrictions\n          styleTarget = styleTarget.contentDocument.head;\n        } catch (e) {\n          // istanbul ignore next\n          styleTarget = null;\n        }\n      }\n\n      memo[target] = styleTarget;\n    }\n\n    return memo[target];\n  };\n}();\n\nvar stylesInDom = [];\n\nfunction getIndexByIdentifier(identifier) {\n  var result = -1;\n\n  for (var i = 0; i < stylesInDom.length; i++) {\n    if (stylesInDom[i].identifier === identifier) {\n      result = i;\n      break;\n    }\n  }\n\n  return result;\n}\n\nfunction modulesToDom(list, options) {\n  var idCountMap = {};\n  var identifiers = [];\n\n  for (var i = 0; i < list.length; i++) {\n    var item = list[i];\n    var id = options.base ? item[0] + options.base : item[0];\n    var count = idCountMap[id] || 0;\n    var identifier = \"\".concat(id, \" \").concat(count);\n    idCountMap[id] = count + 1;\n    var index = getIndexByIdentifier(identifier);\n    var obj = {\n      css: item[1],\n      media: item[2],\n      sourceMap: item[3]\n    };\n\n    if (index !== -1) {\n      stylesInDom[index].references++;\n      stylesInDom[index].updater(obj);\n    } else {\n      stylesInDom.push({\n        identifier: identifier,\n        updater: addStyle(obj, options),\n        references: 1\n      });\n    }\n\n    identifiers.push(identifier);\n  }\n\n  return identifiers;\n}\n\nfunction insertStyleElement(options) {\n  var style = document.createElement('style');\n  var attributes = options.attributes || {};\n\n  if (typeof attributes.nonce === 'undefined') {\n    var nonce =  true ? __webpack_require__.nc : undefined;\n\n    if (nonce) {\n      attributes.nonce = nonce;\n    }\n  }\n\n  Object.keys(attributes).forEach(function (key) {\n    style.setAttribute(key, attributes[key]);\n  });\n\n  if (typeof options.insert === 'function') {\n    options.insert(style);\n  } else {\n    var target = getTarget(options.insert || 'head');\n\n    if (!target) {\n      throw new Error(\"Couldn't find a style target. This probably means that the value for the 'insert' parameter is invalid.\");\n    }\n\n    target.appendChild(style);\n  }\n\n  return style;\n}\n\nfunction removeStyleElement(style) {\n  // istanbul ignore if\n  if (style.parentNode === null) {\n    return false;\n  }\n\n  style.parentNode.removeChild(style);\n}\n/* istanbul ignore next  */\n\n\nvar replaceText = function replaceText() {\n  var textStore = [];\n  return function replace(index, replacement) {\n    textStore[index] = replacement;\n    return textStore.filter(Boolean).join('\\n');\n  };\n}();\n\nfunction applyToSingletonTag(style, index, remove, obj) {\n  var css = remove ? '' : obj.media ? \"@media \".concat(obj.media, \" {\").concat(obj.css, \"}\") : obj.css; // For old IE\n\n  /* istanbul ignore if  */\n\n  if (style.styleSheet) {\n    style.styleSheet.cssText = replaceText(index, css);\n  } else {\n    var cssNode = document.createTextNode(css);\n    var childNodes = style.childNodes;\n\n    if (childNodes[index]) {\n      style.removeChild(childNodes[index]);\n    }\n\n    if (childNodes.length) {\n      style.insertBefore(cssNode, childNodes[index]);\n    } else {\n      style.appendChild(cssNode);\n    }\n  }\n}\n\nfunction applyToTag(style, options, obj) {\n  var css = obj.css;\n  var media = obj.media;\n  var sourceMap = obj.sourceMap;\n\n  if (media) {\n    style.setAttribute('media', media);\n  } else {\n    style.removeAttribute('media');\n  }\n\n  if (sourceMap && btoa) {\n    css += \"\\n/*# sourceMappingURL=data:application/json;base64,\".concat(btoa(unescape(encodeURIComponent(JSON.stringify(sourceMap)))), \" */\");\n  } // For old IE\n\n  /* istanbul ignore if  */\n\n\n  if (style.styleSheet) {\n    style.styleSheet.cssText = css;\n  } else {\n    while (style.firstChild) {\n      style.removeChild(style.firstChild);\n    }\n\n    style.appendChild(document.createTextNode(css));\n  }\n}\n\nvar singleton = null;\nvar singletonCounter = 0;\n\nfunction addStyle(obj, options) {\n  var style;\n  var update;\n  var remove;\n\n  if (options.singleton) {\n    var styleIndex = singletonCounter++;\n    style = singleton || (singleton = insertStyleElement(options));\n    update = applyToSingletonTag.bind(null, style, styleIndex, false);\n    remove = applyToSingletonTag.bind(null, style, styleIndex, true);\n  } else {\n    style = insertStyleElement(options);\n    update = applyToTag.bind(null, style, options);\n\n    remove = function remove() {\n      removeStyleElement(style);\n    };\n  }\n\n  update(obj);\n  return function updateStyle(newObj) {\n    if (newObj) {\n      if (newObj.css === obj.css && newObj.media === obj.media && newObj.sourceMap === obj.sourceMap) {\n        return;\n      }\n\n      update(obj = newObj);\n    } else {\n      remove();\n    }\n  };\n}\n\nmodule.exports = function (list, options) {\n  options = options || {}; // Force single-tag solution on IE6-9, which has a hard limit on the # of <style>\n  // tags it will allow on a page\n\n  if (!options.singleton && typeof options.singleton !== 'boolean') {\n    options.singleton = isOldIE();\n  }\n\n  list = list || [];\n  var lastIdentifiers = modulesToDom(list, options);\n  return function update(newList) {\n    newList = newList || [];\n\n    if (Object.prototype.toString.call(newList) !== '[object Array]') {\n      return;\n    }\n\n    for (var i = 0; i < lastIdentifiers.length; i++) {\n      var identifier = lastIdentifiers[i];\n      var index = getIndexByIdentifier(identifier);\n      stylesInDom[index].references--;\n    }\n\n    var newLastIdentifiers = modulesToDom(newList, options);\n\n    for (var _i = 0; _i < lastIdentifiers.length; _i++) {\n      var _identifier = lastIdentifiers[_i];\n\n      var _index = getIndexByIdentifier(_identifier);\n\n      if (stylesInDom[_index].references === 0) {\n        stylesInDom[_index].updater();\n\n        stylesInDom.splice(_index, 1);\n      }\n    }\n\n    lastIdentifiers = newLastIdentifiers;\n  };\n};\n\n//# sourceURL=webpack:///./node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js?");

/***/ }),

/***/ "./src/Assets/src/css/submissions.scss":
/*!*********************************************!*\
  !*** ./src/Assets/src/css/submissions.scss ***!
  \*********************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("var api = __webpack_require__(/*! ../../../../node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js */ \"./node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js\");\n            var content = __webpack_require__(/*! !../../../../node_modules/css-loader/dist/cjs.js!../../../../node_modules/sass-loader/dist/cjs.js!./submissions.scss */ \"./node_modules/css-loader/dist/cjs.js!./node_modules/sass-loader/dist/cjs.js!./src/Assets/src/css/submissions.scss\");\n\n            content = content.__esModule ? content.default : content;\n\n            if (typeof content === 'string') {\n              content = [[module.i, content, '']];\n            }\n\nvar options = {};\n\noptions.insert = \"head\";\noptions.singleton = false;\n\nvar update = api(content, options);\n\n\n\nmodule.exports = content.locals || {};\n\n//# sourceURL=webpack:///./src/Assets/src/css/submissions.scss?");

/***/ }),

/***/ "./src/Assets/src/js/submissions.js":
/*!******************************************!*\
  !*** ./src/Assets/src/js/submissions.js ***!
  \******************************************/
/*! no static exports found */
/***/ (function(module, exports) {

eval("class Submission {\n  constructor(elem, data) {\n    this.elem = elem;\n    this.data = data;\n    this.id = data.id;\n    this.deleteUrl = '/admin/inspireforms/submissions/delete/' + this.id;\n    this.elem.data('submission', this);\n\n    if (data.read) {\n      elem.find('.read').html(Craft.t('inspireforms', 'Read'));\n    } else {\n      elem.addClass('unread');\n      elem.find('.read').html(Craft.t('inspireforms', 'New'));\n    }\n\n    elem.find('.form').html(data.form.name);\n    elem.find('.created').html(data.dateCreated);\n    elem.find('.notified').html(data.notified ? Craft.t('inspireforms', 'Yes') : Craft.t('inspireforms', 'No'));\n    elem.find('.adminNotified').html(data.adminNotified ? Craft.t('inspireforms', 'Yes') : Craft.t('inspireforms', 'No'));\n    this.modal = InspireForms.Modal;\n    this.initOpen();\n    this.initDelete();\n  }\n\n  initOpen() {\n    let _this = this;\n\n    this.elem.find('td:not(.thin), .expand').click(function (e) {\n      e.preventDefault();\n\n      _this.open();\n    });\n  }\n\n  open() {\n    this.modal.open(this.data.id);\n    this.elem.removeClass('unread');\n  }\n\n  initDelete() {\n    let _this = this;\n\n    this.elem.find('.delete').click(function () {\n      if (confirm(Craft.t('inspireforms', 'Are you sure you want to delete this submission ?'))) {\n        let data = {};\n        data[Craft.csrfTokenName] = Craft.csrfTokenValue;\n        let elem = $(this);\n        $.ajax({\n          url: _this.deleteUrl,\n          dataType: 'json',\n          method: 'POST',\n          data: data\n        }).done(function (data) {\n          _this.remove();\n\n          Craft.cp.displayNotice(data.message);\n        }).fail(function (data) {\n          Craft.cp.displayError(data.responseJSON.error);\n        });\n      }\n    });\n  }\n\n  remove() {\n    this.elem.remove();\n  }\n\n}\n\nvar PreviewSubmissionModal = Garnish.Modal.extend({\n  submissionId: null,\n  desiredWidth: function () {\n    if (Garnish.isMobileBrowser(true)) {\n      return $(window).width() - 40;\n    } else {\n      return 400;\n    }\n  },\n  init: function (settings) {\n    settings = $.extend(this.defaultSettings, settings);\n\n    let _this = this;\n\n    this.$container = $('<div class=\"modal submissionmodal hidden\">\\\n\t\t\t<a class=\"icon delete close\" title=\"Close\" href=\"#\"></a>\\\n\t\t\t<div class=\"body\"></div>\\\n\t\t</div>').appendTo(Garnish.$bod);\n    this.$container.find('.close').click(function (e) {\n      _this.hide();\n    });\n    this.base(this.$container, $.extend({\n      resizable: true\n    }, settings));\n  },\n  open: function (id) {\n    if (id == this.submissionId) {\n      return this.show();\n    }\n\n    this.submissionId = id;\n    let data = {};\n    data[Craft.csrfTokenName] = Craft.csrfTokenValue;\n\n    let _this = this;\n\n    $.ajax({\n      url: '/admin/inspireforms/submissions/view/' + id,\n      dataType: 'json',\n      data: data,\n      method: 'POST'\n    }).done(function (data) {\n      _this.$container.find('.body').html(data.html);\n\n      _this.$container.removeClass('hidden');\n\n      _this.show();\n    }).fail(function () {\n      alert(error);\n    });\n  }\n});\n\nclass Submissions {\n  constructor() {\n    this.container = $('#submissions-view');\n    this.list = $('#submission-list');\n    this.actions = $('#submission-actions');\n    this.filters = this.container.find('form');\n    this.deleteSelectionUrl = '/admin/inspireforms/submissions/delete-many';\n    this.exportSelectionUrl = '/admin/inspireforms/submissions/export';\n    this.selectAllElem = this.container.find('#selectAll');\n    this.pagination = this.container.find('#pagination');\n    this.skeleton = this.list.find('.skeleton');\n    this.tab = $('body').find('#tab-submissions');\n    this.selected = {};\n    this.load();\n\n    let _this = this;\n\n    this.filters.find('input[type=text], select').change(function () {\n      _this.load();\n    });\n    this.filters.on('reset', function () {\n      setTimeout(function () {\n        _this.load();\n      }, 1);\n    });\n    this.initSelectAll();\n    this.bindSelects();\n    this.bindActions();\n  }\n\n  bindSelects() {\n    let _this = this;\n\n    $(document).on('click', '#submission-list .submission [type=checkbox]', function () {\n      if ($(this).is(':checked')) {\n        _this.select($(this).closest('tr'));\n      } else {\n        _this.unselect($(this).closest('tr'));\n      }\n    });\n  }\n\n  bindActions() {\n    let _this = this;\n\n    this.actions.data('menubtn').on('optionSelect', function (e) {\n      if ($(e.option).hasClass('delete')) {\n        _this.deleteSelection().done(function () {\n          _this.load();\n        });\n      } else {\n        _this.exportSelection();\n      }\n    });\n  }\n\n  deleteSelection() {\n    this.actions.hide();\n\n    let _this = this;\n\n    let selected = Object.keys(this.selected);\n    let data = {\n      ids: selected\n    };\n    data[Craft.csrfTokenName] = Craft.csrfTokenValue;\n    return $.ajax({\n      dataType: 'json',\n      url: _this.deleteSelectionUrl,\n      data: data,\n      method: 'POST'\n    }).done(function (data) {\n      selected.forEach(function (id) {\n        _this.selected[id].remove();\n      });\n      _this.selected = [];\n      Craft.cp.displayNotice(data.message);\n    });\n  }\n\n  initSelectAll() {\n    let _this = this;\n\n    this.selectAllElem.change(function () {\n      if ($(this).is(':checked')) {\n        _this.selectAll();\n      } else {\n        _this.unselectAll();\n      }\n    });\n  }\n\n  selectAll() {\n    let _this = this;\n\n    $.each(this.list.find('tr.submission'), function (i, item) {\n      _this.select($(item));\n    });\n  }\n\n  unselectAll() {\n    let _this = this;\n\n    $.each(this.list.find('tr.submission'), function (i, item) {\n      _this.unselect($(item));\n    });\n  }\n\n  select(elem) {\n    this.selected[elem.data('submission').id] = elem.data('submission');\n    elem.find('[type=checkbox]').attr('checked', 'checked');\n    this.actions.show();\n  }\n\n  unselect(elem) {\n    delete this.selected[elem.data('submission').id];\n    elem.find('[type=checkbox]').removeAttr('checked');\n\n    if ($.isEmptyObject(this.selected)) {\n      this.actions.hide();\n    }\n  }\n\n  load(page = 0) {\n    let action = this.filters.attr('action');\n    let data = this.filters.serializeArray();\n    data.push({\n      name: 'page',\n      value: page\n    });\n\n    let _this = this;\n\n    if (this.selectAllElem.is(':checked')) {\n      this.selectAllElem.trigger('click');\n    }\n\n    $.ajax({\n      method: 'POST',\n      data: data,\n      dataType: 'json',\n      url: action\n    }).done(function (data) {\n      _this.populateTable(data.submissions);\n\n      _this.pagination.html(data.pagination);\n\n      _this.bindPager();\n    });\n  }\n\n  bindPager() {\n    let _this = this;\n\n    this.pagination.find('a').click(function (e) {\n      e.preventDefault();\n    });\n    this.pagination.find('li:not(.active):not(.disabled)').click(function (e) {\n      _this.load($(this).find('a').data('page'));\n    });\n  }\n\n  populateTable(data) {\n    let _this = this;\n\n    this.list.find('.submission').remove();\n\n    if (data.length) {\n      this.list.find('#no-submissions').hide();\n    } else {\n      this.list.find('#no-submissions').show();\n    }\n\n    let sub;\n    let submission;\n    data.forEach(function (data) {\n      sub = _this.skeleton.clone().show().removeClass('skeleton').addClass('submission');\n      sub.find('[type=checkbox]').attr('id', sub.find('[type=checkbox]').attr('id') + data.id);\n      sub.find('label').attr('for', sub.find('label').attr('for') + data.id);\n      submission = new Submission(sub, data);\n      sub.appendTo(_this.list.find('tbody'));\n    });\n  }\n\n}\n\n$(document).ready(function () {\n  window.InspireForms = {\n    Submissions: new Submissions(),\n    Modal: new PreviewSubmissionModal({\n      autoShow: false\n    })\n  };\n});\n\n//# sourceURL=webpack:///./src/Assets/src/js/submissions.js?");

/***/ }),

/***/ "./src/Assets/src/submissions.js":
/*!***************************************!*\
  !*** ./src/Assets/src/submissions.js ***!
  \***************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _js_submissions_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./js/submissions.js */ \"./src/Assets/src/js/submissions.js\");\n/* harmony import */ var _js_submissions_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_js_submissions_js__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var _css_submissions_scss__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./css/submissions.scss */ \"./src/Assets/src/css/submissions.scss\");\n/* harmony import */ var _css_submissions_scss__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_css_submissions_scss__WEBPACK_IMPORTED_MODULE_1__);\n\n\n\n//# sourceURL=webpack:///./src/Assets/src/submissions.js?");

/***/ }),

/***/ 3:
/*!*********************************************!*\
  !*** multi ./src/Assets/src/submissions.js ***!
  \*********************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("module.exports = __webpack_require__(/*! /Users/boris/Web/vhosts-php7/craft/vendor/inspire/forms/src/Assets/src/submissions.js */\"./src/Assets/src/submissions.js\");\n\n\n//# sourceURL=webpack:///multi_./src/Assets/src/submissions.js?");

/***/ })

/******/ });