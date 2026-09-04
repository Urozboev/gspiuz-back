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
        'name' => 'Qoʻshish',
        'disabled' => true
        ],
        ]
        ])
    </div>
</div> <!-- / .header -->

<!-- CARDS -->
<div class="container-fluid">
    <div class="row">
        <div class="col-8">
            <div class="card mw-50">
                <div class="card-body">
                    <form method="post" action="{{ route($route_name . '.store') }}" enctype="multipart/form-data" id="add">
                        @csrf
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
                                            <label for="title" class="form-label required">Sarlavha</label>
                                            <input type="text" required class="form-control @error('title.'.$lang->code) is-invalid @enderror" name="title[{{ $lang->code }}]" value="{{ old('title.'.$lang->code) }}" id="title" placeholder="Sarlavha...">
                                            @error('title.'.$lang->code )
                                            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label for="desc" class="form-label">Tavsif</label>
                                            <textarea name="dec[{{ $lang->code }}]" id="desc" cols="30" rows="10" class="form-control @error('dec.'.$lang->code) is-invalid @enderror ckeditor" name="desc[{{ $lang->code }}]" placeholder="Tavsif...">{{ old('desc.'.$lang->code) }}</textarea>
                                            @error('desc.'.$lang->code)
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                <div class="form-group">
                                    <label for="employs" class="form-label">Taʼlim dasturi</label>
                                    <select class="form-control mb-4 @error('employs') is-invalid @enderror" data-choices='{"": true}'  name="educational_program_id">
                                        @foreach ($educationalprogram as $key => $item)
                                            <option value="{{ $item->id }}" {{ (old('educational_program_id') ? in_array($item->id, old('educational_program_id')) : '') ? 'selected' : '' }}>{{ $item->name[$main_lang->code] }}</option>
                                        @endforeach
                                    </select>
                                    @error('employs')
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
            <div class="card mw-50">
                <div class="card-body">
                    @csrf
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <!-- Dropzone -->
                                <label for="dropzone" class="form-label">Logotip</label>
                                <div class="dropzone dropzone-multiple" id="dropzone"></div>
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
