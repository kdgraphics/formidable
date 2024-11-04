document.addEventListener("DOMContentLoaded", function() {
    const phoneInputs = document.querySelectorAll('input[type="tel"]');

    phoneInputs.forEach((input) => {
        const iti = window.intlTelInput(input, {
            loadUtilsOnInit:
                input.dataset.loadUtilsOnInit ||
                "formidable/assets/dist/utils.js",
            separateDialCode: true,
            initialCountry: "auto",
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
