document.addEventListener("DOMContentLoaded", function() {
    const phoneInputs = document.querySelectorAll('input[type="tel"]');

    // Local Storage: Country Code GET
    function getStoredCountry() {
        return localStorage.getItem("formidableCountryCode");
    }

    // Local Storage: Country Code STORE
    function setStoredCountry(countryCode) {
        localStorage.setItem("formidableCountryCode", countryCode);
    }

    phoneInputs.forEach((input) => {
        const iti = window.intlTelInput(input, {
            loadUtilsOnInit: input.dataset.utilsScript,
            initialCountry: "auto",
            separateDialCode: false,
            nationalMode: false,
            autoPlaceholder: "aggressive",
            customPlaceholder: (
                selectedCountryPlaceholder,
                selectedCountryData
            ) => "+" + selectedCountryData.dialCode,
            geoIpLookup: function(success, failure) {
                // Check local storage
                const countryCode = getStoredCountry();

                if (countryCode) {
                    success(countryCode);

                    return true;
                }

                // If no country stored, do API call
                fetch("https://ipwho.is")
                    .then((resp) => resp.json())
                    .then((data) => {
                        const countryCode = data.country_code.toLowerCase();

                        // Store country in local storage and set
                        setStoredCountry(countryCode);
                        success(data.country_code);
                    })
                    .catch(() => failure("gb"));
            },
        });

        // Add class
        input.classList.add("form-control");

        // Handle validation
        input.closest("form")?.addEventListener("submit", function(e) {
            if (!iti.isValidNumber()) {
                e.preventDefault();

                input.classList.add("is-invalid");
            } else {
                input.classList.remove("is-invalid");

                input.value = iti.getNumber();
            }
        });

        // Format on blur
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
});
