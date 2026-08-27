// const pjax={
//     loadPage:function(url){window.location.href=url;},
// }
// Extend the String prototype with a replaceAll function
String.prototype.replaceAll = function (search, replacement) {
    return this.replace(new RegExp(search, "g"), replacement);
};





// Google Translate Section End 


/**
 * @namespace app
 * @description Core application utility functions for handling AJAX, UI interactions, and data manipulation
 */
const app = {
    /**
     * Replaces template placeholders with actual data values
     * @param {string} htmlString - Template string containing placeholders like __key__
     * @param {Object} data - Key-value pairs for replacement
     * @returns {string} Processed HTML string with replacements
     */
    dataToHtml: function(htmlString, data) {
        // Replace all known placeholders with values
        Object.entries(data).forEach(([key, value]) => {
            htmlString = htmlString.replaceAll(`__${key}__`, value);
        });
        // Clean up any remaining placeholders
        return htmlString.replaceAll(/\__(.+?)\__/g, "");
    },

    /**
     * Renders an HTML template with multiple data entries
     * @param {string} template - HTML template string
     * @param {Array} data - Array of data objects to render
     * @returns {string} Combined HTML string
     */
    renderHtmlData: function(template, data) {
        if (!template) return "";
        return data.reduce((html, item) => html + this.dataToHtml(template, item), "");
    },

    /**
     * Shows a modal view loaded from a URL
     * @param {string} url - URL to fetch modal content
     */
    showModalView: function(url) {
        this.showLoading();
        $.ajax({
            url,
            method: "GET",
            success: (response) => {
                this.hideLoading();
                this.setModalContent(response);
                this.showModal();
                // Run any necessary initializationf
                runDocumentReady();
            },
            error: this.ajaxError
        });
    },

    /**
     * Modal management functions
     */
    setModalContent: function(html) {
        this.commonModel.find("#common-modal-content").html(html);
    },

    showModal: function() {
        if (!this.commonModel.is(":visible")) {
            this.commonModel.modal("show");
        }
    },

    hideModal: function() {
        if (this.commonModel.is(":visible")) {
            this.commonModel.modal("hide");
        }
    },
    
     /**
     * Shows a modal view loaded from a URL
     * @param {string} url - URL to fetch modal content
     */
    showCustomModalView: function(url) {
        this.showLoading();
        $.ajax({
            url,
            method: "GET",
            success: (response) => {
                this.hideLoading();
                this.setCustomModalContent(response);
                this.showCustomModal();
                // Run any necessary initializationf
                runDocumentReady();
            },
            error: this.ajaxError
        });
    },

    /**
     * Modal management functions
     */
    setCustomModalContent: function(html) {
        this.custCommonModel.find("#custom-common-modal-content").html(html);
    },

    showCustomModal: function() {
        if (!this.custCommonModel.is(":visible")) {
            this.custCommonModel.modal("show");
        }
    },

    hideCustomModal: function() {
        if (this.custCommonModel.is(":visible")) {
            this.custCommonModel.modal("hide");
        }
    },

    /**
     * Loading indicator management
     */
    showLoading: function() {
        Swal.showLoading(); 
    },

    hideLoading: function() {
        Swal.close();
    },

    /**
     * Performs an AJAX action without confirmation
     * @param {HTMLElement} obj - DOM element with data attributes
     * @param {Function} cb - Callback function
     */
    ajaxAction: function(obj, cb) {
        const $obj = $(obj);
        const postData = {};
        
        if ($obj.data("id")) {
            postData.id = $obj.data("id");
        }
        
        this.ajaxPost($obj.data("action"), postData, cb);
    },

    /**
     * Performs an AJAX action with confirmation
     * @param {HTMLElement} obj - DOM element with data attributes
     * @param {Function} cb - Callback function
     */
    confirmAction: function(obj, cb) {
        const $obj = $(obj);
        const postData = $obj.data("id") ? { id: $obj.data("id") } : {};
        this.ajaxConfirm($obj.data("action"), postData, cb);
    },

    /**
     * Shows a confirmation dialog before performing AJAX POST
     * @param {string} url - Target URL
     * @param {Object} postData - Data to send
     * @param {Function} cb - Callback function
     */
    ajaxConfirm: function(url, postData, cb) {
        Swal.fire({
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes",
            cancelButtonText: "No",
            customClass: {
                confirmButton: "btn btn-primary",
                cancelButton: "btn btn-secondary"
            }
        }).then((result) => {
            if (result.value) {
                this.ajaxPost(url, postData, cb);
            }
        });
    },
    
     /**
     * Performs an AJAX action with confirmation
     * @param {HTMLElement} obj - DOM element with data attributes
     * @param {Function} cb - Callback function
     */
    confirmStatusAction: function(obj, cb) {
        const $obj = $(obj);
        const postData = $obj.data("id") ? { id: $obj.data("id") } : {};
        this.ajaxStatusConfirm($obj.data("action"), postData, cb);
    },

    /**
     * Shows a confirmation dialog before performing AJAX POST
     * @param {string} url - Target URL
     * @param {Object} postData - Data to send
     * @param {Function} cb - Callback function
     */
    ajaxStatusConfirm: function(url, postData, cb) {
        Swal.fire({
            title: "Are you sure?",
            text: "You want to change the status!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes",
            cancelButtonText: "No",
            customClass: {
                confirmButton: "btn btn-primary",
                cancelButton: "btn btn-secondary"
            }
        }).then((result) => {
            if (result.value) {
                this.ajaxPost(url, postData, cb);
            }
        });
    },
    
    
    /**
     * Performs an AJAX action with confirmation
     * @param {HTMLElement} obj - DOM element with data attributes
     * @param {Function} cb - Callback function
     */
    confirmApproveAction: function(obj, cb) {
        const $obj = $(obj);
        const postData = $obj.data("id") ? { id: $obj.data("id") } : {};
        this.ajaxApproveConfirm($obj.data("action"), postData, cb);
    },
    
       /**
     * Shows a confirmation dialog before performing AJAX POST
     * @param {string} url - Target URL
     * @param {Object} postData - Data to send
     * @param {Function} cb - Callback function
     */
   
    
    ajaxApproveConfirm: function(url, postData, cb) {
    Swal.fire({
        title: "Are you sure?",
        text: "You Want To Approve It!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes",
        cancelButtonText: "No",
        customClass: {
            confirmButton: "btn btn-primary",
            cancelButton: "btn btn-secondary"
        },
        preConfirm: () => {
            return new Promise((resolve, reject) => {
                // Show loading spinner when confirming the action
                Swal.fire({
                    title: "Processing...",
                    text: "Approving please wait...",
                    icon: "info",
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    onOpen: () => {
                        Swal.showLoading(); // Show the loading spinner
                    }
                });

                // Perform the AJAX request
                this.ajaxPost(url, postData, (response) => {
                    if (response.status === 1) {
                        // Hide the loading spinner and show the success message
                        Swal.fire({
                            // title: "Success!",
                            text: response.message,
                            icon: "success",
                            confirmButtonText: "OK"
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        // Hide the loading spinner and show the error message
                        Swal.fire({
                            title: "Warning!",
                            html: response.message,
                            icon: "warning",
                            confirmButtonText: "OK"
                        });
                    }
                    resolve(); // Resolve the promise to close the loading state
                });
            });
        }
    });
},

 /**
     * Performs an AJAX action with confirmation
     * @param {HTMLElement} obj - DOM element with data attributes
     * @param {Function} cb - Callback function
     */
    confirmRejectAction: function(obj, cb) {
        const $obj = $(obj);
        const postData = $obj.data("id") ? { id: $obj.data("id") } : {};
        this.ajaxRejectConfirm($obj.data("action"), postData, cb);
    },


     ajaxRejectConfirm: function(url, postData, cb) {
        Swal.fire({
            title: "Are you sure?",
            text: "You Want to Reject It!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes",
            cancelButtonText: "No",
            customClass: {
                confirmButton: "btn btn-primary",
                cancelButton: "btn btn-secondary"
            },
            preConfirm: () => {
                return new Promise((resolve, reject) => {
                    // Show loading spinner when confirming the action
                    Swal.fire({
                        title: "Processing...",
                        text: "Rejecting please wait...",
                        icon: "info",
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        onOpen: () => {
                            Swal.showLoading(); // Show the loading spinner
                        }
                    });
    
                    // Perform the AJAX request
                    this.ajaxPost(url, postData, (response) => {
                        if (response.status === 1) {
                            // Hide the loading spinner and show the success message
                            Swal.fire({
                                // title: "Success!",
                                text: response.message,
                                icon: "success",
                                confirmButtonText: "OK"
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            // Hide the loading spinner and show the error message
                            Swal.fire({
                                title: "Error!",
                                text: response.message,
                                icon: "error",
                                confirmButtonText: "OK"
                            });
                        }
                        resolve(); // Resolve the promise to close the loading state
                    });
                });
            }
        });
    },
       /**
     * Performs an AJAX action with confirmation
     * @param {HTMLElement} obj - DOM element with data attributes
     * @param {Function} cb - Callback function
     */
       confirmRestoreAction: function(obj, cb) {
        const $obj = $(obj);
        const postData = $obj.data("id") ? { id: $obj.data("id") } : {};
        this.ajaxRestoreConfirm($obj.data("action"), postData, cb);
    },

      /**
     * Shows a confirmation dialog before performing AJAX POST
     * @param {string} url - Target URL
     * @param {Object} postData - Data to send
     * @param {Function} cb - Callback function
     */
      ajaxRestoreConfirm: function(url, postData, cb) {
        Swal.fire({
            title: "Are you sure?",
            text: "You Want To Restore It !",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes",
            cancelButtonText: "No",
            customClass: {
                confirmButton: "btn btn-primary",
                cancelButton: "btn btn-secondary"
            }
        }).then((result) => {
            if (result.value) {
                this.ajaxPost(url, postData, cb);
            }
        });
    },
    
    /**
     * AJAX utility functions
     */
    ajaxPost: function(url, postData, cb) {
        postData[CSRF_NAME] = CSRF_TOKEN;
        this.ajaxRequest(url, postData, cb);
    },

    ajaxGet: function(url, cb = this.ajaxSuccess) {
        this.showLoading();
        $.ajax({
            url,
            method: "GET",
            dataType: "json",
            success: this.ajaxSuccess,
            error: this.ajaxError
        });
    },

    /**
     * Handles form submission via AJAX
     * @param {HTMLFormElement} form - Form element to submit
     * @param {Function} cb - Callback function
     */
    ajaxForm: function(form, cb) {
        const $form = $(form);
        console.log($form);
        this.ajaxRequest($form.attr("action"), $form.serialize(), cb);
    },

    /**
     * Shows a modal view loaded from a URL
     * @param {string} url - URL to fetch modal content
     */
    showToastAlert: function(message,type) {
        const $bgColor = (type == 'success' ? 'green' : 'red');
        Toastify({
            text: message,
            duration: 3000,
            close: true,
            gravity: "top",
            position: "right",
            backgroundColor: $bgColor,
          }).showToast()
    },

    /**
     * Shows a modal view loaded from a URL
     * @param {string} url - URL to fetch modal content
     */
    showSweetAlertToast(message, type) {
      const allowedTypes = ['success', 'error', 'warning', 'info', 'question'];
    
      Toast.fire({
        icon: allowedTypes.includes(type) ? type : 'info', // default to 'info' if invalid
        title: message
      });
    },

    /**
     * Handles file upload form submission
     * @param {HTMLFormElement} form - Form element with files
     * @param {Function} cb - Callback function
     */
    ajaxFileForm: function(form, cb) {
        const $form = $(form);
        this.ajaxFileRequest($form.attr("action"), new FormData($form[0]), cb);
    },

    ajaxFilePost: function (url, postData, cb) {
        postData.append(CSRF_NAME, CSRF_TOKEN);
        app.ajaxFileRequest(url, postData, cb);
    },

    /**
     * Core AJAX request handler
     * @param {string} url - Target URL
     * @param {Object} postData - Data to send
     * @param {Function} cb - Callback function
     */
    ajaxRequest: function(url, postData, cb = this.ajaxSuccess) {
        this.showLoading();
        $.ajax({
            url,
            method: "POST",
            data: postData,
            dataType: "json",
            success: (response) => {
                this.hideLoading();
                cb(response);
            },
            error: this.ajaxError
        });
    },


    /**
         * Core AJAX request handler
         * @param {string} url - Target URL
         * @param {Object} postData - Data to send
         * @param {Function} cb - Callback function
     */
    ajaxFileRequest: function (url, postData, cb) {
        if (cb === undefined) {
            cb = app.ajaxSuccess;
        }
        app.showLoading();
        $.ajax({
            url: url,
            method: "post",
            data: postData,
            dataType: "json",
            success: function (response) {
                app.hideLoading();
                cb(response);
            },
            error: app.ajaxError,
            processData: false,
            contentType: false,
        });
    },

    /**
     * Handles the next action based on AJAX response
     * @param {Object} response - Server response object
     */
    runNextAction: function(next,response){
        if (next === "load") {
            pjax.loadPage(response.url);
        } else if (next === "refresh") {
            pjax.loadPage(window.location.href);
        } else if(next === "list_refresh"){
            pagination.loadData();
        } else if (next === "table_refresh") {
            datatableObj.ajax.reload();
        } else if (next === "redirect") {
            window.location.href = response.url;
        } else if (next === "reload") {
            window.location.reload();
        } else if (next === "hide_modal") {
            this.hideModal();
        } else  if (next === "show_modal_view") {
            this.showModalView(response.url);
        }  
    },

    nextAction: function(response) {
        if (response.next === undefined) {
            return false;
        }
        if(response.next.match(',')){
            response.next.split(',').forEach(function(next){
                app.runNextAction(next,response);
            })
        }else{
            app.runNextAction(response.next,response);
        }
    },

    /**
     * Default AJAX success handler
     * @param {Object} response - Server response
     */
    ajaxSuccess: function(response) {
        if (response.status) {
            if (response.message) {
                Swal.fire("", response.message, "success").then(() => {
                    app.nextAction(response);
                });
            } else {
                app.nextAction(response);
            }
        } else if (response.message) {
            Swal.fire("Warning", response.message, "warning");
        }
    },

    /**
     * Default AJAX error handler
     */
    ajaxError: function() {
        Swal.fire(
            "Oops!",
            "Something went wrong. Please try again later.",
            "error"
        );
    },

    /**
     * Resource loading utilities
     */
    addCSS: function(urls) {
        urls.forEach(url => {
            if (!$(`link[href="${url}"]`).length) {
                $("body").append(`<link href="${url}" rel="stylesheet">`);
            }
        });
    },

    addJS: function(urls) {
        urls.forEach(url => {
            if (!$(`script[src="${url}"]`).length) {
                $("body").append(`<script src="${url}"></script>`);
            }
        });
    },

    /**
     * Loads a script and executes callback when ready
     * @param {string} url - Script URL
     * @param {Function} callback - Callback function
     */
    loadScript: function(url, callback) {
        if ($(`script[src="${url}"]`).length) {
            callback();
            return;
        }

        const script = document.createElement("script");
        script.type = "text/javascript";
        script.src = url;

        if (script.readyState) {
            script.onreadystatechange = function() {
                if (script.readyState === "loaded" || script.readyState === "complete") {
                    script.onreadystatechange = null;
                    callback();
                }
            };
        } else {
            script.onload = callback;
        }

        document.head.appendChild(script);
    },

    /**
     * Cookie management utilities
     */
    setCookie: function(cname, cvalue, exdays) {
        const d = new Date();
        d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
        const expires = `expires=${d.toUTCString()}`;
        document.cookie = `${cname}=${cvalue}; ${expires};path=/`;
    },

    getCookie: function(cname) {
        const name = `${cname}=`;
        const decodedCookie = decodeURIComponent(document.cookie);
        const ca = decodedCookie.split(';');
        
        for (let c of ca) {
            while (c.charAt(0) === ' ') {
                c = c.substring(1);
            }
            if (c.indexOf(name) === 0) {
                return c.substring(name.length, c.length);
            }
        }
        return "";
    },
    validateFile: function (file) {
        var re = /(\.jpg|\.jpeg|\.bmp|\.gif|\.png|\.zip)$/i;
        if (!re.exec(file.name)) {
            return { status: 0, message: "File is not valid" };
        }
        return { status: 1, message: "" };
    },
    
    /**
     * Generates a random ID string
     * @param {number} length - Length of ID to generate
     * @returns {string} Random ID
     */
    makeId: function(length) {
        const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        return Array.from({ length }, () => 
            characters.charAt(Math.floor(Math.random() * characters.length))
        ).join('');
    },

    /**
     * Initializes the application
     */
    init: function() {
        this.commonModel = $("#common-modal");
        this.custCommonModel = $("#custCommonModal");

        // Set up user token if not exists
        if (!this.getCookie(`${APP_UID}_token`)) {
            this.setCookie(`${APP_UID}_token`, this.makeId(64), 365);
        }

        // Set timezone
        const tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
        if (this.getCookie(`${APP_UID}_tz`) !== tz) {
            this.setCookie(`${APP_UID}_tz`, tz, 30);
        }
    }
};

$(document).ready(function () {
    app.init();
});

/**
 * Executes all registered document-ready functions.
 */
    function runDocumentReady() {
        if (documentReadyFunctions) {
            $.each(documentReadyFunctions, function (index, cb) {
                cb();
            });
            documentReadyFunctions = [];
        }
        flatpickr('.flatpickr-range', {
        dateFormat: "F j, Y",
        mode: 'range'
        })
    }

   $('.sidebar_hide').click(function(){
        if ($(window).width() <= 1198) {
            $('.sidebar_click').hide();
            $('.sidebar-backdrop').removeClass();
        }
    });
    
    $('.sidebar_show').click(function(){
       if($(window).width() <= 1198) {
           $('.sidebar_click').show();
       }
    });
    
    // show password
  $(document).on('click', '.toggle-password', function () {
    const wrapper = $(this).closest('.position-relative'); // Get the closest container
    const input = wrapper.find('input'); // Find the input inside that container
    const icon = $(this).find('i'); // Get the <i> inside the clicked span

    const isPassword = input.attr('type') === 'password';
    input.attr('type', isPassword ? 'text' : 'password');

    icon.toggleClass('bi-eye bi-eye-slash');
});

    
    $('.google-translate').hide();
    $('#change_language_btn').show();

    // When the Change Language button is clicked, toggle visibility
    $('#change_language_btn').click(function() {
        $('.google-translate').toggle(); // Toggle the visibility of the Google Translate element
        $(this).toggle(); // Toggle the visibility of the Change Language button
    });

    // Wait for the Google Translate dropdown to be inserted into the DOM
    document.addEventListener("DOMContentLoaded", function () {
        var targetNode = document.getElementById('google_translate_element');
    
        if (targetNode) {
            let observer = new MutationObserver(function (mutations) {
                mutations.forEach(function (mutation) {
                    if (mutation.addedNodes.length) {
                        // Now that the select element exists, we can bind the change event
                        $('#google_translate_element select').on('change', function () {
                            // Hide the Google Translate element when a language is selected
                            $('.google-translate').hide();
                            $('#change_language_btn').show(); // Show the Change Language button again
                        });
                    }
                });
            });
    
            // Start observing the Google Translate container for changes
            observer.observe(targetNode, {
                childList: true,
                subtree: true
            });
        } else {
            
        }
    });
   
    
    
    
    $(window).resize(function() {
    if ($(window).width() > 1198) {
        // Ensure sidebar is always visible when the window width is greater than 1198px
        $('.sidebar_click').show();
        $('.sidebar-backdrop').addClass('active'); // or add your appropriate class for the backdrop
    }
});

// Code For Link a Google Translate
window.googleTranslateElementInit = function () {
    new google.translate.TranslateElement({
        pageLanguage: 'en',
        layout: google.translate.TranslateElement.InlineLayout.HORIZONTAL // or VERTICAL
    }, 'google_translate_element');

    $('.VIpgJd-ZVi9od-xl07Ob-lTBxed').attr('href', 'javascript:void(0)');
};


// // Remove Page Banner Of Google Translate
document.addEventListener('DOMContentLoaded', function () {
    function removeTranslateStuff() {
        // Hide iframe banner
        const iframe = document.querySelector('iframe.goog-te-banner-frame');
        if (iframe) {
            iframe.style.display = 'none';
        }

        // Reset body position (in case Google shifted it)
        document.body.style.top = '0px';

        // Hide Google Translate overlay
        const overlay = document.querySelector('.goog-te-overlay');
        if (overlay) {
            overlay.style.display = 'none';
        }

        // Hide any obfuscated div banner like VIpgJd-ZVi9od-ORHb
        const banner = document.querySelector('[class*="VIpgJd-ZVi9od-ORHb"]');
        if (banner) {
            banner.style.display = 'none';
        }
    }

    // Run immediately
    removeTranslateStuff();

    // Observe for dynamic injection by Google Translate
    const observer = new MutationObserver(removeTranslateStuff);
    observer.observe(document.body, { childList: true, subtree: true });
});

// dropdown list of language selector
window.addEventListener('load', function () {
    const savedLang = localStorage.getItem('selected_lang');
    if (savedLang) {
        setTimeout(() => {
            const selectEl = document.querySelector('.goog-te-combo');
            if (selectEl) {
                selectEl.value = savedLang;
                selectEl.dispatchEvent(new Event('change'));
            }
        }, 1000); // wait a bit to ensure the dropdown is rendered
    }

        //  // Stop the fake link from navigating
        //     const fakeLink = document.querySelector('.VIpgJd-ZVi9od-xl07Ob-lTBxed');
        //     if (fakeLink) {
        //         fakeLink.setAttribute('href', 'javascript:void(0)');
        //     }


    document.addEventListener('change', function (e) {
        if (e.target.classList.contains('goog-te-combo')) {
            localStorage.setItem('selected_lang', e.target.value);
        }
    });
    $('.VIpgJd-ZVi9od-xl07Ob-lTBxed').attr('href', 'javascript:void(0)');
    
});

window.addEventListener('load', function () {
    setTimeout(function () {
        const iframe = document.querySelector('.goog-te-menu-frame');
        if (iframe) {
            const iframeDocument = iframe.contentDocument || iframe.contentWindow.document;
            const style = document.createElement('style');
            style.innerHTML = `
                .goog-te-menu2 {
                    max-height: 300px !important;
                    overflow-y: auto !important;
                    box-sizing: border-box !important;
                }
                .goog-te-menu2 td {
                    max-width: 100% !important;
                }
            `;
            iframeDocument.head.appendChild(style);
        }
    }, 2000); // Adjust the timeout if needed
});




var contentEditor={
    init:function()
    {
        $('.content-editor').on('mouseenter',function(){
            element=$(this);
            var id=element.data('id');
            id=id?id:element.data('key');
            if(element.data('class')=='action'){
                if(!element.find('.content-editor-action').length){
                    element.append('<span class="content-editor-action white-pencil-bg" onclick="app.showModalView(\'admin/content/update?id=' + id + '\');" ><i class="bi bi-pencil"></i></span>');
                }
            }else{
                if(!element.find('.content-editor-button').length){
                    element.append('<div class="content-editor-button white-pencil-bg" onclick="contentEditor.showEditor(\''+id+'\');" ><i class="bi bi-pencil"></i></div>')
                }
            }
        })
    },
    showEditor:function(key){
        app.showModalView('admin/content/update?id='+key);
    }
}


/**
 * A class for managing dynamic content loading and interactions with a target HTML element.
 */
class View {
    /**
     * Target element where content will be dynamically loaded.
     * @type {string}
     */
    target = "";
    /**
     * Initializes the View class with a target element.
     * @param {string} target - Selector for the target element.
     */
    init(target) {
        this.target = $(target);
    }
    /**
     * Clears the content of the target element.
     */
    clear() {
        this.target.html('');
    }
    /**
     * Loads content into the target element via an AJAX GET request.
     * @param {string} url - The URL to fetch the content from.
     */
    load(url) {
        var target = this.target;
        target.html("Loading...");
        $.ajax({
            url: url,
            method: "get",
            success: function (response) {
                target.html(response);
                runDocumentReady();
            },
            error: function (e) {
                Swal.fire(
                    "Oops!",
                    "Something went wrong. Pelase Try after sometime.",
                    "error"
                );
            },
        });
    }
}

/**
 * Extends jQuery with a utility to serialize a form into a JSON object.
 */
(function ($) {
    $.fn.serializeObject = function () {
        var self = this,
            json = {},
            push_counters = {},
            patterns = {
                validate:
                    /^[a-zA-Z][a-zA-Z0-9_]*(?:\[(?:\d*|[a-zA-Z0-9_]+)\])*$/,
                key: /[a-zA-Z0-9_]+|(?=\[\])/g,
                push: /^$/,
                fixed: /^\d+$/,
                named: /^[a-zA-Z0-9_]+$/,
            };

        /**
    * Builds a nested object structure.
    * @param {object|array} base - Base object or array to modify.
    * @param {string|number} key - Key or index.
    * @param {any} value - Value to assign.
    * @returns {object|array} Updated object or array.
    */
        this.build = function (base, key, value) {
            base[key] = value;
            return base;
        };
        /**
         * Generates a counter for push operations.
         * @param {string} key - Key to count pushes for.
         * @returns {number} Push counter.
         */
        this.push_counter = function (key) {
            if (push_counters[key] === undefined) {
                push_counters[key] = 0;
            }
            return push_counters[key]++;
        };

        $.each($(this).serializeArray(), function () {
            var k,
                keys = this.name.match(patterns.key),
                merge = this.value,
                reverse_key = this.name;
            while ((k = keys.pop()) !== undefined) {
                // Adjust reverse_key
                reverse_key = reverse_key.replace(
                    new RegExp("\\[" + k + "\\]$"),
                    ""
                );
                if (k.match(patterns.push)) {
                    merge = self.build(
                        [],
                        self.push_counter(reverse_key),
                        merge
                    );
                } else if (k.match(patterns.fixed)) {
                    merge = self.build([], k, merge);
                } else if (k.match(patterns.named)) {
                    merge = self.build({}, k, merge);
                }
            }
            json = $.extend(true, json, merge);
        });
        return json;
    };
})(jQuery);

/**
 * A class for managing AJAX-based pagination functionality.
 */
class Pagination {
    /**
     * URL for AJAX requests.
     * @type {string}
     */
    ajaxUrl = "";
    /**
     * jQuery object representing the container for pagination content.
     * @type {jQuery}
     */
    ajaxContainer = false;
    /**
     * Determines whether to load data on initialization.
     * @type {boolean}
     */
    initLoadData = true;
    /**
     * Pagination type (e.g., normal or load-more).
     * @type {number}
     */
    type = 0;
    /**
     * Data sent with AJAX requests.
     * @type {object}
     */
    postData = {
        page: 1,
        _token: CSRF_TOKEN,
        sort: { field: "", direction: "asc" },
        search: "",
        filter: {},
        filter_extra: {},
    }
    /**
     * Initializes the pagination with URL and container ID.
     * @param {string} url - The URL for AJAX requests.
     * @param {string} [ajaxContainerId] - The container for displaying pagination.
     */
    init(url, ajaxContainerId) {
        if (ajaxContainerId === undefined) {
            ajaxContainerId = "#pagination-ajax-container";
        }
        this.ajaxUrl = url;
        this.ajaxContainer = $(ajaxContainerId);

        var urlParam = new URLSearchParams(new URL(window.location.href).search);
        this.postData.filter = urlParam.get('filter');
        this.postData.page = urlParam.get('page');
        if (this.postData.filter == "") {
            this.postData.filter = {};
        } else if (typeof this.postData.filter == "object") {
        } else {
            this.postData.filter = this.postData.filter.replaceAll(
                "&quot;",
                '"'
            );
            this.postData.filter = JSON.parse(this.postData.filter);
        }

        if (this.initLoadData) {
            this.loadData();
        } else {
            var _this = this;
            _this.ajaxContainer.find(".page-link").click(function () {
                _this.loadList($(this).data("page"));
            });
        }
    }

    /**
 * Initializes the pagination functionality by setting the URL for AJAX requests 
 * and the container where the paginated data will be loaded. 
 * Then, it triggers the data loading process.
 *
 * @param {string} url - The URL for AJAX requests to fetch paginated data.
 * @param {string} ajaxContainer - The selector or DOM element where the data will be injected.
 */
    initPagination(url, ajaxContainer) {
        // Set the AJAX URL and container for future use
        this.ajaxUrl = url;
        this.ajaxContainer = $(ajaxContainer);
        // Load the data from the provided URL
        this.loadData();
    }
    /**
     * Loads data via an AJAX request and updates the container.
     */
    loadData() {
        var _this = this;
        if (_this.type == 0) {
            _this.ajaxContainer.html("Loading...");
        } else {
            _this.ajaxContainer.find('.page-link').text('Loading...');
        }
        $.ajax({
            method: "post",
            url: this.ajaxUrl,
            data: this.postData,
            success: function (response) {
                if (_this.type == 0) {
                    _this.ajaxContainer.html(response);
                } else {
                    _this.ajaxContainer.find('.pagination-load-more').remove();
                    _this.ajaxContainer.append(response);
                }
                _this.ajaxContainer.find(".page-link").click(function () {
                    _this.loadList($(this).data("page"));
                });
            },
            error: function (e) {
                _this.ajaxContainer.html(
                    "Something Went wrong! Please Try after sometime"
                );
            },
        });
    }
    /**
 * Loads the list of data for the specified page.
 * 
 * @param {number} page - The page number to load.
 */
    loadList(page) {
        this.postData.page = page;
        this.loadData();// Fetch the data based on updated page
        app.setUrl(window.location.href, { page: page });// Update the URL to reflect the page number
    }
    /**
 * Sorts the data by the specified field and direction.
 * 
 * @param {string} field - The field by which to sort the data.
 * @param {string} direction - The direction to sort the data (e.g., 'asc' or 'desc').
 */
    sort(field, direction) {
        this.postData.sort.field = field;
        this.postData.sort.direction = direction;
        this.loadList(1);// Reset to the first page after sorting
        app.setUrl(window.location.href, { page: 1, sort: field + "-" + direction });// Update the URL with sort parameters
    }
    /**
 * Searches the data based on the provided search value.
 * 
 * @param {string} value - The search value to filter the data by.
 */
    search(value) {
        this.postData.search = value;
        this.loadList(1);// Reset to the first page after search
        app.setUrl(window.location.href, { page: 1, search: value });// Update the URL with search parameters
    }
    
    filterClear(){
        this.postData.filter={};
        this.setUrl(window.location.href, {
            page: 1,
            filter: "",
        });
    }
    /**
 * Filters the data based on a key-value pair. If the value is empty or the same as the current filter, it removes the filter.
 * 
 * @param {string} key - The key to filter the data by.
 * @param {string} value - The value to filter the data by.
 */
    filter(key, value) {
        if (value == '' || this.postData.filter[key] == value) {
            delete this.postData.filter[key];// Remove filter if value is empty or matches the existing one
        } else {
            this.postData.filter[key] = value;// Add or update the filter with the new value
        }
        this.loadList(1);// Reset to the first page after filtering
        app.setUrl(window.location.href, {
            page: 1,
            filter: JSON.stringify(this.postData.filter),// Update the URL with the current filters
        });

    }
    /**
 * Handles filtering for multiple values. It adds or removes values from the filter based on whether they are already included.
 * 
 * @param {string} key - The key to filter the data by.
 * @param {string} value - The value to filter by, potentially multiple values separated by commas.
 */
    filterMultiple(key, value) {
        var oldData = this.postData.filter[key];
        if (oldData) {
            oldData = oldData.split(',');
            // Remove the value if it's already in the filter, else add it
            if (oldData.indexOf(value) >= 0) {
                oldData = $.grep(oldData, function (v) {
                    return v != value;
                });
            } else {
                oldData.push(value);
            }
            value = oldData.join(',');
        }
        this.filter(key, value);// Apply the updated filter

    }
    /**
 * Submits the filter form and updates the list accordingly.
 * 
 * @param {HTMLFormElement} form - The form element containing the filter data.
 */
    filterFormSubmit(form) {
        this.postData.filter = $(form).serializeObject();// Serialize the form data into an object
        this.loadList(1);// Reset to the first page after form submission
        app.setUrl(window.location.href, {
            page: 1,
            filter: JSON.stringify(this.postData.filter),// Update the URL with the serialized filter
        });
    }
}
/**
 * Handles image cropping functionality using the Cropper.js library.
 */


class ImageCrop {
    cropTarget = false;// HTML element for image cropping
    cropperObj = false;// Cropper.js instance
    uploadPath = "";// Server upload URL
    cropperConfig = {
        aspectRatio: 1,
        cropBoxResizable: false,
        autoCropArea: 1,
    };
    /**
     * Initializes the ImageCrop instance.
     * @param {string} id - The ID of the HTML element for the cropper.
     * @param {string} uploadPath - The server upload URL.
     */
    init(id, uploadPath) {
        this.cropTarget = document.getElementById(id);
        this.uploadPath = uploadPath;
    }
    /**
     * Sets the configuration for the cropper.
     * @param {Object} config - The configuration object for Cropper.js.
     */
    setConfig(config) {
        this.cropperConfig = config;
    }
    /**
     * Initializes the Cropper.js instance.
     */
    setCropper() {
        this.cropperObj = new Cropper(this.cropTarget, this.cropperConfig);
        $(".image-crop-action").show();
    }
    
    /**
     * Sets the cropper with a selected file.
     * @param {File} file - The file to be cropped.
     */
    setCropFile(file) {
        var validationResult = app.validateFile(file);
        if (!validationResult.status) {
            Swal.fire({
                icon: "error",
                title: "File is not valid! Please select only the following formats:",
                text: "[png, gif, jpeg, webp, jpg]",
            });
            return false;
        }
        if (this.cropperObj) {
            this.cropperObj.destroy();
        }
        var _this = this;
        var reader = new FileReader();
        reader.onload = function (e) {
            _this.cropTarget.src = e.target.result;
            _this.setCropper();
        };
        reader.readAsDataURL(file);
    }
    /**
     * Uploads the cropped image to the server.
     * @returns {boolean} - Returns false if no image is selected.
     */
    uploadImage() {
        if (!this.cropperObj) {
            Swal.fire("Warning", "Please select image", "warning");
            return false;
        }
        app.showLoading();
        var _uploadPath = this.uploadPath;
        this.urltoFile(
            this.cropperObj.getCroppedCanvas().toDataURL(),
            "image.png",
            "image/png"
        ).then(function (file) {
            var formData = new FormData();
            formData.append(CSRF_NAME, CSRF_TOKEN);
            formData.append("image", file);
            $.ajax({
                url: _uploadPath,
                method: "post",
                data: formData,
                dataType: "json",
                processData: false,
                contentType: false,
                success: app.ajaxSuccess,
                error: app.ajaxError,
            });
        });
    }
 
    /**
     * Converts a data URL to a File object.
     * @param {string} url - The data URL to convert.
     * @param {string} filename - The name of the resulting file.
     * @param {string} mimeType - The MIME type of the file.
     * @returns {Promise<File>} - A promise resolving to a File object.
     */
    urltoFile(url, filename, mimeType) {
        return fetch(url)
            .then(function (res) {
                return res.arrayBuffer();
            })
            .then(function (buf) {
                return new File([buf], filename, { type: mimeType });
            });
    }
    /**
     * Retrieves the cropped image as a File object.
     * @returns {Promise<File>} - A promise resolving to the cropped file.
     */
    getFile() {
        return this.urltoFile(
            this.cropperObj.getCroppedCanvas().toDataURL(),
            "image.png",
            "image/png"
        );
    }
    /**
     * Rotates the image 90 degrees counterclockwise.
     */
    rotateLeft() {
        if (this.cropperObj) {
            this.cropperObj.rotate(90);
        }
    }
    /**
     * Rotates the image 90 degrees clockwise.
     */
    rotateRight() {
        if (this.cropperObj) {
            this.cropperObj.rotate(-90);
        }
    }
}

/**
 * Manages file uploads with drag-and-drop support.
 */
var fileDropBox = {
    files: [],
    fileDropBoxContainer: false,
    fileAccept: "image/png, image/jpeg, image/webp , image/jpg",
    fileInputHtml: "",
    /**
     * Initializes the file drop box.
     * @param {string} previewTarget - The selector for the preview container.
     * @param {Array<Object>} oldFiles - Previously uploaded files.
     */
    init: function (previewTarget, oldFiles) {
        (this.fileInputHtml =
            '<input style="visibility:hidden;" onchange="fileDropBox.selectFiles(this.files)" class="form-control file-input" type="file"  accept="' +
            this.fileAccept +
            '" multiple>'),
            (this.fileDropBoxContainer = $(previewTarget));
        var oldFilesHtml = "";
        $.each(oldFiles, function (index, fileData) {
            oldFilesHtml += fileDropBox.previewFile(
                fileData.type,
                fileData.url,
                fileData.name,
                fileData.name
            );
        });
        this.fileDropBoxContainer.html(
            '<div class="drop-file" ondragover="event.preventDefault();" ondrop="event.preventDefault();fileDropBox.selectFiles(event.dataTransfer.files);" onclick="$(this).next().find(\'.file-input\').click()"><h3 class="drop-text">Drop file here Or Click here to select File</h3></div><div class="file-input-container">' +
            fileDropBox.fileInputHtml +
            '</div><div class="file-preview row">' +
            oldFilesHtml +
            "</div>"
        );
    },
    /**
    * Handles file selection.
    * @param {FileList} files - The selected files.
    */
    selectFiles: function (files) {
        var previewHtml = "";
        $.each(files, function (index, file) {
            previewHtml += fileDropBox.previewFile(
                file.type,
                URL.createObjectURL(file),
                file.name,
                false
            );
            fileDropBox.files.push(file);
        });
        this.fileDropBoxContainer.find(".file-preview").append(previewHtml);
        this.fileDropBoxContainer
            .find(".file-input-container")
            .html(fileDropBox.fileInputHtml);
    },
    /**
     * Generates a preview for a file.
     * @param {string} type - The file type.
     * @param {string} url - The file URL.
     * @param {string} name - The file name.
     * @param {string|null} id - The file ID (for old files).
     * @returns {string} - The HTML string for the file preview.
     */
    previewFile: function (type, url, name, id) {
        var previewHtml = '<div class="file-preview-item mb-3 col-md-4">';
        if (type.match("video")) {
            previewHtml +=
                '<video class="video-preview" controls><source type="' +
                type +
                '" src="' +
                url +
                '"/></video>';
        } else if (type.match("image")) {
            previewHtml += '<img class="image-preview" src="' + url + '" />';
        } else {
            previewHtml += '<a href="' + url + '">' + name + "</a>";
        }
        previewHtml +=
            '<button onclick="fileDropBox.removeFile(this);" data-file-name="' +
            name +
            '" type="button" class="" style="display: flex;justify-content: center;align-items: end;"><i class="fa-solid fa-xmark"></i></button>';
        if (id) {
            previewHtml +=
                '<input type="hidden" name="file_old[]" value="' +
                id +
                '"></input>';
        }
        previewHtml += "</div>";
        return previewHtml;
    },
    /**
     * Removes a file from the preview and internal file list.
     * @param {HTMLElement} obj - The button element that triggered the removal.
     */
    removeFile: function (obj) {
        var target = $(obj);
        var filename = target.data("file-name");
        target.closest(".file-preview-item").remove();
        fileDropBox.files = $.grep(fileDropBox.files, function (file, index) {
            return file.name != filename;
        });
    },
    /**
     * Removes an old file from the preview.
     * @param {HTMLElement} obj - The button element that triggered the removal.
     */
    removeOldFile: function (obj) {
        $(obj).closest(".file-preview-item").remove();
    },
};


/**custom functions */

function updateDataTableUrl(url) {
    datatableObj.settings().ajax.url(url).load();
}

function initEditorFull(editorElement, fileUploadUrl) {
    var seditor = editorElement.summernote({
        height: 200,
        toolbar: [
            ["style", ["style"]],
            ["font", ["bold", "underline", "clear"]],
            ["fontname", ["fontname"]],
            ["color", ["color"]],
            ["para", ["ul", "ol", "paragraph"]],
            ["table", ["table"]],
            ["insert", ["link", "picture", "video"]],
            ["view", ["codeview"]],
        ],
        callbacks: {
            onImageUpload: function (files) {
                var formData = new FormData();
                formData.append("upload", files[0]);
                app.ajaxFilePost(fileUploadUrl, formData, function (response) {
                    if(response.status){
                        seditor.summernote("insertImage", response.url);
                    }else{
                        Swal.fire("Warning", response.message, "warning");
                    } 
                });
            },
        },
    });
}

function initEditor(editorElement) {
    editorElement.summernote({
        height: 200,
        toolbar: [
            ["style", ["style"]],
            ["font", ["bold", "underline", "clear"]],
            ["fontname", ["fontname"]],
            ["color", ["color"]],
            ["para", ["ul", "ol", "paragraph"]],
            ["table", ["table"]],
            ["view", ["codeview"]], //'fullscreen','help'
        ],
    });
}

/**
 * Previews an image by updating the `src` attribute of the target element.
 *
 * @param {HTMLInputElement} input - The input element containing the image file.
 * @param {string} target - The selector for the target image element.
 */
function previewImage(input, target) {
    $(target).attr("src", URL.createObjectURL(input.files[0]));
}




