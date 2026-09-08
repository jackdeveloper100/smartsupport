/**
 * Global Unauthenticated (HTTP 401) AJAX & PJAX Interceptor
 */
$(document).ready(function () {
    $(document).ajaxError(function (event, jqXHR) {
        if (jqXHR.status === 401) {
            var res = jqXHR.responseJSON;
            var redirectUrl = (res && res.redirect) ? res.redirect : 'login';
            window.location.href = redirectUrl;
        }
    });

    $(document).on('pjax:error', function (event, xhr) {
        if (xhr.status === 401) {
            var res = xhr.responseJSON;
            var redirectUrl = (res && res.redirect) ? res.redirect : 'login';
            window.location.href = redirectUrl;
            return false;
        }
    });
});
