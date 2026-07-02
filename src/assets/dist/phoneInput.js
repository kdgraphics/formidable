(function() {
  "use strict";
  function initFormidablePhoneInputs() {
    const phoneInputs = document.querySelectorAll("[data-formidable-phone-input]");
    phoneInputs.forEach((input) => {
      var _a;
      if (input.dataset.formidablePhoneReady === "1") {
        return;
      }
      input.dataset.formidablePhoneReady = "1";
      const initialCountry = (input.dataset.initialCountry || "gb").toLowerCase();
      const iti = window.intlTelInput(input, {
        initialCountry,
        separateDialCode: false,
        nationalMode: false,
        autoPlaceholder: "aggressive",
        customPlaceholder: (selectedCountryPlaceholder, selectedCountryData) => "+" + selectedCountryData.dialCode
      });
      input.classList.add("form-control");
      (_a = input.closest("form")) == null ? void 0 : _a.addEventListener("submit", function(e) {
        if (!iti.isValidNumber()) {
          e.preventDefault();
          input.classList.add("is-invalid");
        } else {
          input.classList.remove("is-invalid");
          input.value = iti.getNumber();
        }
      });
      input.addEventListener("blur", function() {
        if (iti.isValidNumber()) {
          input.value = iti.getNumber();
        }
      });
      input.addEventListener("open:countrydropdown", function() {
        document.querySelectorAll(".iti__search-input").forEach((e) => {
          e.classList.add("form-control");
          e.placeholder = "Enter country code";
        });
      });
    });
  }
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initFormidablePhoneInputs);
  } else {
    initFormidablePhoneInputs();
  }
})();
//# sourceMappingURL=phoneInput.js.map
