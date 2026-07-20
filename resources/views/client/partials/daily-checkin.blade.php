@php
    $dailyService = app(\App\Services\DailyCheckinService::class);
    $dailyCheckin = $dailyCheckin ?? $dailyService->calendar(auth()->user());
    $claimedToday = (bool) ($dailyCheckin['claimed_today'] ?? false);
    $todayAmount = (int) ($dailyCheckin['today_amount'] ?? 0);
    $coinLabel = $dailyCheckin['coin_name'] ?? coin_name();
    $tomorrow = now()->copy()->addDay();
    $tomorrowDay = collect($dailyCheckin['days'] ?? [])->firstWhere('date', $tomorrow->toDateString());
    $tomorrowAmount = (int) ($tomorrowDay['amount'] ?? $dailyService->rewardForDate($tomorrow));
@endphp

@if(($dailyCheckin['enabled'] ?? false) && !$claimedToday)
<button type="button" class="alpha-daily-gift is-shaking" id="alpha-daily-gift" aria-label="Open daily bonus">
    <span class="alpha-daily-gift__icon"><i class="fa fa-gift"></i></span>
    <span class="alpha-daily-gift__copy">
        <strong>Daily Bonus</strong>
        <small>+{{ number_format($todayAmount) }} {{ $coinLabel }}</small>
    </span>
</button>

<div class="alpha-daily-checkin" id="alpha-daily-checkin" data-claim-url="{{ route_path('daily-checkin.claim') }}" aria-hidden="true">
    <div class="alpha-daily-checkin__shade" data-daily-close></div>
    <section class="alpha-daily-checkin__panel" role="dialog" aria-modal="true" aria-labelledby="alpha-daily-checkin-title">
        <button type="button" class="alpha-daily-checkin__close" data-daily-close aria-label="Close">
            <i class="fa fa-times"></i>
        </button>

        <header class="alpha-daily-checkin__head">
            <div class="alpha-daily-checkin__badge"><i class="fa fa-gift"></i></div>
            <small>Daily check-in</small>
            <h2 id="alpha-daily-checkin-title">Claim your daily reward</h2>
            <p>Open the calendar every day and collect {{ number_format($todayAmount) }} {{ $coinLabel }} for today's visit.</p>
        </header>

        <div class="alpha-daily-checkin__summary">
            <span><i class="fa fa-calendar-check"></i> {{ $dailyCheckin['month_label'] }}</span>
            <strong>{{ number_format($todayAmount) }} {{ $coinLabel }}</strong>
        </div>

        <div class="alpha-daily-checkin__week">
            <span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span><span>Sun</span>
        </div>

        <div class="alpha-daily-checkin__grid">
            @php
                $firstDate = \Carbon\Carbon::parse(($dailyCheckin['days'][0]['date'] ?? now()->toDateString()));
                $leading = $firstDate->dayOfWeekIso - 1;
            @endphp
            @for($i = 0; $i < $leading; $i++)
                <span class="alpha-daily-day alpha-daily-day--empty"></span>
            @endfor
            @foreach($dailyCheckin['days'] as $day)
                <span class="alpha-daily-day
                    {{ $day['today'] ? 'is-today' : '' }}
                    {{ $day['claimed'] ? 'is-claimed' : '' }}
                    {{ $day['special'] ? 'is-special' : '' }}
                    {{ $day['future'] ? 'is-future' : '' }}"
                    data-daily-date="{{ $day['date'] }}">
                    <b>{{ $day['day'] }}</b>
                    <small>{{ number_format($day['amount']) }}</small>
                    @if($day['claimed'])<i class="fa fa-check"></i>@endif
                </span>
            @endforeach
        </div>

        <div class="alpha-daily-checkin__actions">
            <button type="button" class="alpha-button alpha-button--primary alpha-daily-claim">
                <i class="fa fa-coins"></i>
                <span>Claim reward</span>
            </button>
            <button type="button" class="alpha-button alpha-daily-later" data-daily-close>Later</button>
        </div>

        <div class="alpha-daily-checkin__message" id="alpha-daily-checkin-message" aria-live="polite"></div>
    </section>
</div>

