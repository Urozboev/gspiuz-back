@extends('layouts.app')

@section('links')
    @include('admin.departaments._dropzone')
@endsection

@section('content')
    @php $name = $departament->name[$main_lang->code] ?? ''; @endphp

    <div class="header">
        <div class="container-fluid">
            <div class="header-body">
                <div class="row align-items-end">
                    <div class="col">
                        <h6 class="header-pretitle">{{ $departament->structureType->name[$main_lang->code] ?? $title }}</h6>
                        <h1 class="header-title">{{ $name }}</h1>
                        @include('app.components.page-hint')
                    </div>
                </div>
            </div>
            @include('app.components.breadcrumb', [
                'datas' => [
                    ['active' => false, 'url' => route($route_name.'.index'), 'name' => $title, 'disabled' => false],
                    ['active' => true, 'url' => '', 'name' => $name, 'disabled' => true],
                ]
            ])
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">

            {{-- Boʻlim haqida --}}
            <div class="col-xl-7">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-header-title">Boʻlim haqida</h4>
                    </div>
                    <div class="card-body">
                        <form method="post" action="{{ route($route_name . '.update', $departament->id) }}" id="department-form">
                            @csrf
                            @method('PUT')

                            @include('admin.departaments._form')

                            <div class="d-flex justify-content-end mt-3">
                                <a href="{{ route($route_name.'.index') }}" class="btn btn-secondary">Orqaga</a>
                                <button type="submit" class="btn btn-primary ms-2">Saqlash</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Xodimlar --}}
            <div class="col-xl-5">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-header-title">Xodimlar</h4>
                        <span class="badge bg-secondary-subtle text-secondary">{{ $staff->count() }}</span>
                    </div>

                    @if($staff->isEmpty())
                        <div class="card-body text-muted">
                            Hali hech kim biriktirilmagan. Pastdan qoʻshing.
                        </div>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach($staff as $meta)
                                <li class="list-group-item d-flex align-items-center gap-3 px-4">
                                    <div class="flex-grow-1">
                                        <a href="{{ route('employs.edit', $meta->employ_id) }}" class="fw-semibold">
                                            {{ $meta->employ->displayName($main_lang->code) }}
                                        </a>
                                        <div class="small text-muted">
                                            {{ $meta->position->name[$main_lang->code] ?? '—' }}
                                            @unless($meta->active) · <span class="text-warning">yashirin</span> @endunless
                                        </div>
                                    </div>
                                    <form method="post" action="{{ route('departaments.staff.destroy', [$departament->id, $meta->id]) }}"
                                          onsubmit="return confirm('Xodim shu boʻlimdan chiqarilsinmi? Xodimning oʻzi oʻchmaydi.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Boʻlimdan chiqarish">Chiqarish</button>
                                    </form>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    <div class="card-footer">
                        <h5 class="mb-3">Xodim qoʻshish</h5>
                        <form method="post" action="{{ route('departaments.staff.store', $departament->id) }}">
                            @csrf
                            <div class="form-group mb-2">
                                <select name="employ_id" class="form-select" data-choices='{"searchEnabled": true, "searchPlaceholderValue": "Familiya yoki ism…"}' required>
                                    <option value="">— xodimni tanlang —</option>
                                    @foreach($employees as $person)
                                        <option value="{{ $person['id'] }}">{{ $person['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="row g-2 mb-2">
                                <div class="col-8">
                                    <select name="position_id" class="form-select" required>
                                        <option value="">— lavozim —</option>
                                        @foreach($positions as $position)
                                            <option value="{{ $position->id }}">{{ $position->name[$main_lang->code] ?? $position->id }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-4">
                                    <input type="number" min="0" name="order" class="form-control" placeholder="Tartib">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Qoʻshish</button>
                            <div class="form-text mt-2">
                                Xodim bitta yozuv boʻlib qoladi. Uni boshqa boʻlimga ham qoʻshsangiz,
                                maʼlumoti ikkala joyda ham bir xil koʻrinadi.
                                Roʻyxatda yoʻq boʻlsa — avval
                                <a href="{{ route('employs.create') }}">yangi xodim yarating</a>.
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
