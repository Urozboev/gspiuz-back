{{--
    Modal xabar formasi — qo'shish va tahrirlash uchun umumiy.
    $popup berilmasa, yangi yozuv yaratiladi.
--}}
@php
    $popup = $popup ?? null;
@endphp

<div class="card">
    <div class="card-body">

        {{-- Har bir til uchun alohida ichki bo'lim. --}}
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
                            Sarlavha
                        </label>
                        <input type="text" name="title[{{ $lang->code }}]" class="form-control"
                               value="{{ old('title.'.$lang->code, data_get($popup?->title, $lang->code)) }}"
                               placeholder="Masalan: Mustaqillik bayrami muborak!">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Matn</label>
                        <textarea name="desc[{{ $lang->code }}]" rows="6"
                                  class="form-control ckeditor">{{ old('desc.'.$lang->code, data_get($popup?->desc, $lang->code)) }}</textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Tugma yozuvi</label>
                        <input type="text" name="action_label[{{ $lang->code }}]" class="form-control"
                               value="{{ old('action_label.'.$lang->code, data_get($popup?->action_label, $lang->code)) }}"
                               placeholder="Masalan: Batafsil oʻqish">
                        <small class="form-text text-muted">
                            Boʻsh qoldirilsa, saytda standart yozuv koʻrsatiladi.
                        </small>
                    </div>

                </div>
            @endforeach
        </div>

        <hr>

        <div class="row">
            <div class="col-12 col-md-6">
                <div class="form-group">
                    <label class="form-label">Qaysi kundan koʻrinsin</label>
                    <input type="date" name="starts_at" class="form-control"
                           value="{{ old('starts_at', optional($popup?->starts_at)->format('Y-m-d')) }}">
                    <small class="form-text text-muted">
                        Boʻsh qoldirilsa — darhol koʻrina boshlaydi.
                    </small>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="form-group">
                    <label class="form-label">Qaysi kungacha</label>
                    <input type="date" name="ends_at" class="form-control"
                           value="{{ old('ends_at', optional($popup?->ends_at)->format('Y-m-d')) }}">
                    <small class="form-text text-muted">
                        Shu kundan keyin xabar oʻzi yoʻqoladi. Boʻsh qoldirilsa — muddatsiz.
                    </small>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Havola</label>
            <input type="text" name="url" class="form-control"
                   value="{{ old('url', $popup?->url) }}"
                   placeholder="https://gspi.uz/news/...">
            <small class="form-text text-muted">
                Xabar bosilganda ochiladigan sahifa. Shart emas.
            </small>
        </div>

        <div class="form-group">
            <label class="form-label">Tartib</label>
            <input type="number" name="order" class="form-control" min="0"
                   value="{{ old('order', $popup?->order ?? 0) }}">
            <small class="form-text text-muted">
                Bir nechta xabar boʻlsa, kichik raqamlisi birinchi koʻrsatiladi.
            </small>
        </div>

        <div class="form-check form-switch mb-2">
            <input class="form-check-input" type="checkbox" name="active" value="1"
                   id="active" {{ old('active', $popup?->active ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="active">Faol</label>
        </div>

        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="action" value="1"
                   id="action" {{ old('action', $popup?->action ?? false) ? 'checked' : '' }}>
            <label class="form-check-label" for="action">Havola tugmasi koʻrsatilsin</label>
        </div>

        <hr>

        <div class="form-group">
            <label class="form-label">Rasm</label>
            <div class="dropzone" id="dropzone"></div>
            <input type="hidden" name="dropzone_images[]" id="dropzone_images"
                   value="{{ old('dropzone_images.0', $popup?->logo) }}">
            <small class="form-text text-muted">
                Bayram tabrigi uchun rasm. Shart emas.
            </small>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary">Saqlash</button>
            <a href="{{ route($route_name.'.index') }}" class="btn btn-white">Bekor qilish</a>
        </div>

    </div>
</div>

@section('scripts')
    <script>
        // Rasm yuklash — boshqa bo'limlardagi bilan bir xil tartib.
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
