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
                maxFiles: 10,
                maxFilesize: 15, // MB
                success: (file, response) => {
                    let input = document.createElement('input');
                    input.setAttribute('type', 'hidden');
                    input.setAttribute('value', response.file_name);
                    input.setAttribute('name', 'dropzone_images[]');

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
                    input.setAttribute('name', 'dropzone_images[]');

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
                @endif
            });
        };
    </script>
@endsection
@section('content')
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

    <!-- CARDS -->
    <div class="container-fluid">
        <div class="row">
            <div class="col-8">
                <div class="card mw-50">
                    <div class="card-body">
                        <form method="post" action="{{ route($route_name . '.store') }}" enctype="multipart/form-data" id="add">
                            @csrf
                            <div class="row">
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
                                                <label for="title" class="form-label {{ $lang->code == $main_lang->code ? 'required' : '' }}"> Nomi</label>
                                                <input type="text" {{ $lang->code == $main_lang->code ? 'required' : '' }} class="form-control @error('name.'.$lang->code) is-invalid @enderror" name="name[{{ $lang->code }}]" value="{{ old('name.'.$lang->code) }}" id="title" placeholder="Nomi...">
                                                @error('name.'.$lang->code)
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <label for="title" class="form-label {{ $lang->code == $main_lang->code ? 'required' : '' }}">Birinchi nomi</label>
                                                <input type="text" {{ $lang->code == $main_lang->code ? 'required' : '' }} class="form-control @error('title.'.$lang->code) is-invalid @enderror" name="first_name[{{ $lang->code }}]" value="{{ old('first_name.'.$lang->code) }}" id="title" placeholder="Ismi...">
                                                @error('first_name.'.$lang->code)
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <label for="desc" class="form-label">Birinchi tavsif</label>
                                                <textarea name="first_description[{{ $lang->code }}]" id="desc" cols="30" rows="10" class="form-control @error('desc.'.$lang->code) is-invalid @enderror ckeditor" name="first_description[{{ $lang->code }}]" placeholder="Tavsif...">{{ old('desc.'.$lang->code) }}</textarea>
                                                @error('first_description.'.$lang->code)
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>
~
                                            <div class="form-group">
                                                <label for="title" class="form-label {{ $lang->code == $main_lang->code ? 'required' : '' }}">Ikkinchi nomi</label>
                                                <input type="text" {{ $lang->code == $main_lang->code ? 'required' : '' }} class="form-control @error('second_name.'.$lang->code) is-invalid @enderror" name="second_name[{{ $lang->code }}]" value="{{ old('second_name.'.$lang->code) }}" id="title" placeholder="Ikkinchi nomi...">
                                                @error('second_name.'.$lang->code)
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <label for="desc" class="form-label">Ikkinchi tavsif</label>
                                                <textarea name="second_description[{{ $lang->code }}]" id="desc" cols="30" rows="10" class="form-control @error('second_description.'.$lang->code) is-invalid @enderror ckeditor" name="second_description[{{ $lang->code }}]" placeholder="Ikkinchi tavsif...">{{ old('second_description.'.$lang->code) }}</textarea>
                                                @error('second_description.'.$lang->code)
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <label for="title" class="form-label {{ $lang->code == $main_lang->code ? 'required' : '' }}">Uchinchi nomi</label>
                                                <input type="text" {{ $lang->code == $main_lang->code ? 'required' : '' }} class="form-control @error('third_name.'.$lang->code) is-invalid @enderror" name="third_name[{{ $lang->code }}]" value="{{ old('third_name.'.$lang->code) }}" id="title" placeholder="Uchinchi nomi...">
                                                @error('third_name.'.$lang->code)
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <label for="desc" class="form-label">Uchinchi tavsif</label>
                                                <textarea  id="desc" cols="30" rows="10" class="form-control @error('third_description.'.$lang->code) is-invalid @enderror ckeditor" name="third_description[{{ $lang->code }}]" placeholder="Uchinchi tavsif...">{{ old('third_description.'.$lang->code) }}</textarea>
                                                @error('third_description.'.$lang->code)
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
                                    <label for="date" class="form-label">Taʼlim dasturlari</label>
                                    <input type="text" id="date" name="educational_programs" class="form-control" value="{{ old('educational_programs') }}" placeholder="Taʼlim dasturlari"  />
                                </div>
                                <div class="form-group">
                                    <label for="date" class="form-label">Auditoriya sigʻimi</label>
                                    <input type="text" id="date" name="audience_size" class="form-control" value="{{ old('audience_size') }}" placeholder="Auditoriya sigʻimi"  />
                                </div>
                                <div class="form-group">
                                    <label for="date" class="form-label">Yashil hudud</label>
                                    <input type="text" id="date" name="green_zone" class="form-control" value="{{ old('green_zone') }}" placeholder="Yashil hudud"  />
                                </div>
                                <div class="form-group">
                                    <label for="menu_id" class="form-label">Turi</label>
                                    <select name="active" class="form-select">
                                        <option value="1">Sinov markazi</option>
                                        <option value="0">Kampuslar</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="map" class="form-label">Insert card (<code>iframe</code>)</label>
                                    <textarea name="map" id="map" cols="4" rows="4" class="form-control @error('map') is-invalid @enderror" name="map">{{ old('map')  }}</textarea>
                                    @error('map')
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
        </div>
    </form>
    </div>
@endsection