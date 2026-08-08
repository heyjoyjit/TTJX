$(document).ready(function () {
    // Global loader show/hide
    window.showLoader = function () {
        $('#loaderOverlay').fadeIn(200);
    };
    window.hideLoader = function () {
        $('#loaderOverlay').fadeOut(200);
    };

    // Intercept all AJAX requests to show loader
    $(document).ajaxStart(function () {
        showLoader();
    }).ajaxStop(function () {
        hideLoader();
    }).ajaxError(function () {
        hideLoader();
    });

    // CSRF token meta (we'll add in layout)
    // We'll pass token via a meta tag
});

function getCsrfToken() {
    return $('meta[name="csrf-token"]').attr('content');
}