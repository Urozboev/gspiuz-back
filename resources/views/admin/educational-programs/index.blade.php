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
                            <th scope="col">Faol</th>
                            <th scope="col"></th>

                        </tr>
                        </thead>
                        <tbody >
                        @foreach ($educational_programs as $programItem)
                            <tr>
                                <td>{{ $programItem['menu']->id ?? '-' }}</td>
                                <td><strong>{{ $programItem['menu']->name[$languages->first()->code] ?? 'No Name' }}</strong></td>
                                <td>Parent Educational_program</td>

                                <td>
                                    <a href="{{ route('educational-programs.edit', $programItem['menu']->id) }}" class="btn btn-sm btn-info">
                                        <i class="fe fe-edit-2"></i>
                                    </a>
                                    <a href="#" class="btn btn-sm btn-danger"
                                       onclick="if(confirm('Are you sure?')) { event.preventDefault(); document.getElementById('delete-form-{{ $programItem['menu']->id }}').submit(); }">
                                        <i class="fe fe-trash"></i>
                                    </a>
                                    <form id="delete-form-{{ $programItem['menu']->id }}" action="{{ route('educational-programs.destroy', $programItem['menu']->id) }}" method="POST" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>

                            @if (!empty($programItem['children']))
                                @foreach ($programItem['children'] as $child)
                                    <tr>
                                        <td>{{ $child->id ?? '-' }}</td>
                                        <td>&mdash; {{ $child->name[$languages->first()->code] ?? 'No Name' }}</td>
                                        <td>{{ $programItem['menu']->name[$languages->first()->code] ?? 'No Parent' }}</td>
                                        <td>
                                            <a href="{{ route('education_faqs.index', $child->id) }}" class="btn btn-sm btn-info">
                                                <i class="fe fe-download-cloud"></i>
                                            </a>
                                            <a href="{{ route('educational-programs.edit', $child->id) }}" class="btn btn-sm btn-info">
                                                <i class="fe fe-edit-2"></i>
                                            </a>
                                            <a href="#" class="btn btn-sm btn-danger"
                                               onclick="if(confirm('Are you sure?')) { event.preventDefault(); document.getElementById('delete-form-{{ $child->id }}').submit(); }">
                                                <i class="fe fe-trash"></i>
                                            </a>
                                            <form id="delete-form-{{ $child->id }}" action="{{ route('educational-programs.destroy', $child->id) }}" method="POST" style="display: none;">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $count->links() }}
                </div>
            </div>
        </div>
    </div>

@endsection
@section('scripts')

@endsection
