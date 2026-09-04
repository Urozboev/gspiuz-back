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
                            <th>Tadbir</th>
                            <th>Sana</th>
                            <th>Joyi</th>
                            <th>Holati</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse ($events as $event)
                            <tr>
                                <td>{{ $event->id }}</td>
                                <td><strong>{{ data_get($event->title, $main_lang->code) }}</strong></td>
                                <td>
                                    {{ optional($event->date)->format('d.m.Y') }}
                                    @if ($event->end_date)
                                        — {{ $event->end_date->format('d.m.Y') }}
                                    @endif
                                    @if ($event->time)
                                        <span class="text-muted">{{ $event->time }}</span>
                                    @endif
                                </td>
                                <td>{{ data_get($event->location, $main_lang->code) }}</td>
                                <td>
                                    @if (!$event->active)
                                        <span class="text-muted">Oʻchirilgan</span>
                                    @elseif ($event->date && $event->date->isFuture())
                                        <span class="text-success fw-bold">Kutilmoqda</span>
                                    @else
                                        <span class="text-muted">Oʻtgan</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route($route_name.'.edit', $event->id) }}"
                                       class="btn btn-sm btn-white">Tahrirlash</a>

                                    <form action="{{ route($route_name.'.destroy', $event->id) }}"
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
                                    Hozircha tadbir yoʻq. “Qoʻshish” tugmasi orqali kalendarga tadbir kiriting.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{ $events->links() }}

    </div>

@endsection
