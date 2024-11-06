function disableSubmitButtons() {
    const buttons = document.querySelectorAll(".formidable-submit");

    buttons.forEach((button) => {
        button.disabled = true;
    });
}

function enableSubmitButtons() {
    const buttons = document.querySelectorAll(".formidable-submit");

    buttons.forEach((button) => {
        button.disabled = false;
    });
}

disableSubmitButtons();

window.addEventListener("load", function() {
    enableSubmitButtons();
});
