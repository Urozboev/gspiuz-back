<div class="mb-3 @error($name) is-invalid @enderror fileUploadBlock">
    <p style="font-weight: 600" class="mb-2">{{ $label }}</p>
    <div class="d-flex flex-wrap previewFiles">
        @foreach (old($name.'_hidden', $datas) as $item)
            <div class="d-flex align-items-center justify-content-center me-2" style="width: 100px; height: 100px; background-color: #eeeeee82; border-radius: 12px; border: 1px dashed #ccc; cursor: pointer; position: relative">
                @if (in_array(pathinfo('/images'.$item)['extension'], ['png', 'jpg', 'jpeg']))
                    <img src="/images/{{ $item }}" alt="" style="height: 100%; width:100%; border-radius: 12px;object-fit: cover;">
                @else
                    <i class="fas fa-file fa-2x" style="color: #29313d;"></i>
                @endif
                <input type="hidden" name="{{ $name }}_hidden[]" value="{{ $item }}">
                <button class="btn btn-danger rmFile" style="position: absolute;top: -7px;padding: 0.15rem 0.55rem;right: -7px;"><i class="fas fa-times"></i></button>
            </div>
        @endforeach
        <div class="d-flex align-items-center justify-content-center openFileDialog" style="width: 100px; height: 100px; background-color: #eeeeee82; border-radius: 12px; border: 1px dashed #ccc; cursor: pointer">
            <i class="fas fa-plus fa-2x" style="color: #29313d;"></i>
        </div>
    </div>
    <input class="form-control fileUploadInput" type="file" style="display: none" name="{{ $name }}">
    @error($name)
    <div class="invalid-feedback">
        {{ $message }}
    </div>
    @enderror
</div>
