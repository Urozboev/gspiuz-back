{{-- Bitta ish joyi qatori. $i — indeks, $row — qiymatlar (yangi qatorda boʻsh). --}}
<div class="border rounded p-3" data-assignment-row>
    @if(!empty($row['id']))
        <input type="hidden" name="assignments[{{ $i }}][id]" value="{{ $row['id'] }}">
    @endif

    <label class="form-label small text-muted mb-1">Boʻlim</label>
    <select name="assignments[{{ $i }}][department_id]" class="form-select form-select-sm mb-2" required>
        <option value="">— tanlang —</option>
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

    <label class="form-label small text-muted mb-1">Lavozimi shu boʻlimda</label>
    <select name="assignments[{{ $i }}][position_id]" class="form-select form-select-sm mb-2" required>
        <option value="">— tanlang —</option>
        @foreach($positions as $position)
            <option value="{{ $position->id }}" @selected((string) ($row['position_id'] ?? '') === (string) $position->id)>
                {{ $position->name[$main_lang->code] ?? $position->id }}
            </option>
        @endforeach
    </select>

    <div class="d-flex align-items-center gap-3 flex-wrap">
        <div class="d-flex align-items-center gap-2">
            <label class="small text-muted m-0" for="assignment-order-{{ $i }}">Tartib</label>
            <input type="number" min="0" id="assignment-order-{{ $i }}" name="assignments[{{ $i }}][order]" value="{{ $row['order'] ?? 0 }}"
                   class="form-control form-control-sm" style="width: 72px;">
        </div>

        <input type="hidden" name="assignments[{{ $i }}][active]" value="0">
        <label class="form-check form-switch m-0 d-flex align-items-center gap-2">
            <input type="checkbox" class="form-check-input m-0" name="assignments[{{ $i }}][active]" value="1"
                   @checked((int) ($row['active'] ?? 1) === 1)>
            <span class="small">Saytda koʻrinadi</span>
        </label>

        <button type="button" class="btn btn-sm btn-link text-danger ms-auto p-0" data-assignment-remove>Olib tashlash</button>
    </div>
</div>
