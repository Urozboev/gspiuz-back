@extends('layouts.app')
@section('links')
    <style>
        #menu-list tr {
            cursor: move;
        }

        .submenu-row {
            background-color: #f9f9f9;
        }

        .menu-row {
            background-color: #fff;
        }

        .ui-sortable-helper {
            background-color: #d1ecf1 !important;
        }
    </style>


@endsection
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
                            <th scope="col">Yuqori turkum</th>
                            <th scope="col"></th>

                        </tr>
                        </thead>
                        <tbody >
                        @foreach ($entranceRequirements as $key => $item)
                            <tr>
                                <th scope="row" style="width: 100px">{{ $entranceRequirements->firstItem() + $key }}</th>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="imb-block me-2 rounded-circle overflow-hidden">
                                            <img src="{{ $item->sm_img ?? asset('assets/img/default.png') }}" alt="">
                                        </div>
                                        {{ $item->name[$main_lang->code] ?? '--'}}
                                    </div>
                                </td>
                                <td>{{ $item->educationalProgram ? $item->educationalProgram->name[$main_lang->code] : 'null' }}</td>

                                <td style="width: 200px">
                                    <div class="d-flex justify-content-end">
                                        <a href="{{ route($route_name.'.edit',$item->id) }}" class="btn btn-sm btn-info"><i class="fe fe-edit-2"></i></a>
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
                    {{ $entranceRequirements->links() }}
                </div>
            </div>
        </div>
    </div>

@endsection
@section('scripts')

@endsection
