<style>
    @media (min-width: 1199px) {
        body {
            overflow-y: auto !important;
            overflow: visible !important;
        }
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof $ !== 'undefined') {
            $(document).on('click', '.sidebar_hide, .sidebar-backdrop, .layout-overlay, .layout-menu-toggle', function () {
                if ($(window).width() <= 1198) {
                    $('body').css('overflow-y', 'auto');
                }
            });

            $(window).on('resize', function () {
                if ($(window).width() > 1198) {
                    $('body').css('overflow-y', 'auto');
                }
            });
        }
    });
</script>
