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

                                <div class="tab-content" id="myTabContent">
                                        <div class="form-group">
                                            <label for="employ_id" class="form-label">Xodim </label>
                                            <select  class="form-select @error('employ_id') is-invalid @enderror" id="employ_id" data-choices='{"": true}' name="employ_id">
                                                @foreach ($employ as $key => $item)
                                                    <option value="{{ $item->id }}" {{ old('employ_id') == $item->id ? 'selected' : '' }}>{{ $item->first_name[$main_lang->code] }}</option>
                                                @endforeach
                                            </select>
                                            @error('employ_id')
                                            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                            @enderror
                                        </div>
                                        <div class="form-group">
                                            <label for="department_id" class="form-label">Fakultet va kafedralar</label>
                                            <select class="form-select @error('department_id') is-invalid @enderror" id="department_id" data-choices='{"": true}' name="department_id">
                                                @foreach ($departaments as $key => $item)
                                                    <option value="{{ $item->id }}" {{ old('department_id') == $item->id ? 'selected' : '' }}>{{ $item->name[$main_lang->code] }}</option>
                                                @endforeach
                                            </select>
                                            @error('department_id')
                                            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                            @enderror
                                        </div>
                                    <div class="form-group">
                                            <label for="position_id" class="form-label">Lavozim</label>
                                            <select class="form-select @error('position_id') is-invalid @enderror" id="position_id" data-choices='{"": true}' name="position_id">
                                                @foreach ($position as $key => $item)
                                                    <option value="{{ $item->id }}" {{ old('position_id') == $item->id ? 'selected' : '' }}>{{ $item->name[$main_lang->code] }}</option>
                                                @endforeach
                                            </select>
                                            @error('position_id')
                                            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                            @enderror
                                        </div>
                                    <div class="form-group">
                                            <label for="parent_id" class="form-label">Shtat turi</label>
                                            <select class="form-select @error('employ_staff_id') is-invalid @enderror" id="employ_staff_id" data-choices='{"": true}' name="employ_staff_id">
                                                @foreach ($employStaff as $key => $item)
                                                    <option value="{{ $item->id }}" {{ old('employ_staff_id') == $item->id ? 'selected' : '' }}>{{ $item->name[$main_lang->code] }}</option>
                                                @endforeach
                                            </select>
                                            @error('employ_staff_id')
                                            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                            @enderror
                                        </div>
                                    <div class="form-group">
                                            <label for="employ_form_id" class="form-label">Ish shakli</label>
                                            <select class="form-select @error('employ_form_id') is-invalid @enderror" id="employ_form_id" data-choices='{"": true}' name="employ_form_id">
                                                @foreach ($employForm as $key => $item)
                                                    <option value="{{ $item->id }}" {{ old('employ_form_id') == $item->id ? 'selected' : '' }}>{{ $item->name[$main_lang->code] }}</option>
                                                @endforeach
                                            </select>
                                            @error('employ_form_id')
                                            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                            @enderror
                                        </div>
                                    <div class="form-group">
                                            <label for="parent_id" class="form-label">Bandlik turi</label>
                                            <select class="form-select @error('employ_type_id') is-invalid @enderror" id="structure_type_id" data-choices='{"": true}' name="employ_type_id">
                                                @foreach ($employType as $key => $item)
                                                    <option value="{{ $item->id }}" {{ old('employ_type_id') == $item->id ? 'selected' : '' }}>{{ $item->name[$main_lang->code] }}</option>
                                                @endforeach
                                            </select>
                                            @error('employ_type_id')
                                            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label for="menu_id" class="form-label">Holati</label>
                                            <select name="active" class="form-select">
                                                <option value="1">Faol</option>
                                                <option value="0">Nofaol</option>
                                            </select>
                                        </div>
                                    <div class="form-group">
                                        <label for="date" class="form-label">Sana</label>
                                        <input type="text" id="date" name="contrakt_date" class="form-control" value="{{ old('contrakt_date') }}" placeholder="{{ date('d-m-Y') }}" data-flatpickr='{"dateFormat": "d-m-Y"}' />
                                    </div>
                                    <div class="form-group">
                                        <label for="subtitle" class="form-label">Tartib</label>
                                        <input type="number" class="form-control " name="order" value="{{ old('order') }}" id="subtitle" placeholder="Tartib...">
                                        @error('order')
                                        <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="subtitle" class="form-label">Shartnoma raqami</label>
                                        <input type="text" class="form-control " name="contrakt_number" value="{{ old('contrakt_number') }}" id="subtitle" placeholder="Shartnoma raqami...">
                                        @error('contrakt_number')
                                        <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
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