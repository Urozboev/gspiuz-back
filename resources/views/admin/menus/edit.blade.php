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
                    @if(isset($productsCategory -> img))

                    var thisDropzone = this;

                    document.querySelector('.dropzone').classList.add('dz-max-files-reached');

                    var input = document.createElement('input');
                    input.setAttribute('type', 'hidden');
                    input.setAttribute('value', '{{ $productsCategory->img }}');
                    input.setAttribute('name', 'dropzone_images');

                    let form = document.getElementById('add');
                    form.append(input);

                    var mockFile = {
                        name: '{{ $productsCategory->img }}',
                        size: 1024 * 512,
                        accepted: true
                    };

                    thisDropzone.options.addedfile.call(thisDropzone, mockFile);
                    thisDropzone.options.thumbnail.call(thisDropzone, mockFile, '{{ $productsCategory->sm_img }}');
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
            'name' => 'Added',
            'disabled' => true
            ],
            ]
            ])
        </div>
    </div> <!-- / .header -->

    <!-- CARDS -->
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="card mw-50">
                    <div class="card-body">
                        <form method="post" action="{{ route($route_name . '.update', [$route_parameter => $menu]) }}" enctype="multipart/form-data" id="add">
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
                                                    <input type="text" {{ $lang->code == $main_lang->code ? 'required' : '' }} class="form-control @error('title.'.$lang->code) is-invalid @enderror" name="title[{{ $lang->code }}]" value="{{ old('title.'.$lang->code) ?? $menu->title[$lang->code] ?? null }}" id="title" placeholder="Sarlavha...">
                                                    @error('title.'.$lang->code)
                                                    <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                                    @enderror
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="form-group">
                                        <label for="path" class="form-label {{ $lang->code == $main_lang->code ? 'required' : '' }}">Sayt manzili</label>
                                        <input type="text"  class="form-control @error('path') is-invalid @enderror" name="path" value="{{ old('path') ?? $menu->path ?? null }}" id="path" placeholder="Sayt manzili...">
                                        @include('admin.menus._path-warning')
                                        @error('path')
                                        <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="path" class="form-label {{ $lang->code == $main_lang->code ? 'required' : '' }}">Manzil</label>
                                        <input type="text"  class="form-control @error('path') is-invalid @enderror" name="slug" value="{{ old('slug')?? $menu->slug ?? null }}" id="slug" placeholder="Manzil...">
                                        @error('slug')
                                        <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>




                                    <div class="form-group">
                                        <label for="parent_id" class="form-label">Yuqori menyu</label>
                                        <select class="form-select @error('parent_id') is-invalid @enderror" id="parent_id" name="parent_id">
                                            <option value="">Asosiy menyu</option>
                                            @foreach ($menus as $key => $item)
                                                <option value="{{ $item->id }}" {{ old('parent_id') ?? $menu->parent_id == $item->id ? 'selected' : '' }}>{{ $item->title[$main_lang->code] }}</option>
                                            @endforeach
                                        </select>
                                        @error('parent_id')
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="in_main" class="form-label">Tartib</label>
                                        <input type="text"  class="form-control @error('order') is-invalid @enderror" name="order" value="{{ old('order') ?? $menu->order ?? null }}" id="order" placeholder="Tartib...">

                                        @error('in_main')
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

                        </form>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-4">

            </div>
        </div>
    </div>
@endsection
