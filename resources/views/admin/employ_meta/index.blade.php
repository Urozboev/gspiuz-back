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
                            <th scope="col">Xodim</th>
                            <th scope="col">Boʻlim</th>
                            <th scope="col">Lavozim</th>
                            <th scope="col">Shtat turi</th>
                            <th scope="col">Employ form</th>
                            <th scope="col">Employ_staff</th>
                            <th scope="col">Faol</th>
                            <th scope="col"></th>

                        </tr>
                        </thead>
                        <tbody >
                        @foreach ($employ_meta as $employ_meta_item)
                            {{-- Glavni menyu --}}
                            <tr>
                                <td>{{ $employ_meta_item->id }}</td>
                                <td>{{ $employ_meta_item->employ ? $employ_meta_item->employ->first_name[$main_lang->code] : 'Main' }} {{ $employ_meta_item->employ ? $employ_meta_item->employ->last_name[$main_lang->code] : 'Main' }}</td>
                                <td>{{ $employ_meta_item->department ? $employ_meta_item->department->name[$main_lang->code] : 'Main' }}</td>
                                <td>{{ $employ_meta_item->position ? $employ_meta_item->position->name[$main_lang->code] : 'Main' }}</td>
                                <td>{{ $employ_meta_item->employ_staff ? $employ_meta_item->employ_staff->name[$main_lang->code] : 'Main' }}</td>
                                <td>{{ $employ_meta_item->employ_form ? $employ_meta_item->employ_form->name[$main_lang->code] : 'Main' }}</td>
                                <td>{{ $employ_meta_item->employ_type ? $employ_meta_item->employ_type->name[$main_lang->code] : 'Main' }}</td>

                                <td>
                                    @if($employ_meta_item->active == 1)
                                        <span style="color: green; font-weight: bold;">Faol</span>
                                    @else
                                        <span style="color: red; font-weight: bold;">Nofaol</span>
                                    @endif
                                </td>

                                <td>
                                    <a href="{{ route('employ_meta.edit',$employ_meta_item->id) }}" class="btn btn-sm btn-info">
                                        <i class="fe fe-edit-2"></i>
                                    </a>
                                    <a href="#" class="btn btn-sm btn-danger"
                                       onclick="if(confirm('Are you sure?')) { event.preventDefault(); document.getElementById('delete-form-{{ $employ_meta_item->id }}').submit(); }">
                                        <i class="fe fe-trash"></i>
                                    </a>
                                    <form id="delete-form-{{ $employ_meta_item->id }}" action="{{ route($route_name.'.destroy', $employ_meta_item->id) }}" method="POST" style="display: none;">
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
                    {{ $employ_meta->links() }}
                </div>
            </div>
        </div>
    </div>

@endsection
@section('scripts')

@endsection