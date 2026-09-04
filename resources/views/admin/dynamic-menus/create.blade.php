@extends('layouts.app')

@section('links')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>


    <!-- Add this in your <head> -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/css/select2.min.css" rel="stylesheet" />

    <!-- Add this before the closing </body> tag -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/js/select2.min.js"></script>


    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script>
        window.onload = function () {
            // Dropzone 1 uchun sozlama
            var add_post_single = new Dropzone("div#dropzone", {
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
                removedfile: function (file) {
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
                error: function (file, message) {
                    alert(message);
                    this.removeFile(file);
                },

                dictDefaultMessage: "Drag files here to upload",
                dictRemoveFile: "Delete file",
                dictCancelUpload: "Cancel download",
                dictMaxFilesExceeded: "Can't load more"
            });

            // Dropzone 2 uchun sozlama
            var add_post_multiple = new Dropzone("div#dropzone-image", {
                url: "{{ url('/admin/upload_from_dropzone') }}",
                paramName: "file",
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                addRemoveLinks: true,
                maxFiles: 20,
                maxFilesize: 10, // MB
                success: (file, response) => {
                    let input = document.createElement('input');
                    input.setAttribute('type', 'hidden');
                    input.setAttribute('value', response.file_name);
                    input.setAttribute('name', 'dropzone_images[]');

                    let form = document.getElementById('add');
                    form.append(input);
                },
                removedfile: function (file) {
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
                error: function (file, message) {
                    alert(message);
                    this.removeFile(file);
                },

                dictDefaultMessage: "Fayllarni shu yerga tashlang",
                dictRemoveFile: "Faylni oʻchirish",
                dictCancelUpload: "Yuklashni bekor qilish",
                dictMaxFilesExceeded: "Bundan ortiq yuklab boʻlmaydi"
            });
        };
    </script>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        .form-grouppp {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 15rem;
            height: 2rem;
            margin-bottom: 20px;
            justify-content: flex-end;
            /* Align elements to the right */
        }

        .form-select {
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .btnn {
            padding: 5px 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .btnn:hover {
            background-color: #0056b3;
        }

        .form-section {
            margin-bottom: 40px;
            padding: 10px;
            position: relative;
        }

        .delete-btn {
            position: absolute;
            top: 30px;
            right: 25px;
            background: red;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .delete-btn:hover {
            background: darkred;
        }

        .select2-container .select2-selection--single {
            height: 38px;
            /* Match the height of your form controls */
        }

        .select2-container .select2-selection__rendered {
            line-height: 38px;
            /* Center the text vertically */
        }
    </style>


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
            'name' => 'Addition',
            'disabled' => true
            ],
            ]
            ])
        </div>
    </div>
    <!-- / .header -->

    <!-- CARDS -->
    <div class="container-fluid">
        <form method="post" action="{{ route($route_name . '.store') }}" enctype="multipart/form-data" id="add">
            <div class="row">
                <div class="col-8">
                    <div class="card mw-50">
                        <div class="card-body">
                            <form method="post" action="{{ route($route_name . '.store') }}" enctype="multipart/form-data"
                                  id="add">
                                @csrf
                                <div class="row">
                                    <div class="col-12">
                                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                                            @foreach($langs as $lang)
                                                <li class="nav-item" role="presentation">
                                                    <button class="nav-link {{ $loop->first ? 'active' : '' }}"
                                                            id="form1{{ $lang->code }}-tab" data-bs-toggle="tab"
                                                            data-bs-target="#{{ $lang->code }}" type="button" role="ttab"
                                                            aria-controls="{{ $lang->code }}"
                                                            aria-selected="{{ $loop->first ? 'true' : 'false' }}">{{ $lang->title
                                                }}</button>
                                                </li>
                                            @endforeach
                                        </ul>
                                        <div class="tab-content" id="myTabContent">
                                            @foreach($langs as $lang)
                                                <div class="tab-pane mt-3 fade {{ $loop->first ? 'show active' : '' }}"
                                                     id="{{ $lang->code }}" role="tabpanel"
                                                     aria-labelledby="{{ $lang->code }}-tab">
                                                    <div class="form-group">
                                                        <label for="title"
                                                               class="form-label {{ $lang->code == $main_lang->code ? 'required' : '' }}">Short
                                                            tile</label>
                                                        <input type="text" {{ $lang->code == $main_lang->code ? 'required' : ''
                                                }} class="form-control @error('short_title.'.$lang->code) is-invalid
                                                @enderror" name="short_title[{{ $lang->code }}]" value="{{
                                                old('short_title.'.$lang->code) }}" id="short_title" placeholder="Qisqa sarlavha...">
                                                        @error('short_title.'.$lang->code)
                                                        <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                        @enderror
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="title"
                                                               class="form-label {{ $lang->code == $main_lang->code ? 'required' : '' }}">Sarlavha</label>
                                                        <input type="text" {{ $lang->code == $main_lang->code ? 'required' : ''
                                                }} class="form-control @error('title.'.$lang->code) is-invalid
                                                @enderror" name="title[{{ $lang->code }}]" value="{{
                                                old('title.'.$lang->code) }}" id="title" placeholder="Sarlavha...">
                                                        @error('title.'.$lang->code)
                                                        <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                        @enderror
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">Sahifa matni</label>
                                                        <textarea name="text[{{ $lang->code }}]"
                                                                  class="form-control ckeditor"
                                                                  rows="10">{{ old('text.'.$lang->code) }}</textarea>
                                                        <small class="form-text text-muted">
                                                            Sahifaning asosiy matni. Word hujjatidan nusxa koʻchirsangiz,
                                                            formatlash va rasmlar saqlanadi.
                                                        </small>
                                                    </div>                                                    <div class="form-group">
                                                        <label for="title"
                                                               class="form-label {{ $lang->code == $main_lang->code ? 'required' : '' }}">File
                                                            {{$lang->code}}</label>
                                                        <input type="file" class="form-control @error('file.'.$lang->code) is-invalid
                                                @enderror" name="file[{{ $lang->code }}]" value="{{
                                                old('file.'.$lang->code) }}" id="file" placeholder="Fayl...">
                                                        @error('file.'.$lang->code)
                                                        <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="form-group">
                                            <!-- Dropzone -->
                                            <label for="dropzone" class="form-label">Rasm</label>
                                            <div class="dropzone dropzone-multiple" id="dropzone"></div>
                                        </div>
                                        <div class="form-group">
                                            <label for="layout" class="form-label">Sahifa koʻrinishi</label>
                                            <select class="form-select" id="layout" name="layout">
                                                <option value="single" {{ old('layout', 'single') === 'single' ? 'selected' : '' }}>
                                                    Bitta sahifa — sarlavha va matn
                                                </option>
                                                <option value="cards" {{ old('layout', '') === 'cards' ? 'selected' : '' }}>
                                                    Kartochkalar — har biri alohida sahifada ochiladi
                                                </option>
                                                <option value="files" {{ old('layout', '') === 'files' ? 'selected' : '' }}>
                                                    Fayllar roʻyxati — yuklab olish uchun
                                                </option>
                                            </select>
                                            <small class="form-text text-muted">
                                                Bu sahifa saytda qanday koʻrinishini belgilaydi.
                                            </small>
                                        </div>
                                        <div class="form-group">
                                            <label for="menu_id" class="form-label">Menyu</label>
                                            <select class="form-select searchable @error('menu_id') is-invalid @enderror"
                                                    id="menu_id" name="menu_id" required>
                                                @foreach ($menus as $key => $item)
                                                    <option value="{{ $item->id }}" {{ old('menu_id')==$item->id ? 'selected' :
                                                '' }}>
                                                        {{ $item->title[$main_lang->code] }}
                                                        @if (in_array(ltrim((string) $item->path, '/'), config('reserved_paths', []), true))
                                                            — {{ $item->path }} (saytda oʻz sahifasi bor)
                                                        @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('menu_id')
                                            <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                        </div>
                    </div>
                    <div id="formContainer"> </div>
                    <div class="form-grouppp">
                        <label for="menu_id" class="form-label">Forma qoʻshish</label>
                        <select id="formSelector"  class="form-select">
                            <option value="1">Form 1</option>
                            <option value="2">Form 2</option>
                            <option value="3">Form 3</option>

                        </select>
                        <a id="addFormBtn" class="btnn">Qoʻshish</a>
                    </div>

                </div>
                <div class="col-3">
                    <div class="card ">
                        <div class="card-body">
                            @csrf
                            <div class="model-btns d-flex justify-content-end">
                                <a href="{{ route('dynamic-menus.index') }}" type="button"
                                   class="btn btn-secondary">Bekor qilish</a>
                                <button type="submit" class="btn btn-primary ms-2">Saqlash</button>
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
        document.getElementById('addFormBtn').addEventListener('click', function () {
            const formContainer = document.getElementById('formContainer');
            const selectedForm = document.getElementById('formSelector').value;

            // Initialize the count for each form type
            const form1Count = document.querySelectorAll('.form-section[data-form-type="1"]').length;
            const form2Count = document.querySelectorAll('.form-section[data-form-type="2"]').length;
            const form3Count = document.querySelectorAll('.form-section[data-form-type="3"]').length;

            // Check if the number of forms for the selected type is below the limit (10)
            if (selectedForm === '1' && form1Count >= 10) {
                alert("Har bir formadan faqat 10 tadan yaratishingiz mumkin.");
                return;
            } else if (selectedForm === '2' && form2Count >= 10) {
                alert("Har bir formadan faqat 10 tadan yaratishingiz mumkin.");
                return;
            } else if (selectedForm === '3' && form3Count >= 10) {
                alert("Har bir formadan faqat 10 tadan yaratishingiz mumkin.");
                return;
            }

            // Create a new form section
            const formSection = document.createElement('div');
            formSection.className = 'form-section';
            formSection.setAttribute('data-form-type', selectedForm); // Set the form type

            const uniqueDropzoneId = `dropzone-${Date.now()}`; // Unique ID for Dropzone

            let formHTML = '';

            if (selectedForm === '1') {
                // Form 1 HTML (same as before)
                formHTML = ` <div class="card mw-50">
                    <div class="card-body">
                        @csrf
                <div class="row">
                    <div class="col-12">
                        <ul class="nav nav-tabs" id="myTab" role="tablist">
@foreach($langs as $lang)
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $loop->first ? 'active' : '' }}"
                                            id="lang-{{ $lang->code }}-tab" data-bs-toggle="tab"
                                            data-bs-target="#lang-{{ $lang->code }}" type="button" role="tab"
                                            aria-controls="lang-{{ $lang->code }}"
                                            aria-selected="{{ $loop->first ? 'true' : 'false' }}">{{ $lang->title }}</button>
                                    </li>
                                    @endforeach
                </ul>
                <div class="tab-content" id="myTabContent">
@foreach($langs as $lang)
                <div class="tab-pane mt-3 fade {{ $loop->first ? 'show active' : '' }}"
                                         id="lang-{{ $lang->code }}" role="tabpanel"
                                         aria-labelledby="lang-{{ $lang->code }}-tab">
                                        <div class="form-group">
                                            <label for="title-{{ $lang->code }}"
                                                   class="form-label {{ $lang->code == $main_lang->code ? 'required' : '' }}">Sarlavha</label>
                                            <input type="text" {{ $lang->code == $main_lang->code ? 'required' : '' }}
                class="form-control @error('title.'.$lang->code) is-invalid @enderror"
                                                   name="formmenu[${form1Count}][title][{{ $lang->code }}]" value="{{ old('title.'.$lang->code) }}"
                                                   id="title-{{ $lang->code }}" placeholder="Sarlavha...">
                                            @error('title.'.$lang->code)
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                </div>
                <div class="form-group">
                    <label for="desc-{{ $lang->code }}" class="form-label">Tavsif</label>
                                            <textarea name="formmenu[${form1Count}][text][{{ $lang->code }}]" id="desc-{{ $lang->code }}"
                                                      class="form-control @error('text.'.$lang->code) is-invalid @enderror" rows="2" placeholder="Tavsif...">{{ old('text.'.$lang->code) }}</textarea>
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
                                        <label for="in_main" class="form-label">Tartib</label>
                                        <input type="number"  class="form-control @error('path') is-invalid @enderror" required name="formmenu[${form1Count}][order]" value="{{ old('path') }}" id="order" placeholder="Tartib...">

                                        @error('in_main')
                <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
                                    </span>
                                        @enderror
                </div>
                                    <input type="hidden" name="formmenu[${form1Count}][type]" value="formmenu">

<div class="form-group">
<!-- Dropzone -->
<label for="${uniqueDropzoneId}" class="form-label">Rasm</label>
    <div class="dropzone dropzone-multiple" id="${uniqueDropzoneId}"></div>

    <!-- Hidden input to store the uploaded image URL or names -->
    <input type="hidden" name="formmenu[${form1Count}][dropzone_images]" id="hiddenInput_${uniqueDropzoneId}">
</div>
                            </div>
                        </div>
                    </div>
                </div>`;  // Your Form 1 HTML here
            } else if (selectedForm === '2') {
                // Form 2 HTML (same as before)
                formHTML = ` <div class="card mw-50">
        <div class="card-body">
            @csrf
                <div class="row">
                    <div class="col-12">
                        <ul class="nav nav-tabs" id="myTab" role="tablist">
@foreach($langs as $lang)
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $loop->first ? 'active' : '' }}"
                                    id="tab-{{ $lang->code }}-tab" data-bs-toggle="tab"
                                    data-bs-target="#tab-{{ $lang->code }}" type="button" role="tab"
                                    aria-controls="tab-{{ $lang->code }}"
                                    aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                                    {{ $lang->title }}
                </button>
            </li>
@endforeach
                </ul>
                <div class="tab-content" id="myTabContent">
@foreach($langs as $lang)
                <div class="tab-pane mt-3 fade {{ $loop->first ? 'show active' : '' }}"
                                id="tab-{{ $lang->code }}" role="tabpanel"
                                aria-labelledby="tab-{{ $lang->code }}-tab">
                                <div class="form-group">
                                    <label for="title" class="form-label {{ $lang->code == $main_lang->code ? 'required' : '' }}">Sarlavha</label>
                                    <input type="text" {{ $lang->code == $main_lang->code ? 'required' : '' }}
                class="form-control @error('title.'.$lang->code) is-invalid @enderror"
                                        name="formmenu2[${form2Count}][title][{{ $lang->code }}]" value="{{ old('title.'.$lang->code) }}"
                                        id="title" placeholder="Sarlavha...">
                                    @error('title.'.$lang->code)
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                </div>
            </div>
@endforeach
                </div>
                                    <input type="hidden" name="formmenu2[${form2Count}][type]" value="formmenu1">

                <div class="form-group">
                    <label for="categories" class="form-label">Turkumlar</label>
                    <select required  id="categories" class="form-control mb-4 @error('categories') is-invalid @enderror"
                            data-choices='{"removeItemButton": true}' multiple name="formmenu2[${form2Count}][categories][]">
                            @foreach ($all_categories as $item)
                <option value="{{ $item->id }}" {{ (old('categories') ? in_array($item->id, old('categories')) : '') ? 'selected' : '' }}>
                                    {{ $item->title[$main_lang->code] }}
                </option>
@endforeach
                </select>
@error('categories')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                </div>
                  <div class="form-group">
                                        <label for="in_main" class="form-label">Tartib</label>
                                        <input type="number" required  class="form-control @error('path') is-invalid @enderror" name="formmenu2[${form2Count}][order]" value="{{ old('path') }}" id="order" placeholder="Tartib...">

                                        @error('in_main')
                <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
                                    </span>

                                        @enderror
                </div>
</div>
</div>
</div>
</div>`;  // Your Form 2 HTML here
            } else if (selectedForm === '3') {
                // Form 3 HTML (same as before)
                formHTML = `<div class="card mw-50">
                    <div class="card-body">
                        @csrf
                <div class="row">
                    <div class="col-12">
                      <ul class="nav nav-tabs" id="myTab" role="tablist">
@foreach($langs as $lang)
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $loop->first ? 'active' : '' }}"
                        id="locale-{{ $lang->code }}-tab" data-bs-toggle="tab"
                        data-bs-target="#locale-{{ $lang->code }}" type="button" role="tab"
                        aria-controls="locale-{{ $lang->code }}"
                        aria-selected="{{ $loop->first ? 'true' : 'false' }}">{{ $lang->title }}</button>
            </li>
        @endforeach
                </ul>
                <div class="tab-content" id="myTabContent">
@foreach($langs as $lang)
                <div class="tab-pane mt-3 fade {{ $loop->first ? 'show active' : '' }}"
                 id="locale-{{ $lang->code }}" role="tabpanel"
                 aria-labelledby="locale-{{ $lang->code }}-tab">
                <div class="form-group">
                    <label for="locale-title-{{ $lang->code }}"
                           class="form-label {{ $lang->code == $main_lang->code ? 'required' : '' }}">Sarlavha</label>
                    <input type="text" {{ $lang->code == $main_lang->code ? 'required' : '' }}
                class="form-control @error('title.'.$lang->code) is-invalid @enderror"
                           name="formmenu3[${form3Count}][title][{{ $lang->code }}]" value="{{ old('title.'.$lang->code) }}"
                           id="locale-title-{{ $lang->code }}" placeholder="Sarlavha...">
                    @error('title.'.$lang->code)
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="locale-desc-{{ $lang->code }}" class="form-label">Tavsif</label>
                    <textarea name="formmenu3[${form3Count}][text][{{ $lang->code }}]" id="locale-desc-{{ $lang->code }}"
                              class="form-control @error('text.'.$lang->code) is-invalid @enderror" rows="2" placeholder="Tavsif...">{{ old('text.'.$lang->code) }}</textarea>
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
                        <label for="menu_id" class="form-label">Lavozim</label>
                        <select name="formmenu3[${form3Count}][position]" class="form-select">
                            <option value="1">right</option>
                            <option value="0">left</option>
                        </select>
                    </div>
  <div class="form-group">
                                        <label for="in_main" class="form-label">Tartib</label>
                                        <input type="hidden" name="formmenu3[${form3Count}][type]" value="formmenu3">
                                        <input type="number"  class="form-control @error('path') is-invalid @enderror" name="formmenu3[${form3Count}][order]" value="{{ old('path') }}" id="order" placeholder="Tartib...">
                                        @error('in_main')
                <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
                                    </span>
                                        @enderror
                </div>
<div class="form-group">
<!-- Dropzone -->
<label for="${uniqueDropzoneId}" class="form-label">Rasm</label>
    <div class="dropzone dropzone-multiple" id="${uniqueDropzoneId}"></div>

    <!-- Hidden input to store the uploaded image URL or names -->
    <input type="hidden" name="formmenu3[${form3Count}][dropzone_images]" id="hiddenInput_${uniqueDropzoneId}">
</div

            </div>
        </div>
    </div>
</div>`;  // Your Form 3 HTML here
            }

            // Add the form to the section
            formSection.innerHTML += formHTML;

            // Add a delete button
            const deleteBtn = document.createElement('button');
            deleteBtn.className = 'delete-btn btn btn-danger mt-3';
            deleteBtn.textContent = 'Delete';
            deleteBtn.type = 'button';
            deleteBtn.addEventListener('click', function () {
                formContainer.removeChild(formSection);
            });
            formSection.appendChild(deleteBtn);

            // Append the section to the container
            formContainer.appendChild(formSection);

            // Initialize CKEditor, Choices.js, and Dropzone after the DOM is updated
            setTimeout(function () {
                // Initialize CKEditor
                // Umumiy sozlama layouts/app.blade.php da: uz tili, rasm yuklash.
                window.initCkeditors(formSection);

                // Initialize Choices.js
                const selects = formSection.querySelectorAll('select[data-choices]');
                selects.forEach(function (select) {
                    new Choices(select, { removeItemButton: true });
                });

                // Initialize Dropzone
                const dropzoneElement = document.getElementById(uniqueDropzoneId);
                new Dropzone(`div#${uniqueDropzoneId}`, {
                    url: "{{ url('/admin/upload_from_dropzone') }}",
                    paramName: "file",
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    addRemoveLinks: true,
                    maxFiles: 20,
                    maxFilesize: 10, // MB
                    success: (file, response) => {
                        // Get the hidden input field for this dropzone
                        let hiddenInput = document.getElementById(`hiddenInput_${uniqueDropzoneId}`);

                        // Parse the current value or initialize an empty array
                        let fileNames = hiddenInput.value ? JSON.parse(hiddenInput.value) : [];

                        // Add the new file name to the array
                        fileNames.push(response.file_name);

                        // Update the hidden input with the updated array
                        hiddenInput.value = JSON.stringify(fileNames);
                    },
                    removedfile: function (file) {
                        // Remove the file name from the hidden input when the file is removed
                        let hiddenInput = document.getElementById(`hiddenInput_${uniqueDropzoneId}`);
                        let fileName = file.name.split('/').pop(); // Extract file name

                        // Parse the current value or initialize an empty array
                        let fileNames = hiddenInput.value ? JSON.parse(hiddenInput.value) : [];

                        // Remove the file name from the array
                        let index = fileNames.indexOf(fileName);
                        if (index !== -1) {
                            fileNames.splice(index, 1); // Remove the file name
                        }

                        // Update the hidden input with the updated array
                        hiddenInput.value = JSON.stringify(fileNames);

                        // Remove the file preview
                        file.previewElement.remove();
                    },
                    error: function (file, message) {
                        alert(message);
                        this.removeFile(file);
                    },
                    dictDefaultMessage: "Drag files here to upload",
                    dictRemoveFile: "Delete file",
                    dictCancelUpload: "Cancel download",
                    dictMaxFilesExceeded: "Can't load more"
                });




                // Now you can access the imageUrls array for further usage, such as submitting them with the form.

            }, 0);
        });

        $(document).ready(function () {
            $('#menu_id').select2({
                placeholder: "Select a menu",
                allowClear: true
            });
        });
    </script>

@endsection
