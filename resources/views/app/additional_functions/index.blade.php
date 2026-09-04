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
        'disabled' => true
        ],
        ]
        ])
    </div>
</div> <!-- / .header -->

<!-- CARDS -->
<div class="container-fluid">
    <form method="post" action="{{ route($route_name . '.store') }}" enctype="multipart/form-data" id="add">
        <div class="row">
            <div class="col-8">
                <div class="card mw-50">
                    <div class="card-body">
                        <form method="post" action="{{ route($route_name . '.store') }}" enctype="multipart/form-data" id="add">
                            @csrf
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label for="google_index" class="form-label">Google indeksatsiya</label>
                                        <textarea name="google_index" id="google_index" cols="4" rows="4" class="form-control @error('google_index') is-invalid @enderror" name="google_index">{{ old('google_index') ?? $additional_function->google_index ?? null }}</textarea>
                                        @error('google_index')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="yandex_index" class="form-label">Yandex indeksatsiya</label>
                                        <textarea name="yandex_index" id="yandex_index" cols="4" rows="4" class="form-control @error('yandex_index') is-invalid @enderror" name="yandex_index">{{ old('yandex_index') ?? $additional_function->yandex_index ?? null }}</textarea>
                                        @error('yandex_index')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="google_analytics" class="form-label">Google Analytics</label>
                                        <textarea name="google_analytics" id="google_analytics" cols="4" rows="4" class="form-control @error('google_analytics') is-invalid @enderror" name="google_analytics">{{ old('google_analytics') ?? $additional_function->google_analytics ?? null }}</textarea>
                                        @error('google_analytics')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="yandex_metrika" class="form-label">Yandex Metrika</label>
                                        <textarea name="yandex_metrika" id="yandex_metrika" cols="4" rows="4" class="form-control @error('yandex_metrika') is-invalid @enderror" name="yandex_metrika">{{ old('yandex_metrika') ?? $additional_function->yandex_metrika ?? null }}</textarea>
                                        @error('yandex_metrika')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="livechat" class="form-label">Livechat</label>
                                        <textarea name="livechat" id="livechat" cols="4" rows="4" class="form-control @error('livechat') is-invalid @enderror" name="livechat">{{ old('livechat') ?? $additional_function->livechat ?? null }}</textarea>
                                        @error('livechat')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <!-- Button -->
                            <div class="model-btns d-flex justify-content-end">
                                <a href="{{ route('posts_categories.index') }}" type="button" class="btn btn-secondary">Bekor qilish</a>
                                <button type="submit" class="btn btn-primary ms-2">Saqlash</button>
                            </div>
                    </div>
                </div>
            </div>
            <div class="col-4">
                <div class="card mw-50">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="telegram_bot_token" class="form-label">Telegram bot tokeni</label>
                                    <input type="text" class="form-control @error('telegram_bot_token') is-invalid @enderror" name="telegram_bot_token" value="{{ old('telegram_bot_token') ?? $additional_function->telegram_bot_token ?? '' }}" id="telegram_bot_token" placeholder="token...">
                                    @error('telegram_bot_token')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="telegram_group_id" class="form-label">ID telegram guruh/kanali</label>
                                    <input type="text" class="form-control @error('telegram_group_id') is-invalid @enderror" name="telegram_group_id" value="{{ old('telegram_group_id') ?? $additional_function->telegram_group_id ?? '' }}" id="telegram_group_id" placeholder="ID...">
                                    @error('telegram_group_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection