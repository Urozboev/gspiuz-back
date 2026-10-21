@extends('layouts.app')

@section('content')
<!-- HEADER -->
<div class="header">
    <div class="container-fluid">

        <!-- Body -->
        <div class="header-body">
            <div class="row align-items-end">
                <div class="col">

                    <!-- Title -->
                    <h1 class="header-title">
                        {{ $title }}
                    </h1>

                    @include('app.components.page-hint')

                </div>
                <div class="col-auto">

                    <!-- Button -->
                    <a href="{{ route($route_name.'.create') }}" class="btn btn-primary lift">
                        Qoʻshish
                    </a>

                </div>
            </div> <!-- / .row -->
        </div> <!-- / .header-body -->
        @include('app.components.breadcrumb', [
        'datas' => [
        [
        'active' => true,
        'url' => '',
        'name' => $title,
        'disabled' => false
        ]
        ]
        ])
    </div>
</div> <!-- / .header -->

<!-- CARDS -->
<div class="container-fluid">
    <div class="search">
        <div class="card">
            <div class="card-body">
                <form action="{{ route($route_name.'.index') }}" class="d-flex align-items-center gap-3">
                    <input type="text" class="form-control" name="search" value="{{ $search }}" placeholder="Familiya, ism yoki otasining ismi…">
                    <label class="form-check m-0 text-nowrap">
                        <input class="form-check-input" type="checkbox" name="unassigned" value="1" @checked($unassigned) onchange="this.form.submit()">
                        <span class="form-check-label">Faqat boʻlimsizlar</span>
                    </label>
                    <button type="submit" class="btn btn-success px-4">Qidirish</button>
                </form>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-body">
            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Xodim</th>
                            <th scope="col">Ish joylari</th>
                            <th scope="col"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($employs as $key => $item)
                        <tr>
                            <th scope="row" style="width: 60px">{{ $employs->firstItem() + $key }}</th>
                            <td>
                                <a href="{{ route($route_name.'.edit', $item->id) }}" class="d-flex align-items-center">
                                    <div class="imb-block me-2 overflow-hidden">
                                        <img src="{{ $item->sm_img ?? asset('assets/img/default.png') }}" alt="">
                                    </div>
                                    {{ $item->displayName($main_lang->code) }}
                                </a>
                            </td>
                            {{-- Ilgari bu yerda "Holati" ustuni bor edi va mavjud boʻlmagan
                                 `active` ni oʻqirdi — hamma xodim "Nofaol" koʻrinardi. --}}
                            <td class="small">
                                @forelse($item->employMetas as $meta)
                                    <div>{{ $meta->department->name[$main_lang->code] ?? '—' }}</div>
                                @empty
                                    <span class="badge bg-warning-subtle text-warning">Boʻlimga biriktirilmagan — saytda koʻrinmaydi</span>
                                @endforelse
                            </td>
                            <td style="width: 140px">
                                <div class="d-flex justify-content-end">
                                    <a href="{{ route($route_name.'.edit',  $item->id) }}" class="btn btn-sm btn-info"><i class="fe fe-edit-2"></i></a>
                                    <a class="btn btn-sm btn-danger ms-3" onclick="var result = confirm('Xodim butunlay oʻchirilsinmi? U barcha boʻlimlardan chiqariladi.');if (result){event.preventDefault();document.getElementById('delete-form{{ $item->id }}').submit();}"><i class="fe fe-trash"></i></a>
                                    <form action="{{ route($route_name.'.destroy', $item->id) }}" id="delete-form{{ $item->id }}" method="POST" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-5">Hech kim topilmadi.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">

                {{ $employs->links() }}
            </div>
        </div>
    </div>
</div>

@endsection
