{{-- Floating assistant. Answers come from AiAssistantController, which only
     ever names businesses that are live in the directory, and the links under
     an answer are built from the database rather than from the model.

     Two stages: a welcome card, then the conversation itself once a question
     has been asked. --}}
@php
    $botSite = \App\Support\AssistantContext::siteName();
    $botCity = trim((string) (session('detected_city') ?: ''));
    $botSuggestions = $botCity !== ''
        ? ['Packers and Movers in ' . $botCity, 'Best hospital in ' . $botCity, 'AC repair near me', 'List my business']
        : ['Packers and Movers in Jaipur', 'Best hospital in Jaipur', 'AC repair near me', 'List my business'];
@endphp

<button type="button" id="sbBotLauncher" class="sb-bot-launcher" aria-label="Open assistant">
    <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <rect x="4" y="8" width="16" height="11" rx="3"></rect>
        <path d="M12 8V4.5"></path><circle cx="12" cy="3.2" r="1.3" fill="currentColor" stroke="none"></circle>
        <path d="M2.5 12.5v2M21.5 12.5v2"></path>
        <circle cx="9" cy="13" r="1.1" fill="currentColor" stroke="none"></circle>
        <circle cx="15" cy="13" r="1.1" fill="currentColor" stroke="none"></circle>
        <path d="M9.5 16.3h5"></path>
    </svg>
</button>

<div id="sbBotPanel" class="sb-bot-panel" role="dialog" aria-label="{{ $botSite }} assistant" aria-hidden="true"
     data-endpoint="{{ route('assistant.chat') }}"
     data-greeting="Hi there, I am the {{ $botSite }} Assistant. Tell me the service you need and your city, and I will find the businesses for you."
     data-suggestions='@json($botSuggestions)'>

    {{-- Stage 1: welcome card --}}
    <div class="sb-bot-stage is-active" id="sbBotWelcome">
        <div class="sb-bot-head">
            <span class="sb-bot-avatar">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect x="4" y="8" width="16" height="11" rx="3"></rect>
                    <path d="M12 8V4.5"></path><circle cx="12" cy="3.2" r="1.3" fill="currentColor" stroke="none"></circle>
                    <circle cx="9" cy="13" r="1.1" fill="currentColor" stroke="none"></circle>
                    <circle cx="15" cy="13" r="1.1" fill="currentColor" stroke="none"></circle>
                    <path d="M9.5 16.3h5"></path>
                </svg>
            </span>

            <button type="button" class="sb-bot-close" aria-label="Close assistant">&times;</button>

            <h3>Hello</h3>
            <p>
                Welcome to <strong>{{ $botSite }}</strong> — tell me the service you need and
                your city, and I will find the businesses for you.
            </p>

            <form class="sb-bot-form" id="sbBotWelcomeForm" autocomplete="off">
                <input type="text" id="sbBotWelcomeInput" placeholder="Ask your query" maxlength="300" aria-label="Ask your query">
                <button type="submit" aria-label="Send">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor" aria-hidden="true">
                        <path d="M3.4 20.4 21 12 3.4 3.6l.1 6.5L15 12l-11.5 1.9z"></path>
                    </svg>
                </button>
            </form>
        </div>

        <div class="sb-bot-welcome-body">
            <div class="sb-bot-chips">
                @foreach($botSuggestions as $suggestion)
                    <button type="button" class="sb-bot-chip">{{ $suggestion }}</button>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Stage 2: the conversation --}}
    <div class="sb-bot-stage" id="sbBotChat">
        <div class="sb-bot-bar">
            <span class="sb-bot-bar-title">Welcome to {{ $botSite }}!</span>
            <button type="button" class="sb-bot-close" aria-label="Close assistant">&times;</button>
        </div>

        <div class="sb-bot-thread" id="sbBotBody"></div>

        <form class="sb-bot-input-bar" id="sbBotForm" autocomplete="off">
            <button type="button" class="sb-bot-home" id="sbBotHome" aria-label="Back to start" title="Back to start">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M3 10.5 12 3l9 7.5"></path><path d="M5 9.8V20h14V9.8"></path><path d="M10 20v-5h4v5"></path>
                </svg>
            </button>
            <input type="text" id="sbBotInput" placeholder="Type here..." maxlength="300" aria-label="Type your message">
            <button type="submit" class="sb-bot-send" aria-label="Send">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true">
                    <path d="M3.4 20.4 21 12 3.4 3.6l.1 6.5L15 12l-11.5 1.9z"></path>
                </svg>
            </button>
        </form>
    </div>
</div>

@include('themes.servicebazzar.partials.inline-css', ['file' => 'layout/ai-assistant.css'])
@include('themes.servicebazzar.partials.inline-js', ['file' => 'layout/ai-assistant.js'])
