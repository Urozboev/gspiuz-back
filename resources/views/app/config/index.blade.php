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
                        Config
                    </h1>

                </div>
            </div> <!-- / .row -->
        </div> <!-- / .header-body -->
        @include('app.components.breadcrumb', [
        'datas' => [
        [
        'active' => false,
        'url' => '',
        'name' => 'Config',
        'disabled' => true
        ]
        ]
        ])
    </div>
</div> <!-- / .header -->

<!-- CARDS -->
<div class="container-fluid">
    <form method="post" action="{{ route('config.update') }}">
        @csrf
        <div class="row">
            <div class="col-4">
                <div class="card mw-50">
                    <div class="card-header">
                        <h2 class="mb-0">Items Groups</h2>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                @foreach($config_groups as $item)
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" name="config_groups[{{ $item->id }}]" type="checkbox" {{ $item->is_active == 1 ? 'checked' : '' }} id="flexSwitchCheckDefault">
                                    <label class="form-check-label" for="flexSwitchCheckDefault">{{ $item->title }}</label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-8">
                <div class="card mw-50">
                    <div class="card-header">
                        <h2 class="mb-0">Items</h2>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                @foreach($config as $item)
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" name="config[{{ $item->id }}]" type="checkbox" {{ $item->is_active == 1 ? 'checked' : '' }} id="flexSwitchCheckDefault">
                                    <label class="form-check-label" for="flexSwitchCheckDefault">{{ $item->title }}</label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        <!-- Button -->
                        <div class="model-btns d-flex justify-content-end">
                            <!-- <a href="{{ route('posts_categories.index') }}" type="button" class="btn btn-secondary">Bekor qilish</a> -->
                            <button type="submit" class="btn btn-primary ms-2">Saqlash</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection