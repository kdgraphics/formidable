function addLoadingSpinner(button) {
    const spinner = document.createElement("span");

    spinner.className = "loading-spinner";
    button.appendChild(spinner);
}

function disableSubmitButtons() {
    const buttons = document.querySelectorAll(".formidable-submit");

    buttons.forEach((button) => {
        button.disabled = true;
        addLoadingSpinner(button);
    });
}

function enableSubmitButtons() {
    const buttons = document.querySelectorAll(".formidable-submit");

    buttons.forEach((button) => {
        button.disabled = false;

        const spinner = button.querySelector(".loading-spinner");

        if (spinner) {
            spinner.remove();
        }
    });
}

disableSubmitButtons();

window.addEventListener("load", function() {
    enableSubmitButtons();
});
