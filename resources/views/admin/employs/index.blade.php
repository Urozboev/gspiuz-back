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
                            <th scope="col">Sana</th>
                            <th scope="col">Holati</th>
                            <th scope="col"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($employs as $key => $item)
                        <tr>
                            <th scope="row" style="width: 100px">{{ $employs->firstItem() + $key }}</th>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="imb-block me-2 overflow-hidden">
                                        <img src="{{ $item->sm_img ?? asset('assets/img/default.png') }}" alt="">
                                    </div>
                                    {{ $item->first_name[$main_lang->code]?? null}} {{ $item->last_name[$main_lang->code]?? null}}
                                </div>
                            </td>
                            <td>{{ isset($item->birthday) ? date('d-m-Y', strtotime($item->birthday)) : '--' }}</td>
                            <td>
                                @if($item->active == 1)
                                    <span style="color: green; font-weight: bold;">Faol</span>
                                @else
                                    <span style="color: red; font-weight: bold;">Nofaol</span>
                                @endif
                            </td>                            <td style="width: 200px">
                                <div class="d-flex justify-content-end">
                                    <a href="{{ route($route_name.'.edit',  $item->id) }}" class="btn btn-sm btn-info"><i class="fe fe-edit-2"></i></a>
                                    <a class="btn btn-sm btn-danger ms-3" onclick="var result = confirm('Want to delete?');if (result){event.preventDefault();document.getElementById('delete-form{{ $item->id }}').submit();}"><i class="fe fe-trash"></i></a>
                                    <form action="{{ route($route_name.'.destroy', $item->id) }}" id="delete-form{{ $item->id }}" method="POST" style="display: none;">
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

                {{ $employs->links() }}
            </div>
        </div>
    </div>
</div>

@endsection
