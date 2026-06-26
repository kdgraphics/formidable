import Vue from "vue";
import { cloneDeep } from "lodash";
import draggable from "vuedraggable";
import Multiselect from "vue-multiselect";
import "./craft-cp/css/cp.css";
import store from "../src/assets/src/store/FormsStore";
import FormsConfig from "../src/assets/src/components/FormsConfig.vue";
import FormsFields from "../src/assets/src/components/FormsFields.vue";
import FormsRules from "../src/assets/src/components/FormsRules.vue";
import FormsRule from "../src/assets/src/components/FormsRule.vue";
import FormsField from "../src/assets/src/components/FormsField.vue";
import FormsMenu from "../src/assets/src/components/FormsMenu.vue";
import FormsTabs from "../src/assets/src/components/FormsTabs.vue";
import FormsToolbar from "../src/assets/src/components/FormsToolbar.vue";
import RuleCondition from "../src/assets/src/components/RuleCondition.vue";
import RuleConditionGroup from "../src/assets/src/components/RuleConditionGroup.vue";
import TextField from "../src/assets/src/fields/TextField.vue";
import DateField from "../src/assets/src/fields/DateField.vue";
import EmailField from "../src/assets/src/fields/EmailField.vue";
import TextareaField from "../src/assets/src/fields/TextareaField.vue";
import NumberField from "../src/assets/src/fields/NumberField.vue";
import RadioField from "../src/assets/src/fields/RadioField.vue";
import RadioEntryField from "../src/assets/src/fields/RadioEntryField.vue";
import RadioCategoryField from "../src/assets/src/fields/RadioCategoryField.vue";
import FileField from "../src/assets/src/fields/FileField.vue";
import HiddenField from "../src/assets/src/fields/Hidden.vue";
import SelectField from "../src/assets/src/fields/SelectField.vue";
import SelectCategoryField from "../src/assets/src/fields/SelectCategoryField.vue";
import SelectEntryField from "../src/assets/src/fields/SelectEntryField.vue";
import CheckboxField from "../src/assets/src/fields/CheckboxField.vue";
import CheckboxesField from "../src/assets/src/fields/CheckboxesField.vue";
import CheckboxEntryField from "../src/assets/src/fields/CheckboxEntryField.vue";
import CheckboxCategoryField from "../src/assets/src/fields/CheckboxCategoryField.vue";
import GroupField from "../src/assets/src/fields/Group.vue";
import PhoneNumberField from "../src/assets/src/fields/PhoneNumberField.vue";
import Choices from "../src/assets/src/fields/Choices.vue";
import "./cp-preview.css";

// Craft owns these history routes in the real CP. A static preview has no
// matching server routes, so leave the current preview URL alone.
store._actions.setHistory = [() => Promise.resolve()];

const blankForm = () => ({
  id: null, name: "", handle: "", captcha: null, spamCheck: "",
  obfuscateFieldNames: 0, frontEndValidation: 1, saveSubmissions: 1,
  saveSpam: 0, honeypot: 0, honeypotField: "", redirect: "",
  notifySpam_key: null, notifySpamEmails: "", notifyAdmin_key: null,
  notifyEmails: "", notify_key: null, notifyField: "", method: "POST",
  autocomplete: 1, class: "", htmlId: "", submitLabel: "Submit",
  submitClass: "", color: "#0d78a8",
});

const baseField = {
  id: null, form_id: null, group_id: null, active: true, required: false,
  labelHtml: false, label: "", tooltip: "", tooltipLabel: "", default: "",
  placeholder: "", instructions: "", fieldClass: "", containerClass: "",
  labelClass: "", autocomplete: false, validRules: ["present", "equal"],
};

const makeField = (type, friendlyName, options = {}) => ({
  ...baseField, type, friendlyName, name: "", handle: "",
  label: "", tooltip: "", tooltipLabel: "", placeholder: "", default: "",
  choices: [], groups: [], sections: [], fields: [], multiple: false,
  includeSelect: false, selectLabel: "", choiceContainerClass: "", choiceLabelClass: "",
  ...options,
});

