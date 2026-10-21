{{--
    Xodimning ish joylari: qaysi boʻlimda, qaysi lavozimda.

    Bitta xodim bir nechta boʻlimda boʻlishi mumkin — masalan prorektor
    ham institut kengashi aʼzosi. U bitta yozuv boʻlib qoladi, bu yerda
    faqat qatorlar qoʻshiladi.

    $assignments — mavjud tayinlovlar (EmployMeta), yaratishda boʻsh.
--}}
@php
    $rows = old('assignments')
        ? collect(old('assignments'))
        : $assignments->map(fn ($meta) => [
            'id' => $meta->id,
            'department_id' => $meta->department_id,
            'position_id' => $meta->position_id,
            'order' => $meta->order,
            'active' => $meta->active,
        ]);

    $grouped = $departments->groupBy(fn ($d) => $d->structureType->name[$main_lang->code] ?? 'Boshqa');
@endphp

<div class="card">
    <div class="card-header">
        <h4 class="card-header-title">Ish joylari</h4>
        <button type="button" class="btn btn-sm btn-outline-primary" id="assignment-add">+ Qoʻshish</button>
    </div>
    <div class="card-body">
        <p class="text-muted small mb-3">
            Xodim saytda shu boʻlimlarning sahifasida koʻrinadi.
            Bir nechta boʻlimda ishlasa — har biri uchun qator qoʻshing,
            xodimni qayta yaratmang.
        </p>

        <input type="hidden" name="assignments_present" value="1">

        <div id="assignment-list" class="d-flex flex-column gap-2">
            @foreach($rows as $i => $row)
                @include('admin.employs._assignment_row', ['i' => $i, 'row' => $row])
            @endforeach
        </div>

        <div id="assignment-empty" class="text-warning small {{ $rows->isEmpty() ? '' : 'd-none' }}">
            Hech qaysi boʻlimga biriktirilmagan — xodim saytda koʻrinmaydi.
        </div>
    </div>
</div>

<template id="assignment-template">
    @include('admin.employs._assignment_row', ['i' => '__INDEX__', 'row' => []])
</template>

<script>
    (function () {
        var list = document.getElementById('assignment-list');
        var empty = document.getElementById('assignment-empty');
        var template = document.getElementById('assignment-template');
        var counter = {{ $rows->count() + 100 }};

        function refresh() {
            empty.classList.toggle('d-none', list.children.length > 0);
        }

        document.getElementById('assignment-add').addEventListener('click', function () {
            var html = template.innerHTML.replace(/__INDEX__/g, String(counter++));
            list.insertAdjacentHTML('beforeend', html);
            refresh();
        });

        list.addEventListener('click', function (event) {
            var button = event.target.closest('[data-assignment-remove]');
            if (!button) {
                return;
            }
            button.closest('[data-assignment-row]').remove();
            refresh();
        });
    })();
</script>
