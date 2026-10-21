@extends('layouts.app')

@section('content')
    <div class="header">
        <div class="container-fluid">
            <div class="header-body">
                <div class="row align-items-end">
                    <div class="col">
                        <h1 class="header-title">{{ $title }}</h1>
                        @include('app.components.page-hint')
                    </div>
                    <div class="col-auto">
                        <a href="{{ route($route_name.'.create', $type ? ['type' => $type] : []) }}" class="btn btn-primary lift">
                            Qoʻshish
                        </a>
                    </div>
                </div>
            </div>
            @include('app.components.breadcrumb', [
                'datas' => [['active' => true, 'url' => '', 'name' => $title, 'disabled' => false]]
            ])
        </div>
    </div>

    <div class="container-fluid">

        {{-- Tur boʻyicha boʻlinish: saytdagi menyu shunday ajratilgan. --}}
        <ul class="nav nav-pills mb-3 flex-wrap gap-1">
            <li class="nav-item">
                <a class="nav-link {{ $type ? '' : 'active' }}" href="{{ route($route_name.'.index', array_filter(['search' => $search])) }}">Hammasi</a>
            </li>
            @foreach($StructureTypes as $item)
                <li class="nav-item">
                    <a class="nav-link {{ $type === (int) $item->id ? 'active' : '' }}"
                       href="{{ route($route_name.'.index', array_filter(['type' => $item->id, 'search' => $search])) }}">
                        {{ $item->name[$main_lang->code] ?? $item->id }}
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="card">
            <div class="card-body">
                <form action="{{ route($route_name.'.index') }}" class="d-flex">
                    @if($type)
                        <input type="hidden" name="type" value="{{ $type }}">
                    @endif
                    <input type="text" class="form-control" name="search" value="{{ $search }}" placeholder="Nomi boʻyicha qidirish…">
                    <button type="submit" class="btn btn-success ms-3 px-4">Qidirish</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover card-table mb-0">
                    <thead>
                    <tr>
                        <th>Nomi</th>
                        <th>Turi</th>
                        <th class="text-center">Xodimlar</th>
                        <th class="text-center">Maʼlumot</th>
                        <th>Holati</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($departaments as $departament)
                        @php
                            $hasInfo = mb_strlen(trim(strip_tags((string) ($departament->description[$main_lang->code] ?? '')))) > 20;
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route($route_name.'.edit', $departament->id) }}" class="fw-semibold">
                                    {{ $departament->name[$main_lang->code] ?? '—' }}
                                </a>
                                @if($departament->parent)
                                    <div class="small text-muted">{{ $departament->parent->name[$main_lang->code] ?? '' }}</div>
                                @endif
                            </td>
                            <td class="small">{{ $departament->structureType->name[$main_lang->code] ?? '—' }}</td>
                            <td class="text-center">
                                @if($departament->staff_count)
                                    {{ $departament->staff_count }}
                                @else
                                    <span class="badge bg-warning-subtle text-warning">yoʻq</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($hasInfo)
                                    <span class="badge bg-success-subtle text-success">bor</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">yoʻq</span>
                                @endif
                            </td>
                            <td>
                                @if($departament->active)
                                    <span class="text-success">● Saytda</span>
                                @else
                                    <span class="text-muted">○ Yashirin</span>
                                @endif
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route($route_name.'.edit', $departament->id) }}" class="btn btn-sm btn-info" title="Tahrirlash">
                                    <i class="fe fe-edit-2"></i>
                                </a>
                                <form action="{{ route($route_name.'.destroy', $departament->id) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Boʻlim oʻchirilsinmi? Xodimlarning oʻzi oʻchmaydi.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Oʻchirish"><i class="fe fe-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">Hech narsa topilmadi.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">{{ $departaments->links() }}</div>
    </div>
@endsection
