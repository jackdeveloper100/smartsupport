const APP_URL = window.location.origin;  

const pjax = {
    $mainContainer: null,
    cache: [],
    activeMenuList: null,

    loadPage(url, cache = false, scroll = true) {
        if (url !== window.location.href) {
            window.history.pushState({}, "", url);
        }

        const cachedPage = this.cache.find(item => item.url === url);
        if (cachedPage) {
            this.updateContent(cachedPage.html);
            if (!scroll) $(window).scrollTop(0);
            this.updateActiveMenu(url);
            return;
        }

        this.$mainContainer.css("min-height", this.$mainContainer.height()).html(`
            <div class="page-loader">
                <div class="spinner-border text-primary">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `);

        const ajaxUrl = `${url}${url.includes("?") ? "&" : "?"}partial=1&layout=${this.$mainContainer.data("layout")}`;

        $.ajax({
            url: ajaxUrl,
            method: "GET",
            success: (response) => {
                if (response === "unauthorized") {
                    window.location.reload();
                } else if (response === "reload" || response.includes("<body")) {
                    window.location.href = url;
                } else {
                    if (cache) this.cache.push({ url, html: response });
                    this.updateContent(response, scroll);
                    if (!scroll) $(window).scrollTop(0);
                    this.updateActiveMenu(url);
                }
            },
            error: (xhr) => {
                try {
                    const response = JSON.parse(xhr.responseText);
                    this.$mainContainer.html(response.message || "");
                } catch {
                    window.location.href = url;
                }
            }
        });
    },

    updateContent(html) {
        this.$mainContainer.html(html).css("min-height", 0);
        // Update page title based on loaded content
        $("title").text($("#main-content").data("title") || document.title);
        // Run any JS initialization you need after PJAX load
        runDocumentReady?.();
    },

    routeLinks() {
        $(document).on("click", "a.pjax", (e) => {
            const target = e.currentTarget;
            const href = target.href;

            if (!href || href.match(/#|javascript:void|undefined/)) return;
            if (e.ctrlKey || target.target === "_blank") return window.open(href, "_blank");

            e.preventDefault();

            const scroll = target.getAttribute("data-pjax-scroll") !== "false";
            const cache = target.hasAttribute("data-pjax-cache");

            this.loadPage(href, cache, scroll);
        });
    },

    updateActiveMenu(url) {
        let pjaxurl = url.replace(APP_URL, '').split('?')[0];
        if (pjaxurl.length > 1 && pjaxurl.endsWith('/')) {
            pjaxurl = pjaxurl.slice(0, -1);
        }
        if (pjaxurl === '') {
            pjaxurl = 'home';  // fallback
        }

        this.activeMenuList.removeClass("active open");

        this.activeMenuList.each((index, element) => {
            element = $(element);
            let pjaxLinks = [];

            // Use data-active_menu_links if defined
            if (element.data('active_menu_links')) {
                pjaxLinks = element.data('active_menu_links').split(',').map(s => s.trim());
            }

            // If no explicit links, try to gather hrefs from child links
            if (pjaxLinks.length === 0) {
                element.find('a.sidebar-link, a.submenu-link').each((i, link) => {
                    let href = $(link).attr('href') || '';
                    if (href.startsWith(APP_URL)) {
                        href = href.replace(APP_URL, '');
                    }
                    href = href.split('?')[0];
                    if (href.length > 1 && href.endsWith('/')) href = href.slice(0, -1);
                    if (href === '') href = 'home';
                    pjaxLinks.push(href);
                });
            }

            if (pjaxLinks.includes(pjaxurl)) {
                element.addClass("active");
                const extraClass = element.data('active_menu_class');
                if (extraClass) {
                    element.addClass(extraClass);
                }
            }
        });
    },

    init() {
        this.$mainContainer = $("#main-container");
        if (!this.$mainContainer.length) {
            return console.error("pjax: Main container not found");
        }

        this.activeMenuList = $(".sidebar-item, .submenu-item");

        this.routeLinks();
        window.addEventListener("popstate", () => this.loadPage(window.location.href));

        // Initial active menu highlight on page load
        this.updateActiveMenu(window.location.href);
    }
};

// Initialize PJAX on DOM ready
$(document).ready(() => pjax.init());
