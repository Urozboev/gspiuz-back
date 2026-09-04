@extends('layouts.app')

@section('content')

    <div class="header">
        <div class="container-fluid">
            <div class="header-body">
                <h1 class="header-title">
                    {{ $title }}
                </h1>

                @include('app.components.page-hint')
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <form action="{{ route($route_name.'.store') }}" method="post" enctype="multipart/form-data">
            @csrf
            @include('admin.popups._form')
        </form>
    </div>

@endsection
