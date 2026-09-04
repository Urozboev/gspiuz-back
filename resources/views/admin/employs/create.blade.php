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
        'name' => 'Addition',
        'disabled' => true
        ],
        ]
        ])
    </div>
</div> <!-- / .header -->

<!-- CARDS -->
<div class="container-fluid">
    <form method="post" action="{{ route($route_name . '.store') }}" enctype="multipart/form-data" id="add">
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
                                                <label for="title" class="form-label {{ $lang->code == $main_lang->code ? 'required' : '' }}">Ismi</label>
                                                <input type="text" {{ $lang->code == $main_lang->code ? 'required' : '' }} class="form-control @error('first_name.'.$lang->code) is-invalid @enderror" name="first_name[{{ $lang->code }}]" value="{{ old('first_name.'.$lang->code) }}" id="title" placeholder="Ismi...">
                                                @error('first_name.'.$lang->code)
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <label for="last_name" class="form-label">Familiyasi</label>
                                                <input type="text" class="form-control @error('last_name.'.$lang->code) is-invalid @enderror" name="last_name[{{ $lang->code }}]" value="{{ old('last_name.'.$lang->code) }}" id="last_name" placeholder="Familiyasi...">
                                                @error('last_name.'.$lang->code)
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <label for="surname" class="form-label">Otasining ismi</label>
                                                <input type="text" class="form-control @error('surname.'.$lang->code) is-invalid @enderror" name="surname[{{ $lang->code }}]" value="{{ old('surname.'.$lang->code) }}" id="surname" placeholder="Otasining ismi...">
                                                @error('surname.'.$lang->code)
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <label for="address" class="form-label">Manzil</label>
                                                <input type="text" class="form-control @error('address.'.$lang->code) is-invalid @enderror" name="address[{{ $lang->code }}]" value="{{ old('address.'.$lang->code) }}" id="address" placeholder="Manzil...">
                                                @error('address.'.$lang->code)
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <label for="address" class="form-label">Ish vaqti</label>
                                                <input type="text" class="form-control @error('work_time.'.$lang->code) is-invalid @enderror" name="work_time[{{ $lang->code }}]" value="{{ old('work_time.'.$lang->code)?? $employ->work_time[$lang->code] ?? null }}" id="address" placeholder="Ish vaqti...">
                                                @error('address.'.$lang->code)
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <label for="address" class="form-label">Lavozim</label>
                                                <input type="text" class="form-control @error('position.'.$lang->code) is-invalid @enderror" name="position[{{ $lang->code }}]" value="{{ old('position.'.$lang->code)?? $employ->position[$lang->code] ?? null }}" id="position" placeholder="lavozim...">
                                                @error('position.'.$lang->code)
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <label for="answer{{ $lang->code }}" class="form-label {{ $lang->code == $main_lang->code ? 'required' : '' }}">Tavsif</label>
                                                <textarea id="dec.{{ $lang->code }}" cols="30" rows="10" class="form-control @error('dec.'.$lang->code) is-invalid @enderror ckeditor" name="dec[{{ $lang->code }}]" placeholder="Tavsif...">{{ old('dec.'.$lang->code) }}</textarea>
                                                @error('dec.'.$lang->code)
                                                <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                                @enderror
                                            </div>

                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="date" class="form-label">Elektron pochta</label>
                                    <input type="email" id="date" name="email" class="form-control" value="{{ old('email') }}" placeholder="Elektron pochta"  />
                                </div>
                                <div class="form-group">
                                    <label for="date" class="form-label">Telefon</label>
                                    <input type="phone" id="date" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="Telefon"  />
                                </div>
                            </div>
                            <!-- Button -->
                            <div class="model-btns d-flex justify-content-end">
                                <a href="{{ route('employs.index') }}" type="button" class="btn btn-secondary">Bekor qilish</a>
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
                                    <label for="date" class="form-label">Tugʻilgan sana</label>
                                    <input type="date" id="date" name="birthday" class="form-control" value="{{ old('birthday') }}" placeholder="{{ date('d-m-Y') }}" />
                                </div>
                                <div class="form-group">
                                    <label for="date" class="form-label">Ishga kirgan sana</label>
                                    <input type="date" id="date" name="start_time" class="form-control" value="{{ old('start_time') }}" placeholder="{{ date('d-m-Y') }}"  />
                                </div>
                                <div class="form-group">
                                    <label for="menu_id" class="form-label">Holati</label>
                                    <select name="status" class="form-select">
                                        <option value="1">Faol</option>
                                        <option value="0">Nofaol</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="menu_id" class="form-label">Jinsi</label>
                                    <select name="gender" class="form-select">
                                        <option value="male">Erkak</option>
                                        <option value="female">Ayol</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-auto">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input"
                                                       type="checkbox"
                                                       name="special"
                                                       id="switchTwo"
                                                       value="1">
                                                <label class="form-check-label" for="switchTwo"></label>
                                            </div>
                                        </div>
                                        <div class="col ms-n2">
                                            <small class="text-muted">
                                                Special
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-auto">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input"
                                                       type="checkbox"
                                                       name="professor"
                                                       id="switchTwo"
                                                       value="1">
                                                <label class="form-check-label" for="switchTwo"></label>
                                            </div>
                                        </div>
                                        <div class="col ms-n2">
                                            <small class="text-muted">
                                                Professor
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <!-- Dropzone -->
                                    <label for="dropzone" class="form-label">Rasm</label>
                                    <div class="dropzone dropzone-multiple" id="dropzone"></div>
                                </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
