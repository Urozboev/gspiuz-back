{{--
    Manzil maydoni ostidagi ogohlantirish.

    Ba'zi manzillar saytda kod bilan yozilgan sahifaga tegishli (masalan
    /news, /gallery). Admin shunday manzilni yangi bandga bersa, kod
    sahifasi ustun chiqadi va bu yerda kiritilgan kontent ko'rinmaydi.

    Ro'yxat: config/reserved_paths.php
--}}
<small class="form-text text-muted d-block mt-1">
    Manzil saytdagi sahifa manzili boʻladi, masalan <code>/citizen_appeal</code>.
    Sahifa aynan shu manzil boʻyicha topiladi.
</small>

<div class="alert alert-warning mt-2 d-none" id="path-reserved-warning" role="alert">
    <strong>Bu manzil band.</strong>
    Saytda <code id="path-reserved-name"></code> sahifasi allaqachon bor va u
    ustun chiqadi — bu yerda kiritilgan kontent koʻrinmaydi.
    Boshqa manzil tanlang, masalan <code id="path-reserved-hint"></code>.
</div>

<details class="mt-2">
    <summary class="text-muted" style="cursor: pointer; font-size: .8125rem;">
        Band manzillar roʻyxati ({{ count(config('reserved_paths', [])) }} ta)
    </summary>
    <p class="text-muted mt-2 mb-0" style="font-size: .8125rem;">
        @foreach (config('reserved_paths', []) as $reserved)
            <code>/{{ $reserved }}</code>{{ !$loop->last ? ' · ' : '' }}
        @endforeach
    </p>
</details>

<script>
            // Manzil yozilayotganda band manzillarni tekshiramiz.
            (function () {
                var reserved = @json(config('reserved_paths', []));
                var input = document.getElementById('path');
                var box = document.getElementById('path-reserved-warning');

                if (!input || !box) {
                    return;
                }

                var name = document.getElementById('path-reserved-name');
                var hint = document.getElementById('path-reserved-hint');

                function check() {
                    // "/news?tab=1" -> "news"
                    var value = (input.value || '').trim().replace(/^\/+/, '').split('?')[0];
                    var taken = value !== '' && reserved.indexOf(value) !== -1;

                    box.classList.toggle('d-none', !taken);

                    if (taken) {
                        name.textContent = '/' + value;
                        hint.textContent = '/' + value + '-2';
                    }
                }

                input.addEventListener('input', check);
                check();
            })();
</script>
