@extends('layouts.app')

@section('links')

    <script>
        window.onload = function() {
            var add_post = new Dropzone("div#dropzone_logo", {
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
                    input.setAttribute('name', 'logo[]');

                    let form = document.getElementById('add');
                    form.append(input);
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
                dictDefaultMessage: "Fayllarni shu yerga tashlang",
                dictRemoveFile: "Faylni oʻchirish",
                dictCancelUpload: "Yuklashni bekor qilish",
                dictMaxFilesExceeded: "Bundan ortiq yuklab boʻlmaydi",

                @if(old('logo'))
                init: function() {
                    var thisDropzone = this;

                    // document.querySelector('.dropzone').classList.add('dz-max-files-reached');

                    @foreach(old('logo') as $img)

                    var input = document.createElement('input');
                    input.setAttribute('type', 'hidden');
                    input.setAttribute('value', '{{ $img }}');
                    input.setAttribute('name', 'logo[]');

                    var form = document.getElementById('add');
                    form.append(input);

                    var mockFile = {
                        name: '{{ $img }}',
                        size: 1024 * 512,
                        accepted: true
                    };

                    thisDropzone.options.addedfile.call(thisDropzone, mockFile);
                    thisDropzone.options.thumbnail.call(thisDropzone, mockFile, '/upload/images/{{ $img }}');
                    thisDropzone.files.push(mockFile)

                    @endforeach
                }
                @elseif(isset($site_info -> logo))
                init: function() {
                    var thisDropzone = this;

                    var input = document.createElement('input');
                    input.setAttribute('type', 'hidden');
                    input.setAttribute('value', '{{ $site_info->logo }}');
                    input.setAttribute('name', 'logo[]');

                    var form = document.getElementById('add');
                    form.append(input);

                    var mockFile = {
                        name: '{{ $site_info->logo }}',
                        size: 1024 * 512,
                        accepted: true
                    };

                    thisDropzone.options.addedfile.call(thisDropzone, mockFile);
                    thisDropzone.options.thumbnail.call(thisDropzone, mockFile, '/upload/images/{{ $site_info->logo }}');
                    thisDropzone.files.push(mockFile)
                }
                @endif
            });

            var add_post = new Dropzone("div#dropzone_logo_dark", {
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
                    input.setAttribute('name', 'logo_dark[]');

                    let form = document.getElementById('add');
                    form.append(input);
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
                dictDefaultMessage: "Drag files here to upload",
                dictRemoveFile: "Delete file",
                dictCancelUpload: "Cancel download",
                dictMaxFilesExceeded: "Can't load more",

                @if(old('dropzone_images'))
                init: function() {
                    var thisDropzone = this;

                    // document.querySelector('.dropzone').classList.add('dz-max-files-reached');

                    @foreach(old('dropzone_images') as $img)

                    var input = document.createElement('input');
                    input.setAttribute('type', 'hidden');
                    input.setAttribute('value', '{{ $img }}');
                    input.setAttribute('name', 'logo_dark[]');

                    var form = document.getElementById('add');
                    form.append(input);

                    var mockFile = {
                        name: '{{ $img }}',
                        size: 1024 * 512,
                        accepted: true
                    };

                    thisDropzone.options.addedfile.call(thisDropzone, mockFile);
                    thisDropzone.options.thumbnail.call(thisDropzone, mockFile, '/upload/images/{{ $img }}');
                    thisDropzone.files.push(mockFile)

                    @endforeach
                }
                @elseif(isset($site_info -> logo_dark))
                init: function() {
                    var thisDropzone = this;

                    var input = document.createElement('input');
                    input.setAttribute('type', 'hidden');
                    input.setAttribute('value', '{{ $site_info->logo_dark }}');
                    input.setAttribute('name', 'logo_dark[]');

                    var form = document.getElementById('add');
                    form.append(input);

                    var mockFile = {
                        name: '{{ $site_info->logo_dark }}',
                        size: 1024 * 512,
                        accepted: true
                    };

                    thisDropzone.options.addedfile.call(thisDropzone, mockFile);
                    thisDropzone.options.thumbnail.call(thisDropzone, mockFile, '/upload/images/{{ $site_info->logo_dark }}');
                    thisDropzone.files.push(mockFile)
                }
                @endif
            });

            var add_post = new Dropzone("div#dropzone_favicon", {
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
                    input.setAttribute('name', 'favicon[]');

                    let form = document.getElementById('add');
                    form.append(input);
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
                dictDefaultMessage: "Drag files here to upload",
                dictRemoveFile: "Delete file",
                dictCancelUpload: "Cancel download",
                dictMaxFilesExceeded: "Can't load more",

                @if(old('dropzone_images'))
                init: function() {
                    var thisDropzone = this;

                    // document.querySelector('.dropzone').classList.add('dz-max-files-reached');

                    @foreach(old('dropzone_images') as $img)

                    var input = document.createElement('input');
                    input.setAttribute('type', 'hidden');
                    input.setAttribute('value', '{{ $img }}');
                    input.setAttribute('name', 'favicon[]');

                    var form = document.getElementById('add');
                    form.append(input);

                    var mockFile = {
                        name: '{{ $img }}',
                        size: 1024 * 512,
                        accepted: true
                    };

                    thisDropzone.options.addedfile.call(thisDropzone, mockFile);
                    thisDropzone.options.thumbnail.call(thisDropzone, mockFile, '/upload/images/{{ $img }}');
                    thisDropzone.files.push(mockFile)

                    @endforeach
                }
                @elseif(isset($site_info -> favicon))
                init: function() {
                    var thisDropzone = this;

                    var input = document.createElement('input');
                    input.setAttribute('type', 'hidden');
                    input.setAttribute('value', '{{ $site_info->favicon }}');
                    input.setAttribute('name', 'favicon[]');

                    var form = document.getElementById('add');
                    form.append(input);

                    var mockFile = {
                        name: '{{ $site_info->favicon }}',
                        size: 1024 * 512,
                        accepted: true
                    };

                    thisDropzone.options.addedfile.call(thisDropzone, mockFile);
                    thisDropzone.options.thumbnail.call(thisDropzone, mockFile, '/upload/images/{{ $site_info->favicon }}');
                    thisDropzone.files.push(mockFile)
                }
                @endif
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
            'disabled' => true
            ],
            ]
            ])
        </div>
    </div> <!-- / .header -->

    <!-- CARDS -->
    <div class="container-fluid">
        <form method="post" action="{{ route('facts_figures.create') }}" enctype="multipart/form-data" id="add">
            @csrf
            <div class="row">
                <div class="col-8">
                    <div class="card mw-50">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12">

                                    <div class="form-group">
                                        <label for="phone_number" class="form-label">Auditoriya sigʻimi</label>
                                        <input name="audience_size" id="phone_number" cols="4" rows="4"  value="{{ old('audience_size') ?? $site_info->audience_size ?? null }}" class="form-control  @error('audience_size') is-invalid @enderror" name="audience_size">
                                        @error('phone_number')
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="phone_number" class="form-label">Taʼlim dasturlari</label>
                                        <input name="educational_programs" id="educational_programs" cols="4" rows="4"  value="{{ old('educational_programs') ?? $site_info->educational_programs ?? null }}" class="form-control  @error('educational_programs') is-invalid @enderror" name="educational_programs">
                                        @error('educational_programs')
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="phone_number" class="form-label">Green Zone</label>
                                        <input name="green_zone" id="green_zone" cols="4" rows="4"  value="{{ old('green_zone') ?? $site_info->green_zone ?? null }}" class="form-control  @error('green_zone') is-invalid @enderror" name="green_zone">
                                        @error('green_zone')
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="phone_number" class="form-label">Kutubxona fondi</label>
                                        <input name="library_collection" id="library_collection" cols="4" rows="4"  value="{{ old('library_collection') ?? $site_info->library_collection ?? null }}" class="form-control  @error('library_collection') is-invalid @enderror" name="library_collection">
                                        @error('library_collection')
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="phone_number" class="form-label">Number of Students</label>
                                        <input name="number_of_students" id="number_of_students" cols="4" rows="4"  value="{{ old('number_of_students') ?? $site_info->number_of_students ?? null }}" class="form-control  @error('number_of_students') is-invalid @enderror" name="number_of_students">
                                        @error('number_of_students')
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="phone_number" class="form-label">Yigit talabalar</label>
                                        <input name="male_students" id="male_students" cols="4" rows="4"  value="{{ old('male_students') ?? $site_info->male_students ?? null }}" class="form-control  @error('male_students') is-invalid @enderror" name="male_students">
                                        @error('male_students')
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="phone_number" class="form-label">Qiz talabalar</label>
                                        <input name="female_students" id="female_students" cols="4" rows="4"  value="{{ old('female_students') ?? $site_info->female_students ?? null }}" class="form-control  @error('female_students') is-invalid @enderror" name="female_students">
                                        @error('male_students')
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

            </div>
        </form>
    </div>
@endsection
