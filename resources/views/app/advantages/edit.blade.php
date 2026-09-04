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
            init: function() {
                @if(isset($advantage -> img))

                var thisDropzone = this;

                document.querySelector('.dropzone').classList.add('dz-max-files-reached');

                var input = document.createElement('input');
                input.setAttribute('type', 'hidden');
                input.setAttribute('value', '{{ $advantage->img }}');
                input.setAttribute('name', 'dropzone_images');

                let form = document.getElementById('add');
                form.append(input);

                var mockFile = {
                    name: '{{ $advantage->img }}',
                    size: 1024 * 512,
                    accepted: true
                };

                // thisDropzone.emit("addedfile", mockFile);
                // thisDropzone.emit("thumbnail", mockFile, '{{ $advantage->sm_img }}');
                // thisDropzone.emit("complete", mockFile);

                thisDropzone.options.addedfile.call(thisDropzone, mockFile);
                thisDropzone.options.thumbnail.call(thisDropzone, mockFile, '{{ $advantage->sm_img }}');
                thisDropzone.files.push(mockFile)

                @endif
            },
            error: function(file, message) {
                alert(message);
                this.removeFile(file);
            },

            // change default texts
            dictDefaultMessage: "Fayllarni shu yerga tashlang",
            dictRemoveFile: "Faylni oʻchirish",
            dictCancelUpload: "Yuklashni bekor qilish",
            dictMaxFilesExceeded: "Bundan ortiq yuklab boʻlmaydi"
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
        'name' => 'Tahrirlash',
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
                    <form method="post" action="{{ route($route_name . '.update', [$route_parameter => $advantage]) }}" enctype="multipart/form-data" id="add">
                        @csrf
                        @method('put')
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
                                            <label for="title" class="form-label {{ $lang->code == $main_lang->code ? 'required' : '' }}">Sarlavha</label>
                                            <input type="text" {{ $lang->code == $main_lang->code ? 'required' : '' }} class="form-control @error('title.'.$lang->code) is-invalid @enderror" name="title[{{ $lang->code }}]" value="{{ old('title.'.$lang->code) ?? $advantage->title[$lang->code] ?? null }}" id="title" placeholder="Sarlavha...">
                                            @error('title.'.$lang->code)
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                        <div class="form-group">
                                            <label for="desc" class="form-label">Tavsif</label>
                                            <textarea name="desc[{{ $lang->code }}]" id="desc" cols="30" rows="10" class="form-control @error('desc.'.$lang->code) is-invalid @enderror ckeditor" name="desc[{{ $lang->code }}]" placeholder="Tavsif...">{{ old('desc.'.$lang->code) ?? $advantage->desc[$lang->code] ?? null }}</textarea>
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
                                    <label for="advantage_category_id" class="form-label">Turkum</label>
                                    <select class="form-select @error('advantage_category_id') is-invalid @enderror" id="advantage_category_id" name="advantage_category_id">
                                        <option value="">Roʻyxatdan tanlang</option>
                                        @foreach ($all_categories as $key => $item)
                                        <option value="{{ $item->id }}" {{ old('advantage_category_id') ?? $advantage->advantage_category_id == $item->id ? 'selected' : '' }}>{{ $item->title[$main_lang->code] }}</option>
                                        @endforeach
                                    </select>
                                    @error('advantage_category_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <!-- Dropzone -->
                                    <label for="dropzone" class="form-label">Fon rasmi</label>
                                    <div class="dropzone dropzone-multiple" id="dropzone"></div>
                                </div>
                            </div>
                        </div>
                        <!-- Button -->
                        <div class="model-btns d-flex justify-content-end">
                            <a href="{{ route($route_name.'.index') }}" type="button" class="btn btn-secondary">Bekor qilish</a>
                            <button type="submit" class="btn btn-primary ms-2">Saqlash</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
        <div class="col-4">

        </div>
    </div>
</div>
@endsection