// This mirrors Formidable::$availableFields. The values are the same shape
// Craft serialises for the CP, so the real editor components can be exercised.
const registeredFields = {
  text: makeField("text", "Text"),
  date: makeField("date", "Date", { format: "d/m/Y", disableMobile: false, enableTime: false, enableSeconds: false, maxDate: "", minDate: "", minuteIncrement: 5, noCalendar: false }),
  email: makeField("email", "Email"),
  textarea: makeField("textarea", "Text area", { rows: 4 }),
  number: makeField("number", "Number", { min: "", max: "", step: "" }),
  radio: makeField("radio", "Radio", { choices: [] }),
  radio_entry: makeField("radio_entry", "Radio Entry"),
  radio_category: makeField("radio_category", "Radio Category"),
  file: makeField("file", "File", { volume: "", extensions: "", maxFiles: 1, attachToUserEmail: false, attachToAdminEmail: false, size: 2 }),
  hidden: makeField("hidden", "Hidden"),
  select: makeField("select", "Select", { choices: [] }),
  select_category: makeField("select_category", "Select Category"),
  select_entry: makeField("select_entry", "Select Entry"),
  checkbox: makeField("checkbox", "Checkbox"),
  checkboxes: makeField("checkboxes", "Checkboxes", { choices: [] }),
  checkbox_entry: makeField("checkbox_entry", "Checkbox Entry"),
  checkbox_category: makeField("checkbox_category", "Checkbox Category"),
  group: makeField("group", "Group", { fields: [] }),
  phone_number: makeField("phone_number", "Phone Number"),
};

const makeRule = (type, name, conditionName, defaultMessage, options = {}) => ({
  id: null, type, name, title: "", conditionName, defaultMessage,
  message: defaultMessage, messages: {}, value: "", form_id: null,
  fields: [], conditions: [], conditionGroups: [], requiresValue: false,
  requiresField: false, ...options,
});

// This mirrors Formidable::$availableRules and the labels shown by Craft.
const registeredRules = {
  biggerThan: makeRule("biggerThan", "Bigger than", "is bigger than", "{field.name} must be bigger than {rule.value}.", { requiresValue: true }),
  contain: makeRule("contain", "Contain", "contains", "{field.name} must contain {rule.value}.", { requiresValue: true }),
  equalTo: makeRule("equalTo", "Equal to", "is equal to", "{field.name} must equal to {rule.value}.", { requiresValue: true }),
  off: makeRule("off", "Off", "is off", "{field.name} must be off."),
  on: makeRule("on", "On", "is on", "{field.name} must be on."),
  required: makeRule("required", "Required", "is present", "{field.name} is required."),
  sameThan: makeRule("sameThan", "Same than", "is same than", "{field.name} must be the same than {rule.value}.", { requiresField: true }),
  size: makeRule("size", "Size", "is of size", "Size of {field.name} must be {rule.value}.", { requiresValue: true }),
  smallerThan: makeRule("smallerThan", "Smaller than", "is smaller than", "{field.name} must be smaller than {rule.value}.", { requiresValue: true }),
};

const state = Vue.observable({ notice: "", error: "", nextId: 1 });
const savedForms = {};

window.Craft = {
  csrfTokenName: "csrf",
  csrfTokenValue: "cp-preview",
  getCpUrl: (path) => path,
  getUrl: (path) => path,
  t: (_category, message) => message,
  cp: {
    displayNotice: (message) => { state.notice = message; state.error = ""; },
    displayError: (message) => { state.error = message; state.notice = ""; },
  },
  ColorInput: class {},
  initUiElements(root) {
    root.querySelectorAll(".lightswitch").forEach((switchElement) => {
      if (switchElement.dataset.previewReady) return;
      switchElement.dataset.previewReady = "true";
      switchElement.addEventListener("click", () => {
        const input = switchElement.querySelector("input");
        const value = input.value == 1 ? 0 : 1;
        input.value = value;
        switchElement.classList.toggle("on", Boolean(value));
        window.jQuery(switchElement).trigger("change");
      });
    });
    root.querySelectorAll(".menubtn").forEach((button) => {
      if (button.dataset.previewReady) return;
      button.dataset.previewReady = "true";
      button.setAttribute("aria-haspopup", "true");
      button.addEventListener("click", () => {
        const menu = button.nextElementSibling;
        if (!menu || !menu.classList.contains("menu")) return;
        const isOpen = menu.classList.toggle("menu--open");
        button.setAttribute("aria-expanded", String(isOpen));
      });
      const menu = button.nextElementSibling;
      menu?.addEventListener("click", (event) => {
        if (!event.target.closest("a")) return;
        menu.classList.remove("menu--open");
        button.setAttribute("aria-expanded", "false");
      });
    });
  },
};

