@extends('layouts.app')

@section('links')
    @include('admin.departaments._dropzone')
@endsection

@section('content')
    <div class="header">
        <div class="container-fluid">
            <div class="header-body">
                <div class="row align-items-end">
                    <div class="col">
                        <h1 class="header-title">Yangi boʻlim</h1>
                        @include('app.components.page-hint')
                    </div>
                </div>
            </div>
            @include('app.components.breadcrumb', [
                'datas' => [
                    ['active' => false, 'url' => route($route_name.'.index'), 'name' => $title, 'disabled' => false],
                    ['active' => true, 'url' => '', 'name' => 'Yangi', 'disabled' => true],
                ]
            ])
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-xl-8">
                <div class="card">
                    <div class="card-body">
                        <form method="post" action="{{ route($route_name . '.store') }}" id="department-form">
                            @csrf

                            @include('admin.departaments._form', ['departament' => null])

                            <div class="alert alert-light mt-3 mb-0">
                                Saqlagandan keyin shu sahifaning oʻzida xodimlarni qoʻshasiz.
                            </div>

                            <div class="d-flex justify-content-end mt-3">
                                <a href="{{ route($route_name.'.index') }}" class="btn btn-secondary">Bekor qilish</a>
                                <button type="submit" class="btn btn-primary ms-2">Saqlash</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
