(function () {
    var launcher = document.getElementById('sbBotLauncher');
    var panel = document.getElementById('sbBotPanel');
    var welcome = document.getElementById('sbBotWelcome');
    var chat = document.getElementById('sbBotChat');
    var welcomeForm = document.getElementById('sbBotWelcomeForm');
    var welcomeInput = document.getElementById('sbBotWelcomeInput');
    var form = document.getElementById('sbBotForm');
    var input = document.getElementById('sbBotInput');
    var body = document.getElementById('sbBotBody');
    var homeBtn = document.getElementById('sbBotHome');

    if (!launcher || !panel || !welcome || !chat || !form || !input || !body) {
        return;
    }

    var endpoint = panel.getAttribute('data-endpoint') || '/assistant/chat';
    var greeting = panel.getAttribute('data-greeting') || 'Hi there! What are you looking for?';
    var suggestions = [];

    try {
        suggestions = JSON.parse(panel.getAttribute('data-suggestions') || '[]');
    } catch (e) {
        suggestions = [];
    }

    var csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content
        || (document.querySelector('input[name="_token"]') || {}).value
        || '';

    // The last few turns go back with each message so follow-ups like
    // "and in Delhi?" still make sense. Kept in memory only.
    var history = [];
    var busy = false;
    var started = false;

    /**
     * Where the visitor is, as the site already worked out: the header's
     * location box and the coordinates behind it live in localStorage, not in
     * the session, so the server cannot see them unless they are sent.
     */
    function place() {
        var out = {};

        try {
            var lat = parseFloat(localStorage.getItem('jf_lat'));
            var lng = parseFloat(localStorage.getItem('jf_lng'));
            if (isFinite(lat) && isFinite(lng)) {
                out.lat = lat;
                out.lng = lng;
            }

            var box = document.getElementById('locationInput')
                || document.getElementById('headerLocationInput')
                || document.getElementById('homeLocationInput');

            var name = (box && box.value.trim())
                || localStorage.getItem('jf_selected_location_name')
                || localStorage.getItem('jf_detected_city')
                || localStorage.getItem('jf_city')
                || '';

            if (name) out.place = String(name).slice(0, 120);
        } catch (e) {
            // Private mode, or storage blocked — the server falls back to the session.
        }

        return out;
    }

    function open() {
        panel.classList.add('is-open');
        panel.setAttribute('aria-hidden', 'false');
        launcher.classList.add('is-hidden');
        setTimeout(function () {
            (started ? input : welcomeInput).focus();
        }, 180);
    }

    function close() {
        panel.classList.remove('is-open');
        panel.setAttribute('aria-hidden', 'true');
        launcher.classList.remove('is-hidden');
    }

    function showStage(el) {
        welcome.classList.toggle('is-active', el === welcome);
        chat.classList.toggle('is-active', el === chat);
    }

    /** Opens the conversation, with the greeting and the suggestions in it. */
    function startChat() {
        if (started) {
            showStage(chat);
            return;
        }

        started = true;
        showStage(chat);
        bubble(greeting, 'bot');

        if (suggestions.length) {
            var wrap = document.createElement('div');
            wrap.className = 'sb-bot-chips';

            suggestions.forEach(function (text) {
                var chip = document.createElement('button');
                chip.type = 'button';
                chip.className = 'sb-bot-chip';
                chip.textContent = text;
                wrap.appendChild(chip);
            });

            body.appendChild(wrap);
            body.scrollTop = body.scrollHeight;
        }
    }

    launcher.addEventListener('click', open);

    panel.addEventListener('click', function (e) {
        if (e.target.closest('.sb-bot-close')) close();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && panel.classList.contains('is-open')) close();
    });

    // Tapping the welcome box is enough to move on, as in the reference.
    welcomeInput.addEventListener('focus', function () {
        var typed = welcomeInput.value.trim();
        startChat();
        input.value = typed;
        setTimeout(function () { input.focus(); }, 60);
    });

    welcomeForm.addEventListener('submit', function (e) {
        e.preventDefault();
        var text = welcomeInput.value.trim();
        startChat();
        if (text) ask(text);
    });

    if (homeBtn) {
        homeBtn.addEventListener('click', function () {
            showStage(welcome);
            welcomeInput.value = '';
        });
    }

    function bubble(text, who) {
        var el = document.createElement('div');
        el.className = 'sb-bot-msg from-' + (who === 'user' ? 'user' : 'bot') + (who === 'error' ? ' is-error' : '');
        el.textContent = text;
        body.appendChild(el);
        body.scrollTop = body.scrollHeight;
        return el;
    }

    function showLinks(links) {
        if (!links || !links.length) return;

        var wrap = document.createElement('div');
        wrap.className = 'sb-bot-links';

        links.forEach(function (link) {
            if (!link || !link.url) return;
            var a = document.createElement('a');
            a.href = link.url;
            a.textContent = link.label || link.url;
            wrap.appendChild(a);
        });

        body.appendChild(wrap);
    }

    /**
     * Scroll so the visitor's own question sits at the top of the view and
     * the answer reads downward from it. Scrolling to the very bottom put
     * them in the middle of the business links with the answer off-screen;
     * scrolling to the answer itself still hid the question it belongs to.
     */
    function revealFrom(el) {
        if (!el) return;
        var top = el.offsetTop - 10;
        body.scrollTop = Math.min(Math.max(top, 0), body.scrollHeight - body.clientHeight);
    }

    function typing() {
        var el = document.createElement('div');
        el.className = 'sb-bot-typing';
        el.innerHTML = '<span></span><span></span><span></span>';
        body.appendChild(el);
        body.scrollTop = body.scrollHeight;
        return el;
    }

    function ask(text) {
        if (busy || !text) return;
        busy = true;

        startChat();
        var asked = bubble(text, 'user');
        input.value = '';
        welcomeInput.value = '';
        var dots = typing();

        fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrf
            },
            credentials: 'same-origin',
            body: JSON.stringify(Object.assign({ message: text, history: history.slice(-8) }, place()))
        })
            .then(function (res) {
                if (res.status === 429) throw new Error('slow down');
                if (!res.ok) throw new Error('bad status');
                return res.json();
            })
            .then(function (data) {
                dots.remove();
                var reply = (data && data.reply) || 'Sorry, I could not answer that.';
                bubble(reply, 'bot');
                showLinks(data && data.links);
                revealFrom(asked);
                history.push({ role: 'user', text: text });
                history.push({ role: 'assistant', text: reply });
            })
            .catch(function (err) {
                dots.remove();
                bubble(
                    String(err && err.message) === 'slow down'
                        ? 'That is a lot of questions at once — please wait a moment and try again.'
                        : 'Sorry, something went wrong. Please try again.',
                    'error'
                );
            })
            .then(function () {
                busy = false;
                input.focus();
            });
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        ask(input.value.trim());
    });

    // Chips live in both stages, and new ones arrive with the greeting.
    panel.addEventListener('click', function (e) {
        var chip = e.target.closest('.sb-bot-chip');
        if (chip) ask(chip.textContent.trim());
    });
})();
