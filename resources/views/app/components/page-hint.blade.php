{{--
    Bo'lim sarlavhasi tagidagi bitta qatorli izoh: bu ma'lumot saytning
    qaysi sahifasida ko'rinadi. Xarita — config/admin_pages.php.

    Ishlatilishi:  @include('app.components.page-hint')
    ($route_name o'zgaruvchisi kontrollerdan keladi.)
--}}
@php
    $hint = config('admin_pages.' . ($route_name ?? ''), null);
@endphp

@if ($hint)
    <p class="text-muted mb-0 mt-1" style="font-size: .8125rem;">
        <i class="fe fe-info"></i>

        @if ($hint['path'] ?? null)
            Bu maʼlumot saytning
            <strong>{{ $hint['page'] }}</strong>
            (<code>{{ $hint['path'] }}</code>) sahifasida koʻrinadi.
        @else
            <strong>{{ $hint['page'] }}</strong>.
        @endif

        @if ($hint['note'] ?? null)
            {{ $hint['note'] }}
        @endif
    </p>
@endif
