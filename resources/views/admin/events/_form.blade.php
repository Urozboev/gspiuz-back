{{--
    Tadbir formasi — qo'shish va tahrirlash uchun umumiy.
    $event berilmasa, yangi yozuv yaratiladi.
--}}
@php
    $event = $event ?? null;
@endphp

<div class="card">
    <div class="card-body">

        <ul class="nav nav-tabs mb-3" role="tablist">
            @foreach ($langs as $lang)
                <li class="nav-item">
                    <a class="nav-link {{ $loop->first ? 'active' : '' }}"
                       data-bs-toggle="tab" href="#lang-{{ $lang->code }}" role="tab">
                        {{ $lang->title }}
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="tab-content">
            @foreach ($langs as $lang)
                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="lang-{{ $lang->code }}">

                    <div class="form-group">
                        <label class="form-label {{ $lang->code == $main_lang->code ? 'required' : '' }}">
                            Tadbir nomi
                        </label>
                        <input type="text" name="title[{{ $lang->code }}]" class="form-control"
                               value="{{ old('title.'.$lang->code, data_get($event?->title, $lang->code)) }}"
                               placeholder="Masalan: Xalqaro ilmiy-amaliy anjuman">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Tavsif</label>
                        <textarea name="desc[{{ $lang->code }}]" rows="5"
                                  class="form-control ckeditor">{{ old('desc.'.$lang->code, data_get($event?->desc, $lang->code)) }}</textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Oʻtkaziladigan joy</label>
                        <input type="text" name="location[{{ $lang->code }}]" class="form-control"
                               value="{{ old('location.'.$lang->code, data_get($event?->location, $lang->code)) }}"
                               placeholder="Masalan: Katta majlislar zali">
                    </div>

                </div>
            @endforeach
        </div>

        <hr>

        <div class="row">
            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label class="form-label required">Boshlanish sanasi</label>
                    <input type="date" name="date" class="form-control"
                           value="{{ old('date', optional($event?->date)->format('Y-m-d')) }}">
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label class="form-label">Tugash sanasi</label>
                    <input type="date" name="end_date" class="form-control"
                           value="{{ old('end_date', optional($event?->end_date)->format('Y-m-d')) }}">
                    <small class="form-text text-muted">
                        Koʻp kunlik tadbir uchun. Bir kunlik boʻlsa boʻsh qoldiring.
                    </small>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label class="form-label">Boshlanish vaqti</label>
                    <input type="text" name="time" class="form-control"
                           value="{{ old('time', $event?->time) }}" placeholder="14:00">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-md-6">
                <div class="form-group">
                    <label class="form-label">Tadbir turi</label>
                    <input type="text" name="type" class="form-control"
                           value="{{ old('type', $event?->type) }}"
                           placeholder="konferensiya, uchrashuv, bayram…">
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="form-group">
                    <label class="form-label">Batafsil havola</label>
                    <input type="text" name="url" class="form-control"
                           value="{{ old('url', $event?->url) }}"
                           placeholder="https://…">
                    <small class="form-text text-muted">Shart emas.</small>
                </div>
            </div>
        </div>

        <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" name="active" value="1"
                   id="active" {{ old('active', $event?->active ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="active">Saytda koʻrsatilsin</label>
        </div>

        <div class="form-group">
            <label class="form-label">Rasm</label>
            <div class="dropzone" id="dropzone"></div>
            <input type="hidden" name="dropzone_images[]" id="dropzone_images"
                   value="{{ old('dropzone_images.0', $event?->img) }}">
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary">Saqlash</button>
            <a href="{{ route($route_name.'.index') }}" class="btn btn-white">Bekor qilish</a>
        </div>

    </div>
</div>

@section('scripts')
    <script>
        Dropzone.autoDiscover = false;

        new Dropzone('div#dropzone', {
            url: "{{ url('/admin/upload_from_dropzone') }}",
            paramName: 'file',
            headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')},
            addRemoveLinks: true,
            maxFiles: 1,
            maxFilesize: 10,
            dictDefaultMessage: 'Rasmni shu yerga tashlang',
            success: function (file, response) {
                document.getElementById('dropzone_images').value = response.file_name;
            },
            removedfile: function (file) {
                document.getElementById('dropzone_images').value = '';
                file.previewElement.remove();
            }
        });
    </script>
@endsection
