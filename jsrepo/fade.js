document.addEventListener('DOMContentLoaded', function () {
    document.body.classList.add('page-loaded');
    var anchors = document.querySelectorAll('a[href]');
    anchors.forEach(function(anchor) {
        var href = anchor.getAttribute('href');
        if (!href || href.startsWith('#') || anchor.target) return;
        var url = new URL(anchor.href, window.location.href);
        if (url.origin === window.location.origin) {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                var navigate = function () { window.location.href = anchor.href; };
                document.body.classList.remove('page-loaded');
                document.body.addEventListener('transitionend', navigate, { once: true });
            });
        }
    });
});
