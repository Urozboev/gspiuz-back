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
    <form method="post" action="{{ route($route_name . '.store') }}" enctype="multipart/form-data" id="add">
        @csrf
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
                                            <label for="title" class="form-label {{ $lang->code == $main_lang->code ? 'required' : '' }}">Headline</label>
                                            <input type="text" {{ $lang->code == $main_lang->code ? 'required' : '' }} class="form-control @error('title.'.$lang->code) is-invalid @enderror" name="title[{{ $lang->code }}]" value="{{ old('title.'.$lang->code) ?? $site_info->title[$lang->code] ?? '' }}" id="title" placeholder="Headline...">
                                            @error('title.'.$lang->code)
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Shior</label>
                                            <input type="text" name="tagline[{{ $lang->code }}]" class="form-control"
                                                   value="{{ old('tagline.'.$lang->code) ?? $site_info->tagline[$lang->code] ?? '' }}"
                                                   placeholder="Masalan: Sirdaryo yoshlari taʼlim va taraqqiyot yoʻlida!">
                                            <small class="form-text text-muted">Saytning yuqori qismida koʻrinadi.</small>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Qoʻshimcha shior</label>
                                            <input type="text" name="slogan[{{ $lang->code }}]" class="form-control"
                                                   value="{{ old('slogan.'.$lang->code) ?? $site_info->slogan[$lang->code] ?? '' }}"
                                                   placeholder="Masalan: 2022-yildan pedagogika xizmatidamiz">
                                            <small class="form-text text-muted">Sayt pastida koʻrinadi.</small>
                                        </div>
                                        <div class="form-group">
                                            <label for="desc" class="form-label">Tavsif</label>
                                            <textarea name="desc[{{ $lang->code }}]" id="desc" cols="30" rows="10" class="form-control @error('desc.'.$lang->code) is-invalid @enderror ckeditor" name="desc[{{ $lang->code }}]" placeholder="Tavsif...">{{ old('desc.'.$lang->code) ?? $site_info->desc[$lang->code] ?? '' }}</textarea>
                                            @error('desc.'.$lang->code)
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                        <div class="form-group">
                                            <label for="address" class="form-label">Manzil</label>
                                            <textarea name="address[{{ $lang->code }}]" id="address" cols="4" rows="4" class="form-control @error('address.'.$lang->code) is-invalid @enderror" name="address[{{ $lang->code }}]">{{ old('address.'.$lang->code) ?? $site_info->address[$lang->code] ?? 'Shahar, tuman | Shahar2, tuman2...' }}</textarea>
                                            @error('address.'.$lang->code)
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
{{--                                        <div class="form-group">--}}
{{--                                            <label for="work_time" class="form-label">Opening hours</label>--}}
{{--                                            <textarea name="work_time[{{ $lang->code }}]" id="work_time" cols="4" rows="4" class="form-control @error('work_time.'.$lang->code) is-invalid @enderror" name="work_time[{{ $lang->code }}]">{{ old('work_time.'.$lang->code) ?? $site_info->work_time[$lang->code] ?? '' }}</textarea>--}}
{{--                                            @error('work_time.'.$lang->code)--}}
{{--                                            <span class="invalid-feedback" role="alert">--}}
{{--                                                <strong>{{ $message }}</strong>--}}
{{--                                            </span>--}}
{{--                                            @enderror--}}
{{--                                        </div>--}}
                                    </div>
                                    @endforeach
                                </div>
                                <div class="form-group">
                                    <label for="phone_number" class="form-label">Telefon raqami</label>
                                    <textarea name="phone_number" id="phone_number" cols="4" rows="3" class="form-control @error('phone_number') is-invalid @enderror" placeholder="+998 55 651 92 76 | +998 55 516 90 00">{{ old('phone_number') ?? $site_info->phone_number ?? '' }}</textarea>
                                    @error('phone_number')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                    <div class="form-text">
                                        Bir nechta raqamni <code>|</code> belgisi bilan ajrating.
                                        Bu raqam saytning tepasida ham, pastki qismida ham koʻrinadi.
                                    </div>
                                </div>
                                {{--
                                    Call markaz raqami alohida: saytda "Call markaz"
                                    yozuvi ostida chiqadi. Boʻsh qoldirilsa yuqoridagi
                                    asosiy raqam ishlatiladi.
                                --}}
                                <div class="form-group">
                                    <label for="call_center" class="form-label">Call markaz raqami</label>
                                    <input type="text" name="call_center" id="call_center"
                                           class="form-control @error('call_center') is-invalid @enderror"
                                           value="{{ old('call_center') ?? $site_info->call_center ?? '' }}"
                                           placeholder="Ixtiyoriy — boʻsh qoldirsangiz asosiy raqam ishlatiladi">
                                    @error('call_center')
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
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="yt_url" class="form-label">Bosh sahifadagi video havolasi</label>
                                    <input type="text" class="form-control" name="yt_url" value="{{ old('yt_url') ?? $site_info->yt_url ?? '' }}" id="yt_url" placeholder="https://www.youtube.com/watch?v=…">
                                    <small class="form-text text-muted">Shart emas.</small>
                                </div>
                                <hr>

                                <p class="text-muted mb-3" style="font-size: .8125rem;">
                                    <i class="fe fe-info"></i>
                                    Hujjat qabuli muddati. Saytda “Hujjat qabuliga N kun qoldi”
                                    hisoblagichi shu sanalardan hisoblanadi. Ikkala sana ham boʻsh
                                    boʻlsa, hisoblagich saytda umuman koʻrinmaydi.
                                </p>

                                <div class="row">
                                    <div class="col-12 col-md-6">
                                        <div class="form-group">
                                            <label for="admission_starts_at" class="form-label">Qabul boshlanishi</label>
                                            <input type="date" class="form-control" id="admission_starts_at"
                                                   name="admission_starts_at"
                                                   value="{{ old('admission_starts_at') ?? optional($site_info->admission_starts_at ?? null)->format('Y-m-d') }}">
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="form-group">
                                            <label for="admission_ends_at" class="form-label">Qabul tugashi</label>
                                            <input type="date" class="form-control" id="admission_ends_at"
                                                   name="admission_ends_at"
                                                   value="{{ old('admission_ends_at') ?? optional($site_info->admission_ends_at ?? null)->format('Y-m-d') }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="admission_url" class="form-label">Qabul sahifasi havolasi</label>
                                    <input type="text" class="form-control" id="admission_url" name="admission_url"
                                           value="{{ old('admission_url') ?? $site_info->admission_url ?? '' }}"
                                           placeholder="https://qabul.gspi.uz">
                                    <small class="form-text text-muted">
                                        Boʻsh qoldirilsa, hisoblagich saytdagi /admissions sahifasiga olib boradi.
                                    </small>
                                </div>

                                <hr>

                                <div class="form-group">
                                    <label for="email" class="form-label">Elektron pochta</label>
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
                                    <label for="map" class="form-label">Insert card (<code>iframe</code>)</label>
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
