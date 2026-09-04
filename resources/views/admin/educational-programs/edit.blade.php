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
                    @if(isset($educationalProgram->photo))

                    var thisDropzone = this;

                    document.querySelector('.dropzone').classList.add('dz-max-files-reached');

                    var input = document.createElement('input');
                    input.setAttribute('type', 'hidden');
                    input.setAttribute('value', '{{ $educationalProgram->photo }}');
                    input.setAttribute('name', 'dropzone_images');

                    let form = document.getElementById('add');
                    form.append(input);

                    var mockFile = {
                        name: '{{ $educationalProgram->photo }}',
                        size: 1024 * 512,
                        accepted: true
                    };

                    // thisDropzone.emit("addedfile", mockFile);
                    // thisDropzone.emit("thumbnail", mockFile, '{{ $educationalProgram->sm_img }}');
                    // thisDropzone.emit("complete", mockFile);

                    thisDropzone.options.addedfile.call(thisDropzone, mockFile);
                    thisDropzone.options.thumbnail.call(thisDropzone, mockFile, '{{ $educationalProgram->sm_img }}');
                    thisDropzone.files.push(mockFile)

                    @endif
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
            'name' => 'Update',
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
                        <form method="post" action="{{ route($route_name . '.update', $educationalProgram->id) }}" enctype="multipart/form-data" id="add">
                            @csrf
                            @method('PUT')
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
                                                <input type="text" {{ $lang->code == $main_lang->code ? 'required' : '' }} class="form-control @error('name.'.$lang->code) is-invalid @enderror" name="name[{{ $lang->code }}]" value="{{ old('name.'.$lang->code) ?? ($educationalProgram->name[$lang->code] ?? '') }}" id="title" placeholder="Nomi...">
                                                @error('name.'.$lang->code)
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>

                                            @if ($educationalProgram->parent_id != null && $educationalProgram->parent_id != null)
                                                <div class="form-group">
                                                    <label for="title" class="form-label {{ $lang->code == $main_lang->code ? 'required' : '' }}">Birinchi nomi</label>
                                                    <input type="text" {{ $lang->code == $main_lang->code ? 'required' : '' }} class="form-control @error('firs_name.'.$lang->code) is-invalid @enderror" name="first_name[{{ $lang->code }}]" value="{{ old('first_name.'.$lang->code) ?? ($educationalProgram->first_name[$lang->code] ?? '') }}" id="title" placeholder="Ismi...">
                                                    @error('first_name.'.$lang->code)
                                                    <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                    @enderror
                                                </div>
                                                <div class="form-group">
                                                    <label for="desc" class="form-label">Birinchi tavsif</label>
                                                    <textarea name="first_description[{{ $lang->code }}]" id="desc" cols="30" rows="10" class="form-control @error('desc.'.$lang->code) is-invalid @enderror ckeditor" placeholder="Birinchi tavsif...">{{ old('first_description.'.$lang->code)?? $educationalProgram->first_description[$lang->code] ?? '' }}</textarea>
                                                    @error('first_description.'.$lang->code)
                                                    <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                    @enderror
                                                </div>
                                                <div class="form-group">
                                                    <label for="title" class="form-label {{ $lang->code == $main_lang->code ? 'required' : '' }}">Ikkinchi nomi</label>
                                                    <input type="text" class="form-control @error('second_name.'.$lang->code) is-invalid @enderror" name="second_name[{{ $lang->code }}]" value="{{ old('second_name.'.$lang->code) ?? ($educationalProgram->second_name[$lang->code] ?? '') }}" id="title" placeholder="Ikkinchi nomi...">
                                                    @error('second_name.'.$lang->code)
                                                    <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                    @enderror
                                                </div>
                                                <div class="form-group">
                                                    <label for="desc" class="form-label">Ikkinchi tavsif</label>
                                                    <textarea name="second_description[{{ $lang->code }}]" id="desc" cols="30" rows="10" class="form-control @error('second_description.'.$lang->code) is-invalid @enderror ckeditor" name="second_description[{{ $lang->code }}]" placeholder="Ikkinchi tavsif...">{{ old('secon_description.'.$lang->code)?? $educationalProgram->second_description[$lang->code] ?? '' }}</textarea>
                                                    @error('second_description.'.$lang->code)
                                                    <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                    @enderror
                                                </div>
                                                <div class="form-group">
                                                    <label for="title" class="form-label {{ $lang->code == $main_lang->code ? 'required' : '' }}">Uchinchi nomi</label>
                                                    <input type="text" class="form-control @error('title.'.$lang->code) is-invalid @enderror" name="third_name[{{ $lang->code }}]" value="{{ old('third_name.'.$lang->code) ?? ($educationalProgram->third_name[$lang->code] ?? '') }}" id="title" placeholder="Uchinchi nomi...">
                                                    @error('third_name.'.$lang->code)
                                                    <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                    @enderror
                                                </div>
                                                <div class="form-group">
                                                    <label for="desc" class="form-label">Uchinchi tavsif</label>
                                                    <textarea name="third_description[{{ $lang->code }}]" id="desc" cols="30" rows="10" class="form-control @error('third_description.'.$lang->code) is-invalid @enderror ckeditor" name="third_description[{{ $lang->code }}]" placeholder="Uchinchi tavsif...">{{ old('third_description.'.$lang->code)?? $educationalProgram->third_description[$lang->code] ?? '' }}</textarea>
                                                    @error('third_description.'.$lang->code)
                                                    <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                    @enderror
                                                </div>
                                                <div class="form-group">
                                                    <label for="title" class="form-label {{ $lang->code == $main_lang->code ? 'required' : '' }}">Til</label>
                                                    <input type="text" class="form-control @error('lang.'.$lang->code) is-invalid @enderror" name="lang[{{ $lang->code }}]" value="{{ old('lang.'.$lang->code) ?? $educationalProgram->lang[$lang->code]  ?? ''}}" id="title" placeholder="Til...">
                                                    @error('lang.'.$lang->code)
                                                    <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                    @enderror
                                                </div>
                                                <div class="form-group">
                                                    <label for="title" class="form-label {{ $lang->code == $main_lang->code ? 'required' : '' }}">Manzil</label>
                                                    <input type="text" class="form-control @error('map.'.$lang->code) is-invalid @enderror" name="map[{{ $lang->code }}]" value="{{ old('lang.'.$lang->code)?? $educationalProgram->map[$lang->code] ?? '' }}" id="title" placeholder="Manzil...">
                                                    @error('map.'.$lang->code)
                                                    <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                    @enderror
                                                </div>
                                                <div class="form-group">
                                                    <label for="title" class="form-label {{ $lang->code == $main_lang->code ? 'required' : '' }}">Taʼlim shakli</label>
                                                    <input type="text"  class="form-control @error('map.'.$lang->code) is-invalid @enderror" name="form_education[{{ $lang->code }}]" value="{{ old('form_education.'.$lang->code)?? $educationalProgram->form_education[$lang->code] ?? ''}}" id="title" placeholder="Kunduzgi || Sirtqi...">
                                                    @error('form_education.'.$lang->code)
                                                    <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                    @enderror
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                    @if ($educationalProgram->parent_id != null && $educationalProgram->parent_id != null)
                                        <div class="form-group">
                                            <label for="parent_id" class="form-label">Yuqori menyu</label>
                                            <select class="form-select @error('parent_id') is-invalid @enderror" id="parent_id" name="parent_id">
                                                @foreach ($educationalPrograms as $key => $item)
                                                    <option value="{{ $item->id }}" {{ old('parent_id', $educationalProgram->parent_id) == $item->id ? 'selected' : '' }}>
                                                        {{ $item->name[$main_lang->code] ?? '' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('parent_id')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    @endif
                                    @if ($educationalProgram->parent_id != null && $educationalProgram->parent_id != null)
                                        <div class="form-group">
                                            <label for="employs" class="form-label">Xodim</label>
                                            <select class="form-control mb-4 @error('employs') is-invalid @enderror" data-choices='{"removeItemButton": true}' multiple name="employs[]">
                                                @foreach ($employ as $key => $item)
                                                    <option value="{{ $item->id }}" {{ (old('employs') || $educationalProgram->employs ? in_array($item->id, (old('employs') ?? $educationalProgram->employs()->pluck('employ_id')->toArray())) : '') ? 'selected' : '' }}>{{ $item->first_name[$main_lang->code] ?? '' }}</option>
                                                @endforeach
                                            </select>
                                            @error('employs')
                                            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                            @enderror
                                        </div>
                                    @endif
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
                                @if ($educationalProgram->parent_id != null && $educationalProgram->parent_id != null)
                                    <div class="form-group">
                                        <label for="date" class="form-label">Boshlanish sanasi</label>
                                        <input type="text" id="date" name="date" class="form-control" value="{{ old('date')?? $educationalProgram->date ?? null }}" placeholder="{{ date('d-m-Y') }}" data-flatpickr='{"dateFormat": "d-m-Y"}' />
                                    </div>
                                    <div class="form-group">
                                        <label for="date" class="form-label">Tugash sanasi</label>
                                        <input type="text" id="date" name="sirtqi_date" class="form-control" value="{{ old('sirtqi_date')?? $educationalProgram->sirtqi_date ?? null }}" placeholder="{{ date('d-m-Y') }}" data-flatpickr='{"dateFormat": "d-m-Y"}' />
                                    </div>
                                    <div class="form-group">
                                        <label for="date" class="form-label">icon</label>
                                        <input type="file" id="icon" name="icon" class="form-control" value="{{ old('icon')?? $educationalProgram->icon ?? null }}" placeholder="" />
                                    </div>
                                    <div class="form-group">
                                        <label for="date" class="form-label">Fayl</label>
                                        <input type="file" id="date" name="file" class="form-control" value="{{ old('file') ?? $educationalProgram->file }}" placeholder="fayl..."  />
                                    </div>
                                    {{--
                                        Daraja: menyudagi "Bakalavriat" va "Magistratura"
                                        bandlari shu maydon boʻyicha ajratiladi. Tanlanmasa
                                        yoʻnalish ikkala roʻyxatda ham koʻrinmaydi.
                                    --}}
                                    <div class="form-group">
                                        <label for="level" class="form-label">Daraja</label>
                                        <select class="form-select" id="level" name="level">
                                            <option value="">— tanlanmagan —</option>
                                            <option value="bachelor" @selected(old('level', $educationalProgram->level) === 'bachelor')>Bakalavriat</option>
                                            <option value="master" @selected(old('level', $educationalProgram->level) === 'master')>Magistratura</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="date" class="form-label">Oʻqish muddati</label>
                                        <input type="text" id="date" name="education_years" class="form-control" value="{{ old('education_years') ?? $educationalProgram->education_years ?? null }}" placeholder="Oʻqish muddati..."  />
                                    </div>
                                    <div class="form-group">
                                        <label for="map" class="form-label">Youtobe  link (<code>iframe</code>)</label>
                                        <textarea name="yt_link" id="map" cols="4" rows="4" class="form-control @error('yt_link') is-invalid @enderror" name="yt_link">{{ old('yt_link') ??  $educationalProgram->yt_link ?? null  }}</textarea>
                                        @error('yt_link')
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="date" class="form-label">Manzil</label>
                                        <input type="text" id="date" name="slug" class="form-control" value="{{ old('slug') ?? $educationalProgram->slug }}" />
                                    </div>

                                    <div class="form-group">
                                        <label for="subtitle" class="form-label">Tartib</label>
                                        <input type="text" class="form-control " name="order" value="{{ old('order') ?? $educationalProgram->order }}" id="subtitle" placeholder="Tartib...">
                                        @error('order')
                                        <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="date" class="form-label">Kunduzgi taʼlim narxi</label>
                                        <input type="text" id="date" name="daytime" class="form-control" value="{{ old('daytime') ?? $educationalProgram->daytime ?? null }}" placeholder="Kunduzgi taʼlim narxi..."/>
                                    </div>
                                    <div class="form-group">
                                        <label for="part_time" class="form-label">Sirtqi taʼlim narxi</label>
                                        <input type="text" id="part_time" name="part_time" class="form-control" value="{{ old('part_time') ?? $educationalProgram->part_time}}" placeholder="Sirtqi taʼlim narxi..."  />
                                    </div>
                                @endif

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
        </div>
        </form>
    </div>
@endsection