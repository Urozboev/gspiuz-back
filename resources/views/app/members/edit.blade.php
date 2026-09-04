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
                @if(isset($member -> img))

                var thisDropzone = this;

                document.querySelector('.dropzone').classList.add('dz-max-files-reached');

                var input = document.createElement('input');
                input.setAttribute('type', 'hidden');
                input.setAttribute('value', '{{ $member->img }}');
                input.setAttribute('name', 'dropzone_images');

                let form = document.getElementById('add');
                form.append(input);

                var mockFile = {

                    name: '{{ $member->img }}',
                    size: 1024 * 512,
                    accepted: true
                };

                thisDropzone.options.addedfile.call(thisDropzone, mockFile);
                thisDropzone.options.thumbnail.call(thisDropzone, mockFile, '{{ $member->sm_img }}');
                thisDropzone.files.push(mockFile)

                @endif
            },
            error: function(file, message) {
                alert(message);
                this.removeFile(file);
            },

            // change default texts
            dictDefaultMessage: "Drag files here to upload",
            dictRemoveFile: "Delete file",
            dictCancelUpload: "Cancel download",
            dictMaxFilesExceeded: "Can't load more"
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
        'name' => 'Editing',
        'disabled' => true
        ],
        ]
        ])
    </div>
</div> <!-- / .header -->

<!-- CARDS -->
<div class="container-fluid">
    <form method="post" action="{{ route($route_name . '.update', $member->id) }}" enctype="multipart/form-data" id="add">
        @csrf
        @method('put')
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
                                            <label for="name_{{ $lang->code }}" class="form-label {{ $lang->code == $main_lang->code ? 'required' : '' }}">F.I.O</label>
                                            <input type="text" {{ $lang->code == $main_lang->code ? 'required' : '' }} class="form-control @error('name.'.$lang->code) is-invalid @enderror" name="name[{{ $lang->code }}]" value="{{ old('name.'.$lang->code) ?? $member->name[$lang->code] ?? null }}" id="name_{{ $lang->code }}" placeholder="F.I.Sh....">
                                            @error('name.'.$lang->code)
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                        <div class="form-group">
                                            <label for="position_{{ $lang->code }}" class="form-label">Lavozim</label>
                                            <input type="text" class="form-control @error('position.'.$lang->code) is-invalid @enderror" name="position[{{ $lang->code }}]" value="{{ old('position.'.$lang->code) ?? $member->position[$lang->code] ?? null }}" id="position_{{ $lang->code }}" placeholder="Oʻrni...">
                                            @error('position.'.$lang->code)
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                        <div class="form-group">
                                            <label for="work_time_{{ $lang->code }}" class="form-label">Qabul vaqti</label>
                                            <input type="text" class="form-control @error('work_time.'.$lang->code) is-invalid @enderror" name="work_time[{{ $lang->code }}]" value="{{ old('work_time.'.$lang->code) ?? $member->work_time[$lang->code] ?? null }}" id="work_time_{{ $lang->code }}" placeholder="Qabul vaqti...">
                                            @error('work_time.'.$lang->code)
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                        <div class="form-group">
                                            <label for="answer{{ $lang->code }}" class="form-label {{ $lang->code == $main_lang->code ? 'required' : '' }}">Tavsif</label>
                                            <textarea id="dec.{{ $lang->code }}" cols="30" rows="10" class="form-control @error('dec.'.$lang->code) is-invalid @enderror ckeditor" name="dec[{{ $lang->code }}]" placeholder="Tavsif...">{{ old('dec.'.$lang->code) ?? $member->dec[$lang->code] ?? null }}</textarea>
                                            @error('dec.'.$lang->code)
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                <div class="form-group">
                                    <label for="phone_number" class="form-label">Telefon raqami</label>
                                    <input type="text" class="form-control @error('phone_number') is-invalid @enderror" name="phone_number" value="{{ old('phone_number') ?? $member->phone_number }}" id="phone_number" placeholder="Telefon raqami...">
                                    @error('phone_number')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <!-- Dropzone -->
                                    <label for="dropzone" class="form-label">Logotip</label>
                                    <div class="dropzone dropzone-multiple" id="dropzone"></div>
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
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <div class="form-group">
                                        <label for="instagram_link" class="form-label">Yillar</label>
                                        <input type="number" class="form-control @error('yers') is-invalid @enderror" name="yers" value="{{ old('yers') ?? $member->yers ?? null }}" id="yers" placeholder="yers...">
                                        @error('yers')
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                        @enderror
                                    </div>
                                    <label for="menu_id" class="form-label">Jinsi</label>
                                    <select name="gender" class="form-select">
                                        <option value="male" {{ $member->gender == 'male' ? 'selected' : '' }}>Erkak</option>
                                        <option value="female" {{ $member->gender == 'female' ? 'selected' : '' }}>Ayol</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="instagram_link" class="form-label">Instagram havolasi</label>
                                    <input type="text" class="form-control @error('instagram_link') is-invalid @enderror" name="instagram_link" value="{{ old('instagram_link') ?? $member->instagram_link }}" id="instagram_link" placeholder="havola...">
                                    @error('instagram_link')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="date" class="form-label">Manzil</label>
                                    <input type="text" id="date" name="slug" class="form-control" value="{{ old('slug') ?? $member->slug }}" />
                                </div>
                                <div class="form-group">
                                    <label for="telegram_link" class="form-label">Telegram havolasi</label>
                                    <input type="text" class="form-control @error('telegram_link') is-invalid @enderror" name="telegram_link" value="{{ old('telegram_link') ?? $member->telegram_link }}" id="telegram_link" placeholder="havola...">
                                    @error('telegram_link')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="facebook_link" class="form-label">Facebook havolasi</label>
                                    <input type="text" class="form-control @error('facebook_link') is-invalid @enderror" name="facebook_link" value="{{ old('facebook_link') ?? $member->facebook_link }}" id="facebook_link" placeholder="havola...">
                                    @error('facebook_link')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="linkedin_link" class="form-label">LinkedIn havolasi</label>
                                    <input type="text" class="form-control @error('linkedin_link') is-invalid @enderror" name="linkedin_link" value="{{ old('linkedin_link') ?? $member->linkedin_link }}" id="linkedin_link" placeholder="havola...">
                                    @error('linkedin_link')
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
    </form>
</div>
@endsection
