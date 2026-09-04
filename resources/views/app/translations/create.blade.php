@extends('layouts.app')

@section('content')

<h1 class="text-uppercase mt-5">tarjimalar</h1>

<div class="headcrumbs d-flex mb-3">
  <a href="{{ route('translations.index') }}" class="d-flex text-uppercase me-2" style="opacity:25%">tarjimalar</a> >> <a class="d-flex text-uppercase ms-2">create</a>
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

<form action="{{ route('translations.store') }}" method="post">
  @csrf
  <div class="row">
    <div class="col-12 mb-4">
        <div class="card border-0 shadow components-section">
            <div class="card-body">
              <div class="row mb-4">
                  <div class="col-lg-6 col-sm-6">
                      <!-- Form -->
                      <div class="mb-4">
                          <label>Kalit</label>
                          <input type="text" class="form-control" name="key" placeholder="key" value="{{ old('key') }}">
                      </div>
                      <!-- End of Form -->
                  </div>
              </div>
              <nav>
                <div class="nav nav-tabs border-bottom mb-3" id="nav-tab" role="tablist">
                  @foreach($languages as $language)
                  <button class="nav-link border-bottom" id="{{ $language->lang }}-tab" data-bs-toggle="tab" data-bs-target="#{{ $language->lang }}" type="button" role="tab" aria-controls="{{ $language->lang }}" aria-selected="true">{{ $language->lang }}</button>
                  @endforeach
                  <!-- <button class="nav-link border-bottom" id="nav-profile-tab" data-bs-toggle="tab" data-bs-target="#nav-profile" type="button" role="tab" aria-controls="nav-profile" aria-selected="false">English</button> -->
                </div>
              </nav>
              <div class="tab-content" id="nav-tabContent">
                @foreach($languages as $language)
                <div class="tab-pane fade " id="{{ $language->lang }}" role="tabpanel" aria-labelledby="{{ $language->lang }}-tab">
                  <div class="row mb-4">
                      <div class="col-lg-6 col-sm-6">
                          <!-- Form -->
                          <div class="my-4">
                              <label for="textarea">Qiymat</label>
                              <textarea class="form-control" placeholder="Enter your sentence..." id="textarea" rows="4" name="val[{{ $language->small }}]">{{ old('val.'.$language->small) }}</textarea>
                          </div>
                          <!-- End of Form -->
                      </div>
                  </div>
                </div>
                @endforeach
                <!-- <div class="tab-pane fade" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab">
                  <div class="row mb-4">
                      <div class="col-lg-6 col-sm-6">
                          <div class="my-4">
                              <label for="textarea">Javob</label>
                              <textarea class="form-control" placeholder="Enter your answer..." id="textarea" rows="4" name="answer_2">{{ old('answer_2') }}</textarea>
                          </div>
                      </div>
                  </div>
                </div> -->
              </div>
              <button class="btn btn-success px-5 text-white" type="submit" style="padding:12px">Saqlash</button>
            </div>
        </div>
    </div>
  </div>
</form>

@endsection
