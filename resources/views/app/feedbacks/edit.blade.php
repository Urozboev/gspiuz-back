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
        'name' => 'Tahrirlash',
        'disabled' => true
        ],
        ]
        ])
    </div>
</div> <!-- / .header -->

<!-- CARDS -->
<div class="container-fluid">
    <form method="post" action="{{ route($route_name . '.update', [$route_parameter => $feedback]) }}" enctype="multipart/form-data" id="add" onsubmit="event.preventDefault(); validateForm()">
        @csrf
        @method('put')
        <div class="row">
            <div class="col-8">
                <div class="card mw-50">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <ul class="nav nav-tabs" id="myTab" role="tablist">
                                    @foreach($langs as $lang)
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link {{ $loop->first ? 'active' : '' }}" id="{{ $lang->code }}-tab" data-bs-toggle="tab" data-bs-target="#{{ $lang->code }}" type="button" role="tab" aria-controls="{{ $lang->code }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}">{{ $lang->title }}</button>
                                    </li>
                                    @endforeach
                                </ul>
                                <div class="tab-content" id="myTabContent">
                                    @foreach($langs as $lang)
                                    <div class="tab-pane mt-3 fade {{ $loop->first ? 'show active' : '' }}" id="{{ $lang->code }}" role="tabpanel" aria-labelledby="{{ $lang->code }}-tab">
                                        <div class="form-group">
                                            <label for="name.{{ $lang->code }}" class="form-label {{ $lang->code == $main_lang->code ? 'required' : '' }}">F.I.Sh.</label>
                                            <input type="text" {{ $lang->code == $main_lang->code ? 'required' : '' }} class="form-control @error('name.'.$lang->code) is-invalid @enderror" name="name[{{ $lang->code }}]" value="{{ old('name.'.$lang->code) ?? $feedback->name[$lang->code] ?? null }}" id="name.{{ $lang->code }}" placeholder="F.I.Sh....">
                                            @error('name.'.$lang->code)
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                        <div class="form-group">
                                            <label for="feedback.{{ $lang->code }}" class="form-label {{ $lang->code == $main_lang->code ? 'required' : '' }}">Fikr</label>
                                            <textarea name="feedback[{{ $lang->code }}]" id="feedback_{{ $lang->code }}" cols="30" rows="10" class="form-control @error('feedback.'.$lang->code) is-invalid @enderror ckeditor" name="feedback[{{ $lang->code }}]" placeholder="Fikr..." {{ $lang->code == $main_lang->code ? 'required' : '' }}>{{ old('feedback.'.$lang->code) ?? $feedback->feedback[$lang->code] ?? null }}</textarea>
                                            @error('feedback.'.$lang->code)
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                        <div class="form-group">
                                            <label for="position" class="form-label">Oʻrni</label>
                                            <input type="text" class="form-control @error('position.'.$lang->code) is-invalid @enderror" name="position[{{ $lang->code }}]" value="{{ old('position.'.$lang->code) ?? $feedback->position[$lang->code] ?? null }}" id="position" placeholder="Oʻrni...">
                                            @error('position.'.$lang->code)
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                <div class="form-group">
                                    <label for="img" class="form-label">Rasm</label>
                                    <input type="file" class="form-control @error('img') is-invalid @enderror" name="img" id="img">
                                    @error('img')
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
                        @csrf
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="video" class="form-label">Video fikr (Fayl)</label>
                                    <input type="file" class="form-control @error('video') is-invalid @enderror" name="video" id="video">
                                    @error('video')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="youtube_link" class="form-label">Video havolasi Youtube</label>
                                    <input type="text" class="form-control @error('youtube_link') is-invalid @enderror" name="youtube_link" value="{{ old('youtube_link') ?? $feedback->youtube_link }}" id="youtube_link" placeholder="havola...">
                                    @error('youtube_link')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="link" class="form-label">Asl nusxa havolasi</label>
                                    <input type="text" class="form-control @error('link') is-invalid @enderror" name="link" value="{{ old('link') ?? $feedback->link }}" id="link" placeholder="havola...">
                                    @error('link')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="logo" class="form-label">Tashkilot logotipi</label>
                                    <input type="file" class="form-control @error('logo') is-invalid @enderror" name="logo" id="logo">
                                    @error('logo')
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

@section('scripts')

<script>
    function validateForm() {
        var feedback = CKEDITOR.instances.feedback_{{ $main_lang -> code}}.getData();

        if (feedback == '') {

            alert('Fikr maydonini toʻldirish shart');

            return false;

        }

        document.getElementById('add').submit();
    }
</script>

@endsection