{{-- Bitta ish joyi qatori. $i — indeks, $row — qiymatlar (yangi qatorda boʻsh). --}}
<div class="border rounded p-2" data-assignment-row>
    @if(!empty($row['id']))
        <input type="hidden" name="assignments[{{ $i }}][id]" value="{{ $row['id'] }}">
    @endif

    <select name="assignments[{{ $i }}][department_id]" class="form-select form-select-sm mb-2" required>
        <option value="">— boʻlim —</option>
        @foreach($grouped as $typeName => $items)
            <optgroup label="{{ $typeName }}">
                @foreach($items as $department)
                    <option value="{{ $department->id }}" @selected((string) ($row['department_id'] ?? '') === (string) $department->id)>
                        {{ $department->name[$main_lang->code] ?? $department->id }}
                    </option>
                @endforeach
            </optgroup>
        @endforeach
    </select>

    <div class="d-flex gap-2 align-items-center">
        <select name="assignments[{{ $i }}][position_id]" class="form-select form-select-sm" required>
            <option value="">— lavozim —</option>
            @foreach($positions as $position)
                <option value="{{ $position->id }}" @selected((string) ($row['position_id'] ?? '') === (string) $position->id)>
                    {{ $position->name[$main_lang->code] ?? $position->id }}
                </option>
            @endforeach
        </select>

        <input type="number" min="0" name="assignments[{{ $i }}][order]" value="{{ $row['order'] ?? 0 }}"
               class="form-control form-control-sm" style="width: 72px;" title="Tartib (kichigi oldinda)">

        <input type="hidden" name="assignments[{{ $i }}][active]" value="0">
        <label class="form-check form-switch m-0" title="Saytda koʻrinadi">
            <input type="checkbox" class="form-check-input" name="assignments[{{ $i }}][active]" value="1"
                   @checked((int) ($row['active'] ?? 1) === 1)>
        </label>

        <button type="button" class="btn btn-sm btn-outline-danger" data-assignment-remove title="Olib tashlash">✕</button>
    </div>
</div>
