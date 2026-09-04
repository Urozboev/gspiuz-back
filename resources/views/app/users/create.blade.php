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
            </div> <!-- / .row -->
        </div> <!-- / .header-body -->
        @include('app.components.breadcrumb', [
        'datas' => [
        [
        'active' => false,
        'url' => route($route_name.'.index'),
        'name' => $title,
        'disabled' => false
        ],
        [
        'active' => true,
        'url' => '',
        'name' => 'Qoʻshish',
        'disabled' => true
        ],
        ]
        ])
    </div>
</div> <!-- / .header -->

<!-- CARDS -->
<div class="container-fluid">
    <form method="post" action="{{ route($route_name.'.store') }}" enctype="multipart/form-data" id="add">
        @csrf
        <div class="row">
            <div class="col-8">
                <div class="card mw-50">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="name" class="form-label required">F.I.Sh.</label>
                                    <input type="text" required class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" id="name" placeholder="F.I.Sh....">
                                    @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="username" class="form-label required">Foydalanuvchi nomi</label>
                                    <input type="text" required class="form-control @error('username') is-invalid @enderror" name="username" value="{{ old('username') }}" id="username" placeholder="havola...">
                                    @error('username')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="password" class="form-label required">Parol</label>
                                    <input type="text" required minlength="8" class="form-control @error('password') is-invalid @enderror" name="password" value="{{ old('password') }}" id="password" placeholder="password...">
                                    @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="password_confirmation" class="form-label required">Parolni tasdiqlash</label>
                                    <input type="text" required minlength="8" class="form-control @error('password_confirmation') is-invalid @enderror" name="password_confirmation" value="{{ old('password_confirmation') }}" id="password_confirmation" placeholder="password_confirmation...">
                                    @error('password_confirmation')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <!-- Button -->
                        <div class="model-btns d-flex justify-content-end">
                            <a href="{{ route($route_name.'.index') }}" type="button" class="btn btn-secondary">Bekor qilish</a>
                            <button type="submit" class="btn btn-primary ms-2">Saqlash</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-4">
            </div>
        </div>
    </form>
</div>
@endsection