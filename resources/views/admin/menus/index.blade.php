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
                            <a href="{{ route($route_name.'.detele_file') }}" >
                                {{ $title }}
                            </a>


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
                            <th scope="col">Ichki menyu</th>
                            <th scope="col"></th>
                            <th scope="col"></th>
                        </tr>
                        </thead>
                        <tbody id="menu-list">
                        @foreach ($menus as $menuItem)
                            {{-- Main Menu --}}
                            <tr>
                                <td>{{ $menuItem['menu']->id }}</td>
                                <td><strong>{{ $menuItem['menu']->title[$languages->first()->code] }}</strong></td>
                                <td>Asosiy menyu</td>
                                <td>
                                    <input
                                            type="number"
                                            class="form-control form-control-sm order-input"
                                            value="{{ $menuItem['menu']->order }}"
                                            data-id="{{ $menuItem['menu']->id }}"
                                            style="width: 80px;">
                                </td>
                                <td>
                                    <a href="{{ route($route_name.'.edit', [$route_parameter => $menuItem['menu']]) }}" class="btn btn-sm btn-info">
                                        <i class="fe fe-edit-2"></i>
                                    </a>
                                    <a href="#" class="btn btn-sm btn-danger"
                                       onclick="if(confirm('Are you sure?')) { event.preventDefault(); document.getElementById('delete-form-{{ $menuItem['menu']->id }}').submit(); }">
                                        <i class="fe fe-trash"></i>
                                    </a>
                                    <form id="delete-form-{{ $menuItem['menu']->id }}" action="{{ route($route_name.'.destroy', [$route_parameter => $menuItem['menu']]) }}" method="POST" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>

                            {{-- Sub-menus --}}
                            @if (!empty($menuItem['children']))
                                @foreach ($menuItem['children'] as $child)
                                    <tr>
                                        <td>{{ $child->id }}</td>
                                        <td>&mdash; {{ $child->title[$languages->first()->code] }}</td>
                                        <td>{{ $menuItem['menu']->title[$languages->first()->code] }}</td>
                                        <td>
                                            <input
                                                    type="number"
                                                    class="form-control form-control-sm order-input"
                                                    value="{{ $child->order }}"
                                                    data-id="{{ $child->id }}"
                                                    style="width: 80px;">
                                        </td>
                                        <td>
                                            <a href="{{ route($route_name.'.edit', [$route_parameter => $child]) }}" class="btn btn-sm btn-info">
                                                <i class="fe fe-edit-2"></i>
                                            </a>
                                            <a href="#" class="btn btn-sm btn-danger"
                                               onclick="if(confirm('Are you sure?')) { event.preventDefault(); document.getElementById('delete-form-{{ $child->id }}').submit(); }">
                                                <i class="fe fe-trash"></i>
                                            </a>
                                            <form id="delete-form-{{ $child->id }}" action="{{ route($route_name.'.destroy', [$route_parameter => $child]) }}" method="POST" style="display: none;">
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
{{--    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>--}}
{{--    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>--}}

    <script>
        $(document).ready(function () {
            // Drag-and-drop faollashtirish
            $("#menu-list").sortable({
                placeholder: "ui-state-highlight",
                update: function (event, ui) {
                    let order = [];
                    $("#menu-list tr").each(function (index, element) {
                        let id = $(element).data("id");
                        if (id) {
                            order.push({ id: id, order: index + 1 });
                        }
                    });

                    // Orderni serverga jo'natish
                    $.ajax({
                        url: "{{ route('menu.updateOrder') }}",
                        method: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            order: order,
                        },
                        success: function (response) {
                            alert("Order updated successfully!");
                        },
                        error: function (xhr) {
                            alert("Failed to update order. Please try again.");
                        },
                    });
                },
            });

            // Orderni qo‘lda o‘zgartirish
            $(".order-input").on("change", function () {
                let id = $(this).data("id");
                let order = $(this).val();

                $.ajax({
                    url: "{{ route('menu.updateOrder') }}",
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        order: [{ id: id, order: order }],
                    },
                    success: function (response) {
                        alert("Order updated successfully!");
                    },
                    error: function (xhr) {
                        alert("Failed to update order. Please try again.");
                    },
                });
            });
        });
    </script>

@endsection
