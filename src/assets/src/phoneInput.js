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
                const cached = getStoredCountry();

                if (cached) {
                    success(cached);
                    return true;
                }

                // Helper function to process API result consistently
                function handleCountry(data) {
                    const countryCode = data.country_code.toLowerCase();
                    setStoredCountry(countryCode);
                    success(data.country_code);
                }

                // 1️ Try ipwho.is (will likely fail due to CORS)
                fetch("https://ipwho.is")
                    .then(resp => resp.json())
                    .then(data => {
                        if (!data.country_code) {
                            throw new Error("Invalid ipwho.is response");
                        }
                        handleCountry(data);
                    })
                    .catch(() => {
                        // 2️ Fallback → ipapi.co/json/
                        fetch("https://ipapi.co/json/")
                            .then(resp => resp.json())
                            .then(data => {
                                if (!data.country_code) {
                                    throw new Error("Invalid ipapi response");
                                }
                                handleCountry(data);
                            })
                            .catch(() => {
                                // Final fallback → default "gb"
                                failure("gb");
                            });
                    });
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
