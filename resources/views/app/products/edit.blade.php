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
            dictDefaultMessage: "Fayllarni shu yerga tashlang",
            dictRemoveFile: "Faylni oʻchirish",
            dictCancelUpload: "Yuklashni bekor qilish",
            dictMaxFilesExceeded: "Bundan ortiq yuklab boʻlmaydi",

            @if(old('dropzone_images') || isset($product -> productImages[0]))
            init: function() {
                var thisDropzone = this;

                @foreach((old('dropzone_images') ?? $product -> productImages() -> pluck('img') -> toArray()) as $img)

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
    <form method="post" action="{{ route($route_name . '.update', [$route_parameter => $product]) }}" enctype="multipart/form-data" id="add">
        @csrf
        @method('put')
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
                                                <label for="title" class="form-label {{ $lang->code == $main_lang->code ? 'required' : '' }}">Sarlavha</label>
                                                <input type="text" {{ $lang->code == $main_lang->code ? 'required' : '' }} class="form-control @error('title.'.$lang->code) is-invalid @enderror" name="title[{{ $lang->code }}]" value="{{ old('title.'.$lang->code) ?? $product->title[$lang->code] ?? null }}" id="title" placeholder="Sarlavha...">
                                                @error('title.'.$lang->code)
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <label for="desc" class="form-label">Tavsif</label>
                                                <textarea name="desc[{{ $lang->code }}]" id="desc" cols="30" rows="10" class="form-control @error('desc.'.$lang->code) is-invalid @enderror ckeditor" name="desc[{{ $lang->code }}]" placeholder="Tavsif...">{{ old('desc.'.$lang->code) ?? $product->desc[$lang->code] ?? null }}</textarea>
                                                @error('desc.'.$lang->code)
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <label for="info" class="form-label">Tavsifnoma</label>
                                                <textarea name="info[{{ $lang->code }}]" id="info" cols="30" rows="10" class="form-control @error('info.'.$lang->code) is-invalid @enderror ckeditor" name="info[{{ $lang->code }}]" placeholder="Tavsif...">{{ old('info.'.$lang->code) ?? $product->info[$lang->code] ?? null }}</textarea>
                                                @error('info.'.$lang->code)
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <label for="meta_desc" class="form-label">Meta Tavsif</label>
                                                <textarea id="meta_desc" cols="4" rows="4" class="form-control @error('meta_desc.'.$lang->code) is-invalid @enderror" name="meta_desc[{{ $lang->code }}]">{{ old('meta_desc.'.$lang->code) ?? $product->meta_desc[$lang->code] ?? null }}</textarea>
                                                @error('meta_desc.'.$lang->code)
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <label for="meta_keywords" class="form-label">Meta kalitlar</label>
                                                <textarea id="meta_keywords" cols="4" rows="4" class="form-control @error('meta_keywords.'.$lang->code) is-invalid @enderror" name="meta_keywords[{{ $lang->code }}]">{{ old('meta_keywords.'.$lang->code) ?? $product->meta_keywords[$lang->code] ?? null }}</textarea>
                                                @error('meta_keywords.'.$lang->code)
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>
                                        </div>
                                        @endforeach
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
                                    <label for="brand_id" class="form-label">Brend</label>
                                    <select class="form-select @error('brand_id') is-invalid @enderror" id="brand_id" name="brand_id">
                                        @foreach ($brands as $key => $item)
                                        <option value="{{ $item->id }}" {{ old('brand_id') ?? $product->brand_id == $item->id ? 'selected' : '' }}>{{ $item->title }}</option>
                                        @endforeach
                                    </select>
                                    @error('brand_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="price" class="form-label">Narx</label>
                                    <input type="text" class="form-control @error('price') is-invalid @enderror" name="price" value="{{ old('price') ?? $product->price }}" id="price" placeholder="...">
                                    @error('price')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="discount_price" class="form-label">Narx (chegirmali)</label>
                                    <input type="text" class="form-control @error('discount_price') is-invalid @enderror" name="discount_price" value="{{ old('discount_price') ?? $product->discount_price }}" id="discount_price" placeholder="...">
                                    @error('discount_price')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="categories" class="form-label">Turkumlar</label>
                                    <select class="form-control mb-4 @error('categories') is-invalid @enderror" data-choices='{"removeItemButton": true}' multiple name="categories[]">
                                        @foreach ($all_categories as $key => $item)
                                        <option value="{{ $item->id }}" {{ (old('categories') || $product->productsCategories ? in_array($item->id, (old('categories') ?? $product->productsCategories()->pluck('products_category_id')->toArray())) : '') ? 'selected' : '' }}>{{ $item->title[$main_lang->code] }}</option>
                                        @endforeach
                                    </select>
                                    @error('categories')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <!-- Dropzone -->
                                    <label for="dropzone" class="form-label">Yangilik</label>
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