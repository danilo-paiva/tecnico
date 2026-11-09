/**
 * Monta a estrutura comum das páginas autenticadas (sidebar + topbar)
 * e injeta o conteúdo do <template id="conteudo">.
 */
(function montarLayout() {
    if (!window.Sessao || !Sessao.estaLogado()) {
        window.location.replace("login.html");
        return;
    }

    const app = document.getElementById("app");
    const template = document.getElementById("conteudo");
    if (!app || !template) return;

    const pagina = document.body.getAttribute("data-page") || "dashboard";
    const usuario = Sessao.usuario() || {};
    const api = Sessao.apiConfig();

    const paginas = {
        dashboard: { eyebrow: "Operação", titulo: "Visão geral" },
        eventos: { eyebrow: "Catálogo", titulo: "Eventos" },
        lotes: { eyebrow: "Catálogo", titulo: "Lotes de ingresso" },
        organizadores: { eyebrow: "Equipe", titulo: "Organizadores" }
    };
    const meta = paginas[pagina] || paginas.dashboard;

    const icones = {
        dashboard: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>',
        eventos: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4M16 3v4M3 10h18"/></svg>',
        lotes: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 9a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v1a2 2 0 0 0 0 4v1a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-1a2 2 0 0 0 0-4z"/><path d="M13 7v10" stroke-dasharray="2 3"/></svg>',
        organizadores: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="8" r="3.2"/><path d="M3 20c0-3.3 2.7-5.5 6-5.5s6 2.2 6 5.5"/><path d="M16 5.2A3 3 0 0 1 16 11M18 20c0-2.6-1-4.4-2.5-5.3"/></svg>'
    };

    const itensNav = [
        ["dashboard", "index.html", "Visão geral"],
        ["eventos", "eventos.html", "Eventos"],
        ["lotes", "lotes.html", "Lotes"],
        ["organizadores", "organizadores.html", "Organizadores"]
    ];

    app.innerHTML = `
        <div class="shell">
            <aside class="sidebar">
                <a class="brand" href="index.html">
                    <span class="brand-mark">P</span>
                    <span class="brand-text">pulse<small>eventos</small></span>
                </a>

                <div class="nav-label">Painel</div>
                <nav class="nav">
                    ${itensNav.map(([id, href, rotulo]) => `
                        <a href="${href}" data-nav="${id}" class="${id === pagina ? "active" : ""}">
                            ${icones[id]}<span>${rotulo}</span>
                        </a>`).join("")}
                </nav>

                <div class="sidebar-card">
                    <span class="label">API em uso</span>
                    <div class="api-status">
                        <span class="api-dot" id="apiDot"></span>
                        <div>
                            <b id="apiNome">${UI.esc(api.nome)}</b>
                            <small>${UI.esc(api.origem)}</small>
                        </div>
                    </div>
                    <div class="segmented" id="seletorApi">
                        <button type="button" data-api="js" class="${api.id === "js" ? "active" : ""}">Node</button>
                        <button type="button" data-api="flask" class="${api.id === "flask" ? "active" : ""}">Flask</button>
                    </div>
                </div>
            </aside>

            <div class="main">
                <header class="topbar">
                    <div>
                        <p class="eyebrow">${UI.esc(meta.eyebrow)} · PAW 4º bimestre</p>
                        <h1>${UI.esc(meta.titulo)}</h1>
                    </div>
                    <div class="topbar-actions">
                        <span class="badge accent" style="font-family:var(--mono);font-size:10px;letter-spacing:.1em">
                            api ${UI.esc(api.curto.toLowerCase())}
                        </span>
                        <div class="user">
                            <span class="avatar">${UI.esc(UI.iniciais(usuario.nome))}</span>
                            <div>
                                <b>${UI.esc(usuario.nome || "Usuário")}</b>
                                <small>${UI.esc(usuario.perfil || "")}</small>
                            </div>
                        </div>
                        <button type="button" class="btn btn-ghost btn-sm" id="btnSair">Sair</button>
                    </div>
                </header>

                <main class="content" id="content"></main>
            </div>
        </div>`;

    document.getElementById("content").appendChild(template.content.cloneNode(true));

    document.getElementById("btnSair").addEventListener("click", () => Sessao.sair());

    document.querySelectorAll("#seletorApi button").forEach((botao) => {
        botao.addEventListener("click", () => {
            if (botao.getAttribute("data-api") === Sessao.apiAtual()) return;
            Sessao.definirApi(botao.getAttribute("data-api"));
            window.location.reload();
        });
    });

    // Health check: pinta o indicador da API.
    Sessao.testarApi().then((online) => {
        const dot = document.getElementById("apiDot");
        const nome = document.getElementById("apiNome");
        if (!dot || !nome) return;
        dot.className = "api-dot " + (online ? "on" : "off");
        nome.textContent = online ? api.nome : `${api.nome} (offline)`;
    });

    window.layoutPronto = true;
})();
