(function () {
    var btnSanduiche = document.getElementById('btnSanduiche');
    var sidebar = document.getElementById('sidebar');
    var conteudoPrincipal = document.getElementById('conteudoPrincipal');
    var sidebarOverlay = document.getElementById('sidebarOverlay');

    if (!btnSanduiche || !sidebar || !conteudoPrincipal || !sidebarOverlay) {
        return;
    }

    function fecharSidebarMobile() {
        sidebar.classList.remove('aberta');
        sidebarOverlay.classList.remove('ativo');
    }

    btnSanduiche.addEventListener('click', function () {
        if (window.innerWidth <= 991.98) {
            sidebar.classList.toggle('aberta');
            sidebarOverlay.classList.toggle('ativo');
        } else {
            sidebar.classList.toggle('oculta');
            conteudoPrincipal.classList.toggle('expandido');
        }
    });

    sidebarOverlay.addEventListener('click', fecharSidebarMobile);

    window.addEventListener('resize', function () {
        if (window.innerWidth > 991.98) {
            fecharSidebarMobile();
        }
    });
})();