<div class="alpha-daily-toast" id="alpha-daily-toast" aria-hidden="true" aria-live="polite">
    <div class="alpha-daily-toast__shade" data-daily-toast-close></div>
    <section class="alpha-daily-toast__card" role="dialog" aria-modal="true" aria-labelledby="alpha-daily-toast-title">
        <div class="alpha-daily-toast__icon"><i class="fa fa-gift"></i></div>
        <small>Daily Bonus</small>
        <h3 id="alpha-daily-toast-title">Daily bonus is credited, keep it up!</h3>
        <strong class="alpha-daily-toast__amount">+{{ number_format($todayAmount) }} <i class="fa fa-coins"></i></strong>
        <p>Come back tomorrow to collect +{{ number_format($tomorrowAmount) }} {{ $coinLabel }}.</p>
        <button type="button" class="alpha-button alpha-button--primary" data-daily-toast-close>Glorious!</button>
    </section>
</div>

@push('scripts')
<script>
(function () {
    var gift = document.getElementById('alpha-daily-gift');
    var modal = document.getElementById('alpha-daily-checkin');
    var toast = document.getElementById('alpha-daily-toast');
    if (!gift || !modal) return;

    var claimButton = modal.querySelector('.alpha-daily-claim');
    var message = document.getElementById('alpha-daily-checkin-message');
    var claimUrl = modal.getAttribute('data-claim-url');
    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    var csrf = csrfMeta ? csrfMeta.getAttribute('content') : (window.CSRF_TOKEN || '');
    var coinLabel = @json($coinLabel);

    function openModal() {
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
    }

    function closeModal() {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
    }

    function showToast() {
        if (!toast) return;
        toast.classList.add('is-open');
        toast.setAttribute('aria-hidden', 'false');
    }

    function closeToast() {
        if (!toast) return;
        toast.classList.remove('is-open');
        toast.setAttribute('aria-hidden', 'true');
    }

    gift.addEventListener('click', openModal);
    document.querySelectorAll('[data-daily-open]').forEach(function (button) {
        button.addEventListener('click', function (event) {
            event.preventDefault();
            openModal();
        });
    });

    modal.querySelectorAll('[data-daily-close]').forEach(function (button) {
        button.addEventListener('click', closeModal);
    });

    if (toast) {
        toast.querySelectorAll('[data-daily-toast-close]').forEach(function (button) {
            button.addEventListener('click', closeToast);
        });
    }

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') return;
        if (modal.classList.contains('is-open')) closeModal();
        if (toast && toast.classList.contains('is-open')) closeToast();
    });

    if (!claimButton) return;

    claimButton.addEventListener('click', function () {
        claimButton.disabled = true;
        claimButton.classList.add('is-loading');
        claimButton.querySelector('span').textContent = 'Claiming...';
        message.textContent = '';

        fetch(claimUrl, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf
            },
            body: '{}'
        })
        .then(function (response) { return response.json(); })
        .then(function (data) {
            var amount = Number(data.amount || {{ $todayAmount }});
            claimButton.classList.remove('is-loading');
            claimButton.querySelector('span').textContent = data.already_claimed ? 'Claimed today' : 'Claimed';
            message.textContent = data.already_claimed
                ? 'Today reward was already claimed.'
                : ('You received ' + amount.toLocaleString() + ' ' + coinLabel + '.');

            var todayCell = modal.querySelector('.alpha-daily-day.is-today');
            if (todayCell) {
                todayCell.classList.add('is-claimed');
                if (!todayCell.querySelector('.fa-check')) {
                    todayCell.insertAdjacentHTML('beforeend', '<i class="fa fa-check"></i>');
                }
            }

            var coins = document.querySelector('.header-coins');
            if (coins && typeof data.balance !== 'undefined') {
                coins.innerHTML = Number(data.balance || 0).toLocaleString() + '<i class="fa fa-coins"></i>';
            }

            gift.classList.add('is-hidden');
            gift.setAttribute('aria-hidden', 'true');
            window.setTimeout(function () {
                gift.hidden = true;
            }, 240);
            window.setTimeout(function () {
                closeModal();
                showToast();
            }, 450);
        })
        .catch(function () {
            claimButton.disabled = false;
            claimButton.classList.remove('is-loading');
            claimButton.querySelector('span').textContent = 'Claim reward';
            message.textContent = 'Could not claim reward. Please try again.';
        });
    });
})();
</script>
@endpush
@endif
