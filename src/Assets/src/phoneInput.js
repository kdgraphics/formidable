document.addEventListener("DOMContentLoaded", function() {
    const phoneInputs = document.querySelectorAll('input[type="tel"]');

    // Cookies: Country Code GET
    function getCountryFromCookie() {
        const match = document.cookie.match(/\bformidable_countryCode=([^;]*)/);

        return match ? match[1] : null;
    }

    // Cookies: Country Code STORE
    function setCountryCookie(countryCode) {
        document.cookie = `bformidable_countryCode=${countryCode};path=/;max-age=2592000`;
    }

    phoneInputs.forEach((input) => {
        const iti = window.intlTelInput(input, {
            loadUtilsOnInit:
                input.dataset.loadUtilsOnInit ||
                "formidable/assets/dist/utils.js",
            separateDialCode: true,
            initialCountry: "auto",
            geoIpLookup: function(success, failure) {
                // Check cookie
                const countryCode = getCountryFromCookie();

                if (countryCode) {
                    success(countryCode);

                    return true;
                }

                // If no cookie, do API call
                fetch("https://ipwho.is")
                    .then((resp) => resp.json())
                    .then((data) => {
                        const countryCode = data.country_code.toLowerCase();

                        // Store country in cookie and set
                        setCountryCookie(countryCode);
                        success(data.country_code);
                    })
                    .catch(() => failure("gb"));
            },
            preferredCountries: ["gb"],
        });

        // Add class
        input.classList.add("form-control");

        // Handle validation
        input.closest("form")?.addEventListener("submit", function(e) {
            if (!iti.isValidNumber()) {
                e.preventDefault();

                input.classList.add("is-invalid");

                console.log("error");
            } else {
                input.classList.remove("is-invalid");

                input.value = iti.getNumber();

                console.log("success");
            }
        });

        // Format on blur
        input.addEventListener("blur", function() {
            if (iti.isValidNumber()) {
                input.value = iti.getNumber();
            }
        });
    });
});
