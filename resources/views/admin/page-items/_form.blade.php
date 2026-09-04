{{--
    Sahifa yozuvi formasi. Maydonlar sahifaning ko'rinishiga qarab
    ko'rsatiladi — admin faqat kerakligini ko'radi.
--}}
@php
    $item = $item ?? null;
    $layout = $page->layout;
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
                            {{ $layout === 'files' ? 'Fayl nomi' : 'Sarlavha' }}
                        </label>
                        <input type="text" name="title[{{ $lang->code }}]" class="form-control"
                               value="{{ old('title.'.$lang->code, data_get($item?->title, $lang->code)) }}">
                    </div>

                    @if ($layout === 'cards')
                        <div class="form-group">
                            <label class="form-label">Qoʻshimcha sarlavha</label>
                            <input type="text" name="subtitle[{{ $lang->code }}]" class="form-control"
                                   value="{{ old('subtitle.'.$lang->code, data_get($item?->subtitle, $lang->code)) }}">
                        </div>
                    @endif

                    <div class="form-group">
                        <label class="form-label">
                            {{ $layout === 'files' ? 'Izoh' : 'Qisqacha matn' }}
                        </label>
                        <input type="text" name="desc[{{ $lang->code }}]" class="form-control"
                               value="{{ old('desc.'.$lang->code, data_get($item?->text, $lang->code)) }}">
                        <small class="form-text text-muted">
                            {{ $layout === 'files'
                                ? 'Fayl nomi ostida bir qatorda koʻrinadi.'
                                : 'Kartochkada koʻrinadigan qisqa matn.' }}
                        </small>
                    </div>

                    @if ($layout === 'cards')
                        <div class="form-group">
                            <label class="form-label">Asosiy matn</label>
                            <textarea name="body[{{ $lang->code }}]" rows="10"
                                      class="form-control ckeditor">{{ old('body.'.$lang->code, data_get($item?->body, $lang->code)) }}</textarea>
                            <small class="form-text text-muted">
                                Kartochka bosilganda ochiladigan sahifaning matni. Rasm, video va
                                havolalarni shu yerga qoʻyish mumkin.
                            </small>
                        </div>
                    @endif

                </div>
            @endforeach
        </div>

        <hr>

        @if ($layout === 'files')
            <div class="row">
                <div class="col-12 col-md-6">
                    <div class="form-group">
                        <label class="form-label">Sana</label>
                        <input type="date" name="date" class="form-control"
                               value="{{ old('date', optional($item?->date)->format('Y-m-d') ?: now()->format('Y-m-d')) }}">
                        <small class="form-text text-muted">
                            Saytda fayllar shu sana boʻyicha yillarga guruhlanadi.
                        </small>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="form-group">
                        <label class="form-label {{ $item?->file ? '' : 'required' }}">Fayl</label>
                        <input type="file" name="document" class="form-control">
                        <small class="form-text text-muted">
                            {{ $fileTypes }} — {{ $fileMaxMb }} MB gacha.
                            @if ($item?->file)
                                <br>Hozirgi fayl:
                                <a href="{{ url('/upload/files/' . $item->file) }}" target="_blank">{{ $item->file }}</a>
                                — yangisini yuklamasangiz oʻzgarmaydi.
                            @endif
                        </small>
                    </div>
                </div>
            </div>
        @endif

        @if ($layout === 'cards')
            <div class="row">
                <div class="col-12 col-md-6">
                    <div class="form-group">
                        <label class="form-label">Sana</label>
                        <input type="date" name="date" class="form-control"
                               value="{{ old('date', optional($item?->date)->format('Y-m-d')) }}">
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="form-group">
                        <label class="form-label">Video havolasi</label>
                        <input type="text" name="video" class="form-control"
                               value="{{ old('video', $item?->video) }}"
                               placeholder="https://www.youtube.com/watch?v=…">
                    </div>
                </div>
            </div>
        @endif

        @if ($layout === 'single')
            <div class="row">
                <div class="col-12 col-md-6">
                    <div class="form-group">
                        <label class="form-label">Ikonka nomi</label>
                        <input type="text" name="icon" class="form-control"
                               value="{{ old('icon', $item?->icon) }}" placeholder="Target, BookOpen, Globe…">
                        <small class="form-text text-muted">
                            Boʻsh qoldirilsa boʻlim ikonkasiz chiziladi.
                        </small>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="form-group">
                        <label class="form-label">Havola</label>
                        <input type="text" name="link" class="form-control"
                               value="{{ old('link', $item?->link) }}">
                    </div>
                </div>
            </div>
        @endif

        <div class="row">
            <div class="col-12 col-md-6">
                <div class="form-group">
                    <label class="form-label">Tartib</label>
                    <input type="number" name="order" class="form-control" min="0"
                           value="{{ old('order', $item?->order ?? 0) }}">
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="form-group">
                    <label class="form-label d-block">Holati</label>
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" name="active" value="1"
                               id="active" {{ old('active', $item?->active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="active">Saytda koʻrsatilsin</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">
                {{ $layout === 'files' ? 'Muqova rasmi' : 'Rasmlar' }}
            </label>
            <div class="dropzone" id="dropzone"></div>
            <input type="hidden" name="dropzone_images" id="dropzone_images"
                   value="{{ old('dropzone_images', $item?->image) }}">
            <small class="form-text text-muted">
                {{ $layout === 'files'
                    ? 'Hujjat skani yoki muqova. Shart emas — boʻlmasa ikonka koʻrsatiladi.'
                    : 'Birinchi rasm muqova boʻladi, qolganlari galereyaga tushadi.' }}
            </small>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary">Saqlash</button>
            <a href="{{ route('page-items.index', $page->id) }}" class="btn btn-white">Bekor qilish</a>
        </div>

    </div>
</div>

@section('scripts')
    <script>
        Dropzone.autoDiscover = false;

        // Yuklangan rasm nomlari vergul bilan yashirin maydonda saqlanadi.
        (function () {
            var input = document.getElementById('dropzone_images');
            var names = (input.value || '').split(',').filter(Boolean);

            function sync() {
                input.value = names.join(',');
            }

            new Dropzone('div#dropzone', {
                url: "{{ url('/admin/upload_from_dropzone') }}",
                paramName: 'file',
                headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')},
                addRemoveLinks: true,
                maxFiles: {{ $layout === 'files' ? 1 : 12 }},
                maxFilesize: 10,
                dictDefaultMessage: 'Rasmni shu yerga tashlang',
                success: function (file, response) {
                    file.serverName = response.file_name;
                    names.push(response.file_name);
                    sync();
                },
                removedfile: function (file) {
                    var index = names.indexOf(file.serverName);
                    if (index !== -1) {
                        names.splice(index, 1);
                        sync();
                    }
                    file.previewElement.remove();
                }
            });
        })();
    </script>
@endsection
