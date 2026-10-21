{{--
    Boʻlim, fakultet va kafedra formasi — yaratish va tahrirlashda umumiy.

    $departament — tahrirlashda mavjud yozuv, yaratishda null.
--}}
@php
    $d = $departament ?? null;
    $value = fn (string $field, string $lang) => old($field . '.' . $lang) ?? ($d?->{$field}[$lang] ?? null);
    $selectedType = (int) (old('structure_type_id') ?? $d?->structure_type_id ?? ($type ?? 0));
    $selectedParent = old('parent_id') ?? $d?->parent_id;
@endphp

<ul class="nav nav-tabs" role="tablist">
    @foreach($langs as $lang)
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $loop->first ? 'active' : '' }}" data-bs-toggle="tab"
                    data-bs-target="#dep-{{ $lang->code }}" type="button" role="tab">
                {{ $lang->title }}
            </button>
        </li>
    @endforeach
</ul>

<div class="tab-content">
    @foreach($langs as $lang)
        @php $isMain = $lang->code === $main_lang->code; @endphp
        <div class="tab-pane mt-3 fade {{ $loop->first ? 'show active' : '' }}" id="dep-{{ $lang->code }}" role="tabpanel">

            <div class="form-group">
                <label class="form-label {{ $isMain ? 'required' : '' }}">Nomi</label>
                <input type="text" {{ $isMain ? 'required' : '' }}
                       class="form-control @error('name.'.$lang->code) is-invalid @enderror"
                       name="name[{{ $lang->code }}]" value="{{ $value('name', $lang->code) }}"
                       placeholder="Masalan: Aniq fanlar kafedrasi">
                @error('name.'.$lang->code)
                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Boʻlim haqida</label>
                <div class="form-text mb-2">
                    Ixtiyoriy. Tarixi, vazifalari, yutuqlari — yangilik yozgandek erkin.
                    Word'dan nusxa olib qoʻyish, rasm va jadval qoʻshish mumkin.
                </div>
                <textarea class="form-control ckeditor" name="description[{{ $lang->code }}]" rows="8">{{ $value('description', $lang->code) }}</textarea>
            </div>

            <div class="row">
                <div class="col-md-7 form-group">
                    <label class="form-label">Manzil</label>
                    <input type="text" class="form-control" name="address[{{ $lang->code }}]"
                           value="{{ $value('address', $lang->code) }}" placeholder="Masalan: 2-bino, 214-xona">
                </div>
                <div class="col-md-5 form-group">
                    <label class="form-label">Qabul vaqti</label>
                    <input type="text" class="form-control" name="work_time[{{ $lang->code }}]"
                           value="{{ $value('work_time', $lang->code) }}" placeholder="Dushanba–Juma, 9:00–17:00">
                </div>
            </div>
        </div>
    @endforeach
</div>

<hr class="my-4">

<div class="row">
    <div class="col-md-6 form-group">
        <label class="form-label required">Tuzilma turi</label>
        <select class="form-select @error('structure_type_id') is-invalid @enderror" name="structure_type_id" required>
            <option value="">— tanlang —</option>
            @foreach ($StructureTypes as $item)
                <option value="{{ $item->id }}" @selected($selectedType === (int) $item->id)>{{ $item->name[$main_lang->code] ?? $item->id }}</option>
            @endforeach
        </select>
        <div class="form-text">Saytda qaysi roʻyxatda chiqishini belgilaydi.</div>
    </div>

    <div class="col-md-6 form-group">
        <label class="form-label">Qaysi fakultetga tegishli</label>
        <select class="form-select" name="parent_id" data-choices='{"searchEnabled": true}'>
            <option value="">— yoʻq —</option>
            @foreach ($departaments as $item)
                <option value="{{ $item->id }}" @selected((string) $selectedParent === (string) $item->id)>{{ $item->name[$main_lang->code] ?? $item->id }}</option>
            @endforeach
        </select>
        <div class="form-text">Kafedra uchun — u qaysi fakultet tarkibida.</div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 form-group">
        <label class="form-label">Telefon</label>
        <input type="text" class="form-control" name="phone" value="{{ old('phone') ?? $d?->phone }}" placeholder="+998 67 225 40 60">
    </div>
    <div class="col-md-6 form-group">
        <label class="form-label">Email</label>
        <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') ?? $d?->email }}" placeholder="kafedra@gspi.uz">
        @error('email')
            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
        @enderror
    </div>
</div>

<div class="form-group">
    <label class="form-label">Rasm</label>
    @if($d?->image)
        <div class="d-flex align-items-center gap-3 mb-2">
            <img src="{{ asset('upload/images/' . $d->image) }}" alt="" style="height: 72px; border-radius: 6px; object-fit: cover;">
            <label class="form-check m-0">
                <input class="form-check-input" type="checkbox" name="delete_image" value="1">
                <span class="form-check-label">Rasmni oʻchirish</span>
            </label>
        </div>
        <div class="form-text mb-2">Yangisini yuklasangiz, eskisining oʻrniga qoʻyiladi.</div>
    @endif
    <div class="dropzone dropzone-single" id="dropzone"></div>
</div>

<div class="row">
    <div class="col-md-4 form-group">
        <label class="form-label">Holati</label>
        <select name="active" class="form-select">
            <option value="1" @selected((int) (old('active') ?? $d?->active ?? 1) === 1)>Saytda koʻrinadi</option>
            <option value="0" @selected((int) (old('active') ?? $d?->active ?? 1) === 0)>Yashirin</option>
        </select>
    </div>
    <div class="col-md-3 form-group">
        <label class="form-label">Tartib</label>
        <input type="number" min="0" class="form-control" name="order" value="{{ old('order') ?? $d?->order ?? 0 }}">
        <div class="form-text">Kichigi oldinda.</div>
    </div>
    <div class="col-md-5 form-group">
        <label class="form-label">Sahifa manzili</label>
        <input type="text" class="form-control" name="slug" value="{{ old('slug') ?? $d?->slug }}" placeholder="Boʻsh qoldirsangiz nomdan yasaladi">
    </div>
</div>

<input type="hidden" name="code" value="{{ old('code') ?? $d?->code }}">
