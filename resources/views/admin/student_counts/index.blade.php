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
                            <th scope="col">Yillar</th>
                            <th scope="col">Erkak</th>
                            <th scope="col">Famel</th>
                            <th scope="col"></th>

                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($employ_staff as $employ_staff_item)
                            {{-- Glavni menyu --}}
                            <tr>
                                <td>{{ $employ_staff_item->id }}</td>
                                <td><strong>{{ $employ_staff_item->years }}</strong></td>
                                <td><strong>{{ $employ_staff_item->male }}</strong></td>
                                <td><strong>{{ $employ_staff_item->female }}</strong></td>

                                <td>
                                    <a href="{{ route($route_name.'.edit',$employ_staff_item->id) }}" class="btn btn-sm btn-info">
                                        <i class="fe fe-edit-2"></i>
                                    </a>
                                    <a href="#" class="btn btn-sm btn-danger"
                                       onclick="if(confirm('Are you sure?')) { event.preventDefault(); document.getElementById('delete-form-{{ $employ_staff_item->id }}').submit(); }">
                                        <i class="fe fe-trash"></i>
                                    </a>
                                    <form id="delete-form-{{ $employ_staff_item->id }}" action="{{ route($route_name.'.destroy', $employ_staff_item->id) }}" method="POST" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>

                            {{-- Sub-menyular --}}
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $employ_staff->links() }}
                </div>
            </div>
        </div>
    </div>

@endsection
@section('scripts')

@endsection
