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
            </div>
        </div>
        @include('app.components.breadcrumb', [
            'datas' => [
                ['active' => true, 'url' => '', 'name' => $title, 'disabled' => false]
            ]
        ])
    </div>
</div>

<div class="container-fluid">
    <div class="card mt-4">
        <div class="card-body">

            {{-- Filtrlar --}}
            <form method="GET" class="row g-2 mb-4">
                <div class="col-md-3">
                    <select name="type" class="form-select">
                        <option value="">Barcha turlar</option>
                        @foreach ($types as $key => $labels)
                            <option value="{{ $key }}" @selected(($filters['type'] ?? '') === $key)>{{ $labels['uz'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">Barcha holatlar</option>
                        @foreach ($statuses as $key => $labels)
                            <option value="{{ $key }}" @selected(($filters['status'] ?? '') === $key)>{{ $labels['uz'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Ariza raqami, ism yoki telefon" value="{{ $filters['search'] ?? '' }}">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100" type="submit">Filtrlash</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Ariza raqami</th>
                            <th scope="col">Turi</th>
                            <th scope="col">F.I.Sh.</th>
                            <th scope="col">Telefon</th>
                            <th scope="col">Holati</th>
                            <th scope="col">Sana</th>
                            <th scope="col"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($appeals as $item)
                        <tr>
                            <td><code>{{ $item->ticket }}</code></td>
                            <td>{{ $types[$item->type]['uz'] ?? $item->type }}</td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->phone_number ?? '--' }}</td>
                            <td>
                                @php
                                    $badge = [
                                        'new' => 'bg-secondary',
                                        'in_review' => 'bg-warning',
                                        'answered' => 'bg-success',
                                        'rejected' => 'bg-danger',
                                    ][$item->status] ?? 'bg-secondary';
                                @endphp
                                <span class="badge {{ $badge }}">{{ $statuses[$item->status]['uz'] ?? $item->status }}</span>
                            </td>
                            <td>{{ $item->created_at->format('d.m.Y H:i') }}</td>
                            <td style="width: 160px">
                                <div class="d-flex justify-content-end">
                                    <a href="{{ route($route_name.'.show', [$route_parameter => $item]) }}" class="btn btn-sm btn-info"><i class="fe fe-eye"></i></a>
                                    <a class="btn btn-sm btn-danger ms-3" onclick="if (confirm('O\'chirilsinmi?')) { event.preventDefault(); document.getElementById('delete-form{{ $item->id }}').submit(); }"><i class="fe fe-trash"></i></a>
                                    <form action="{{ route($route_name.'.destroy', [$route_parameter => $item]) }}" id="delete-form{{ $item->id }}" method="POST" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">Murojaatlar yo'q</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $appeals->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
