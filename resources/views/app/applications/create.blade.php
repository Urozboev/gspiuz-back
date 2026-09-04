
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
                dictDefaultMessage: "Fayllarni shu yerga tashlang",
                dictRemoveFile: "Faylni oʻchirish",
                dictCancelUpload: "Yuklashni bekor qilish",
                dictMaxFilesExceeded: "Bundan ortiq yuklab boʻlmaydi",

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
                dictDefaultMessage: "Fayllarni shu yerga tashlang",
                dictRemoveFile: "Faylni oʻchirish",
                dictCancelUpload: "Yuklashni bekor qilish",
                dictMaxFilesExceeded: "Bundan ortiq yuklab boʻlmaydi",

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
        <form method="post" action="{{ route($route_name . '.store') }}" enctype="multipart/form-data" id="add">
            @csrf
            <div class="row">
                <div class="col-8">
                    <div class="card mw-50">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label for="email" class="form-label">Ismi *</label>
                                        <input type="text" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') ?? $site_info->email ?? '' }}" id="email" placeholder="Elektron pochta...">
                                        @error('email')
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="telegram" class="form-label">Telegram</label>
                                        <input type="text" class="form-control @error('telegram') is-invalid @enderror" name="telegram" value="{{ old('telegram') ?? $site_info->telegram ?? '' }}" id="telegram" placeholder="Telegram...">
                                        @error('telegram')
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="instagram" class="form-label">Instagram</label>
                                        <input type="text" class="form-control @error('instagram') is-invalid @enderror" name="instagram" value="{{ old('instagram') ?? $site_info->instagram ?? '' }}" id="instagram" placeholder="Instagram...">
                                        @error('instagram')
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="facebook" class="form-label">Facebook</label>
                                        <input type="text" class="form-control @error('facebook') is-invalid @enderror" name="facebook" value="{{ old('facebook') ?? $site_info->facebook ?? '' }}" id="facebook" placeholder="Facebook...">
                                        @error('facebook')
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="youtube" class="form-label">Youtube</label>
                                        <input type="text" class="form-control @error('youtube') is-invalid @enderror" name="youtube" value="{{ old('youtube') ?? $site_info->youtube ?? '' }}" id="youtube" placeholder="Youtube...">
                                        @error('youtube')
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="map" class="form-label">Xarita qoʻyish (<code>iframe</code>)</label>
                                        <textarea name="map" id="map" cols="4" rows="4" class="form-control @error('map') is-invalid @enderror" name="map">{{ old('map') ?? $site_info->map ?? '' }}</textarea>
                                        @error('map')
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                        @enderror
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group">
                                                <!-- Dropzone -->
                                                <label for="dropzone" class="form-label">Logotip</label>
                                                <div class="dropzone dropzone-multiple" id="dropzone_logo"></div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group">
                                                <!-- Dropzone -->
                                                <label for="dropzone" class="form-label">Logotip (ikkinchi)</label>
                                                <div class="dropzone dropzone-multiple" id="dropzone_logo_dark"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group">
                                                <!-- Dropzone -->
                                                <label for="dropzone" class="form-label">Favikon</label>
                                                <div class="dropzone dropzone-multiple" id="dropzone_favicon"></div>
                                            </div>
                                        </div>
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
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label for="email" class="form-label">First Name *
                                        </label>
                                        <input type="text" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') ?? $site_info->email ?? '' }}" id="email" placeholder="Elektron pochta...">
                                        @error('email')
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="telegram" class="form-label">Telegram</label>
                                        <input type="text" class="form-control @error('telegram') is-invalid @enderror" name="telegram" value="{{ old('telegram') ?? $site_info->telegram ?? '' }}" id="telegram" placeholder="Telegram...">
                                        @error('telegram')
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="instagram" class="form-label">Instagram</label>
                                        <input type="text" class="form-control @error('instagram') is-invalid @enderror" name="instagram" value="{{ old('instagram') ?? $site_info->instagram ?? '' }}" id="instagram" placeholder="Instagram...">
                                        @error('instagram')
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="facebook" class="form-label">Facebook</label>
                                        <input type="text" class="form-control @error('facebook') is-invalid @enderror" name="facebook" value="{{ old('facebook') ?? $site_info->facebook ?? '' }}" id="facebook" placeholder="Facebook...">
                                        @error('facebook')
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="youtube" class="form-label">Youtube</label>
                                        <input type="text" class="form-control @error('youtube') is-invalid @enderror" name="youtube" value="{{ old('youtube') ?? $site_info->youtube ?? '' }}" id="youtube" placeholder="Youtube...">
                                        @error('youtube')
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="map" class="form-label">Xarita qoʻyish (<code>iframe</code>)</label>
                                        <textarea name="map" id="map" cols="4" rows="4" class="form-control @error('map') is-invalid @enderror" name="map">{{ old('map') ?? $site_info->map ?? '' }}</textarea>
                                        @error('map')
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                        @enderror
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group">
                                                <!-- Dropzone -->
                                                <label for="dropzone" class="form-label">Logotip</label>
                                                <div class="dropzone dropzone-multiple" id="dropzone_logo"></div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group">
                                                <!-- Dropzone -->
                                                <label for="dropzone" class="form-label">Logotip (ikkinchi)</label>
                                                <div class="dropzone dropzone-multiple" id="dropzone_logo_dark"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group">
                                                <!-- Dropzone -->
                                                <label for="dropzone" class="form-label">Favikon</label>
                                                <div class="dropzone dropzone-multiple" id="dropzone_favicon"></div>
                                            </div>
                                        </div>
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
