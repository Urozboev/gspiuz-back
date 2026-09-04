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
                    <form action="{{ route($route_name.'.index') }}" class="d-flex">
                        <input type="text" class="form-control" name="search" value="{{$search}}" placeholder="Qidirish...">
                        <button type="submit" class="btn btn-success ms-3" style="width: 300px;">Qidirish</button>
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
                            <th scope="col">Nomi</th>
                            <th scope="col">Menyu</th>
                            <th scope="col">Holati</th>
                            <th scope="col">Sana</th>
                            <th scope="col"></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($menus as $key => $item)
                            <tr>
                                <th scope="row" style="width: 100px">{{ $menus->firstItem() + $key }}</th>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="imb-block me-2 overflow-hidden">
                                            <img src="{{ isset($item->background) ? $item->lg_img : asset('assets/img/default.png') }}" alt="">
                                        </div>
                                        {{ $item->title[$main_lang->code] ?? null}}
                                    </div>
                                </td>
                                <td>{{ $item->menu ? $item->menu->title[$main_lang->code] : '—' }}</td>
                                {{--
                                    Sahifa toʻldirilganmi yoki yoʻqmi. Bir sahifalik
                                    koʻrinishda matn boʻlishi kerak, kartochka va fayl
                                    koʻrinishida esa kamida bitta yozuv.
                                --}}
                                @php
                                    $isSingle = $item->layout === 'single';
                                    $filled = $isSingle
                                        ? mb_strlen(trim(strip_tags((string) ($item->text[$main_lang->code] ?? '')))) > 20
                                        : ($item->forms_count ?? 0) > 0;
                                @endphp
                                <td>
                                    @if ($filled)
                                        <span class="badge bg-success-subtle text-success">
                                            @if (! $isSingle) {{ $item->forms_count }} ta @else Toʻldirilgan @endif
                                        </span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning">Boʻsh</span>
                                    @endif
                                </td>
                                <td>{{ isset($item->created_at) ? date('d-m-Y', strtotime($item->created_at)) : '--' }}</td>
                                <td style="width: 200px">
                                    <div class="d-flex justify-content-end">
                                        {{-- Kartochkalar / fayllar / boʻlimlar — sahifa koʻrinishiga qarab. --}}
                                        <a href="{{ route('page-items.index', $item->id) }}"
                                           class="btn btn-sm btn-white"
                                           title="Sahifa yozuvlari">
                                            @if ($item->layout === 'files')
                                                Fayllar
                                            @elseif ($item->layout === 'cards')
                                                Kartochkalar
                                            @else
                                                Boʻlimlar
                                            @endif
                                        </a>
                                        <a href="{{ route($route_name.'.edit', $item->id) }}" class="btn btn-sm btn-info"><i class="fe fe-edit-2"></i></a>
                                        <a class="btn btn-sm btn-danger ms-3" onclick="var result = confirm('Want to delete?');if (result){event.preventDefault();document.getElementById('delete-form{{ $item->id }}').submit();}"><i class="fe fe-trash"></i></a>
                                        <form action="{{ route($route_name.'.destroy',$item->id) }}" id="delete-form{{ $item->id }}" method="POST" style="display: none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $menus->links() }}
                </div>
            </div>
        </div>
    </div>

@endsection