window.CKEditor5 = { craftcms: { create: async () => null } };
Vue.prototype.$confirm = ({ callback }) => callback(true);
Vue.mixin({ methods: { t: (message) => message, changePageTitle: () => {} } });

window.axios = {
  post(url, data) {
    const payload = cloneDeep(data);
    return new Promise((resolve) => {
      window.setTimeout(() => {
        if (url.endsWith("/new")) {
          resolve({ data: { success: true, form: blankForm(), fields: [], rules: [], loaded: Date.now() } });
          return;
        }
        if (url.endsWith("/save")) {
          const id = payload.form.id || state.nextId++;
          const result = {
            success: true, message: "Form saved", form: { ...payload.form, id },
            fields: payload.fields || [], rules: payload.rules || [], loaded: Date.now(),
          };
          savedForms[id] = cloneDeep(result);
          resolve({ data: result });
          return;
        }
        if (url.includes("/get/")) {
          resolve({ data: cloneDeep(savedForms[url.split("/").pop()]) });
          return;
        }
        if (url.includes("/delete/")) {
          delete savedForms[url.split("/").pop()];
          resolve({ data: { success: true, message: "Form deleted" } });
        }
      }, 120);
    });
  },
};

[
  ["draggable", draggable], ["multiselect", Multiselect],
  ["forms-field", FormsField], ["forms-rule", FormsRule],
  ["condition", RuleCondition], ["condition-group", RuleConditionGroup],
  ["field-text", TextField], ["field-email", EmailField],
  ["field-date", DateField], ["field-textarea", TextareaField], ["field-number", NumberField],
  ["field-radio", RadioField], ["field-radio_entry", RadioEntryField], ["field-radio_category", RadioCategoryField],
  ["field-file", FileField], ["field-hidden", HiddenField], ["field-select", SelectField],
  ["field-select_category", SelectCategoryField], ["field-select_entry", SelectEntryField],
  ["field-checkbox", CheckboxField], ["field-checkboxes", CheckboxesField],
  ["field-checkbox_entry", CheckboxEntryField], ["field-checkbox_category", CheckboxCategoryField],
  ["field-group", GroupField], ["field-phone_number", PhoneNumberField],
  ["field-choices", Choices],
].forEach(([name, component]) => Vue.component(name, component));

new Vue({
  el: "#app",
  store,
  components: { FormsConfig, FormsFields, FormsRules, FormsMenu, FormsTabs, FormsToolbar },
  data: () => ({ registeredFields, registeredRules, state }),
  computed: {
    form() { return this.$store.state.form; },
    allForms() { return this.$store.state.allForms; },
  },
  created() {
    document.body.classList.add("formidable", "ltr", "cp-preview-body");
    this.$store.commit("setAllForms", {});
    this.$store.commit("setActiveTab", "config");
  },
  mounted() {
    this.$nextTick(() => window.Craft.initUiElements(document));
  },
  methods: {
    clearNotice() { this.state.notice = ""; this.state.error = ""; },
  },
  template: `
    <main id="main" class="cp-preview">
      <header class="cp-preview__page-header">
        <h1>{{ form.name || "Add new form" }}</h1>
        <forms-toolbar></forms-toolbar>
      </header>
      <div id="main-content">
        <aside id="sidebar-container">
          <forms-menu :all-forms="allForms"></forms-menu>
          <p v-if="Object.keys(allForms).length === 0" class="empty-forms">No saved forms yet.</p>
        </aside>
        <div id="content-container">
          <div class="content-grid">
            <div class="content-grid__main">
              <forms-tabs tab="config"></forms-tabs>
              <div class="cp-preview__content">
            <forms-config :form-edited="null" :all-emails="[]" :all-captchas="{}" :all-volumes="[]"></forms-config>
            <forms-fields :registered-fields="registeredFields"></forms-fields>
            <forms-rules :registered-rules="registeredRules"></forms-rules>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div v-if="state.notice || state.error" :class="['cp-notice', { error: state.error }]" @click="clearNotice">
        {{ state.error || state.notice }}
      </div>
    </main>
  `,
});
