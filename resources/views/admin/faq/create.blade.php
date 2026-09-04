@extends('layouts.app')

@section('links')

<script>
    window.onload = function() {
        var add_post = new Dropzone("div#dropzone", {
            url: "{{ url('/admin/upload_from_dropzone') }}",
            paramName: "file",
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            addRemoveLinks: true,
            maxFiles: 1,
            maxFilesize: 15, // MB
            success: (file, response) => {
                let input = document.createElement('input');
                input.setAttribute('type', 'hidden');
                input.setAttribute('value', response.file_name);
                input.setAttribute('name', 'dropzone_images');

                let form = document.getElementById('add');
                form.append(input);
                console.log(response);
            },
            removedfile: function(file) {
                file.previewElement.remove();
                if (file.xhr) {
                    let data = JSON.parse(file.xhr.response);
                    let removing_img = document.querySelector('[value="' + data.file_name + '"]');
                    removing_img.remove();
                } else {
                    let data = file.name.split('/')[file.name.split('/').length - 1]
                    let removing_img = document.querySelector('[value="' + data + '"]');
                    removing_img.remove();
                }
            },
            error: function(file, message) {
                alert(message);
                this.removeFile(file);
            },

            // change default texts
            dictDefaultMessage: "Peretashchite syuda file for download",
            dictRemoveFile: "Delete file",
            dictCancelUpload: "Otmenit download",
            dictMaxFilesExceeded: "Ne mojete zagrujat bolshe"
        });
    };
</script>

@endsection

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
        'name' => 'Add',
        'disabled' => true
        ],
        ]
        ])
    </div>
</div> <!-- / .header -->

<div class="container-fluid">
    <form method="post" action="{{ route('education_faqs.store') }}" enctype="multipart/form-data" id="add">
        @csrf
        <input type="hidden" name="educational_program_id" value="{{ $id }}">
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
                                                <label for="question{{ $lang->code }}" class="form-label {{ $lang->code == $main_lang->code ? 'required' : '' }}">Savollar</label>
                                                <input type="text" {{ $lang->code == $main_lang->code ? 'required' : '' }} class="form-control @error('question.'.$lang->code) is-invalid @enderror" name="question[{{ $lang->code }}]" value="{{ old('question.'.$lang->code) }}" id="question{{ $lang->code }}" placeholder="Savol...">
                                                @error('question.'.$lang->code)
                                                <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <label for="answer{{ $lang->code }}" class="form-label {{ $lang->code == $main_lang->code ? 'required' : '' }}">Javob</label>
                                                <textarea id="answer{{ $lang->code }}" cols="30" rows="10" class="form-control @error('answer.'.$lang->code) is-invalid @enderror ckeditor" name="answer[{{ $lang->code }}]" placeholder="Javob...">{{ old('answer.'.$lang->code) }}</textarea>
                                                @error('answer.'.$lang->code)
                                                <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                                @enderror
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="form-group">
                                    <label for="parent_id" class="form-label">Yuqori turkum</label>
                                    <select class="form-select @error('parent_id') is-invalid @enderror" id="parent_id" data-choices='{"": true}' name="parent_id">
                                        <option value="{{null}}"> Asosiy</option>
                                        @foreach ($faqs as $key => $item)
                                            <option value="{{ $item->id }}" {{ old('parent_id') == $item->id ? 'selected' : '' }}>{{ $item->question[$main_lang->code] ?? null}}</option>
                                        @endforeach
                                    </select>
                                    @error('parent_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-auto">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input"
                                                       type="radio"
                                                       name="action"
                                                       id="switchTwo"
                                                       value="1">
                                                <label class="form-check-label" for="switchTwo"></label>
                                            </div>
                                        </div>
                                        <div class="col ms-n2">
                                            <small class="text-muted">
                                                Action
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="employs" class="form-label">Koʻnikma</label>
                                    <select class="form-control mb-4 @error('skill_id') is-invalid @enderror" data-choices='{"1": true}'  name="skill_id" required>
                                        @foreach ($skills as $key => $item)
                                            <option value="{{ $item->id }}" {{ (old('skill_id') ?  : '') ? 'selected' : '' }}>{{ $item->name[$main_lang->code] }}</option>
                                        @endforeach
                                    </select>
                                    @error('employs')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                <!-- CARDS -->
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
