@extends('layouts.app')

@section('content')

    <div class="header">
        <div class="container-fluid">
            <div class="header-body">
                <h1 class="header-title">{{ $title }}</h1>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <form action="{{ route('page-items.update', [$page->id, $item->id]) }}" method="post" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('admin.page-items._form')
        </form>
    </div>

@endsection
