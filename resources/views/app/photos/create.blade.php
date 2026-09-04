@extends('layouts.app')

@section('content')

    <h1 class="text-uppercase mt-5">Rasmlar</h1>

    <div class="headcrumbs d-flex mb-3">
        <a href="{{ route('photos.index') }}" class="d-flex text-uppercase me-2" style="opacity:25%">Rasmlar</a> >> <a class="d-flex text-uppercase ms-2">create</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('photos.store') }}" method="post" enctype='multipart/form-data'>
        @csrf
        <div class="row">
            <div class="col-12 mb-4">
                <div class="card border-0 shadow components-section">
                    <div class="card-body">
                        <nav>
                            <div class="nav nav-tabs border-bottom mb-4" id="nav-tab" role="tablist">
                                @foreach($languages as $language)
                                    <button class="nav-link border-bottom" id="{{ $language->lang }}-tab" data-bs-toggle="tab" data-bs-target="#{{ $language->lang }}" type="button" role="tab" aria-controls="{{ $language->lang }}" aria-selected="true">{{ $language->lang }}</button>
                                @endforeach
                            </div>
                        </nav>
                        <div class="tab-content" id="nav-tabContent">
                            @foreach($languages as $language)
                                <div class="tab-pane fade" id="{{ $language->lang }}" role="tabpanel" aria-labelledby="{{ $language->lang }}-tab">
                                    <div class="row mb-4">
                                        <div class="col-lg-6 col-sm-6">
                                            <!-- Form -->
                                            <div class="my-4">
                                                <label for="textarea">Tavsif</label>
                                                <textarea class="form-control" placeholder="Tavsifni kiriting..." id="textarea" rows="4" name="desc[{{ $language->small }}]">{{ old('desc.'.$language->small) }}</textarea>
                                            </div>
                                            <!-- End of Form -->
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="row mb-4">
                            <div class="col-lg-6 col-sm-6">
                                <div class="mb-3">
                                    <label for="formFile" class="form-label">Rasm</label>
                                    <input class="form-control" type="file" name="img">
                                </div>
                            </div>
                        </div>
                        <button class="btn btn-success px-5 text-white" type="submit" style="padding:12px">Saqlash</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

@endsection
