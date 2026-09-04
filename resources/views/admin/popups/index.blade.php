@extends('layouts.app')

@section('content')

    <div class="header">
        <div class="container-fluid">
            <div class="header-body">
                <div class="row align-items-end">
                    <div class="col">
                        <h1 class="header-title">
                            {{ $title }}
                        </h1>

                        @include('app.components.page-hint')
                    </div>
                    <div class="col-auto">
                        <a href="{{ route($route_name.'.create') }}" class="btn btn-primary lift">
                            Qoʻshish
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">

        <div class="card">
            <div class="card-body">
                <form action="{{ route($route_name.'.index') }}" class="d-flex">
                    <input type="text" class="form-control" name="search"
                           value="{{ $search }}" placeholder="Qidirish...">
                    <button type="submit" class="btn btn-success ms-3" style="width: 200px;">Qidirish</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Sarlavha</th>
                            <th>Koʻrsatiladi</th>
                            <th>Holati</th>
                            <th>Tartib</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse ($popups as $popup)
                            <tr>
                                <td>{{ $popup->id }}</td>
                                <td>
                                    <strong>{{ data_get($popup->title, $main_lang->code) }}</strong>
                                </td>
                                <td>
                                    @if ($popup->starts_at || $popup->ends_at)
                                        {{ optional($popup->starts_at)->format('d.m.Y') ?: '…' }}
                                        —
                                        {{ optional($popup->ends_at)->format('d.m.Y') ?: '…' }}
                                    @else
                                        <span class="text-muted">muddatsiz</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $today = now()->startOfDay();
                                        $started = !$popup->starts_at || $popup->starts_at->lte($today);
                                        $notEnded = !$popup->ends_at || $popup->ends_at->gte($today);
                                    @endphp

                                    @if (!$popup->active)
                                        <span class="text-muted">Oʻchirilgan</span>
                                    @elseif ($started && $notEnded)
                                        <span class="text-success fw-bold">Saytda koʻrinmoqda</span>
                                    @elseif (!$started)
                                        <span class="text-warning">Muddati hali kelmagan</span>
                                    @else
                                        <span class="text-muted">Muddati tugagan</span>
                                    @endif
                                </td>
                                <td>{{ $popup->order }}</td>
                                <td class="text-end">
                                    <a href="{{ route($route_name.'.edit', $popup->id) }}"
                                       class="btn btn-sm btn-white">Tahrirlash</a>

                                    <form action="{{ route($route_name.'.destroy', $popup->id) }}"
                                          method="post" class="d-inline"
                                          onsubmit="return confirm('Oʻchirilsinmi?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-white text-danger">Oʻchirish</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    Hozircha xabar yoʻq. “Qoʻshish” tugmasi orqali bayram tabrigi yoki
                                    eʼlon qoʻshishingiz mumkin.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{ $popups->links() }}

    </div>

@endsection
