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
                    <a href="{{ route('products.create') }}" class="btn btn-primary lift">
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
    <div class="card mt-4">
        <div class="card-body">
            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Nomi</th>
                            <th scope="col">Brend</th>
                            <th scope="col">Narx, Narx (chegirmali)</th>
                            <th scope="col">KOʻRISHLAR SONI</th>
                            <th scope="col"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $key => $item)
                        <tr>
                            <th scope="row" style="width: 100px">{{ $products->firstItem() + $key }}</th>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="imb-block me-2 overflow-hidden">
                                        <img src="{{ isset($item->productImages[0]) ? $item->productImages[0]->sm_img : asset('assets/img/default.png') }}" alt="">
                                    </div>
                                    {{ $item->title[$main_lang->code] }}
                                </div>
                            </td>
                            <td>{{ isset($item->brand) ? $item->brand->title : '--' }}</td>
                            <td>{{ $item->price ?? '--' }} @if($item->discount_price), <span class="text-danger">{{ $item->discount_price }} @endif</span></td>
                            <td>{{ $item->views_count }}</td>
                            <td style="width: 200px">
                                <div class="d-flex justify-content-end">
                                    <a href="{{ route('products.edit', [$route_parameter => $item]) }}" class="btn btn-sm btn-info"><i class="fe fe-edit-2"></i></a>
                                    <a class="btn btn-sm btn-danger ms-3" onclick="var result = confirm('Want to delete?');if (result){event.preventDefault();document.getElementById('delete-form{{ $item->id }}').submit();}"><i class="fe fe-trash"></i></a>
                                    <form action="{{ route($route_name.'.destroy', [$route_parameter => $item]) }}" id="delete-form{{ $item->id }}" method="POST" style="display: none;">
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
                {{ $products->links() }}
            </div>
        </div>
    </div>
</div>

@endsection