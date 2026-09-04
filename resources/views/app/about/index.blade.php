@extends('layouts.app')

@section('content')

    <h1 class="text-uppercase mt-5">Biz haqimizda</h1>

    <div class="headcrumbs d-flex mb-3">
        <a href="{{ route('about.index') }}" class="d-flex text-uppercase me-2" style="opacity:25%">Biz haqimizda</a> >> <a class="d-flex text-uppercase ms-2">tahrirlash</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('about.update', ['id' => $about->id]) }}" method="post" enctype='multipart/form-data'>
        @csrf
        <div class="row">
            <div class="col-12 mb-4">
                <div class="card border-0 shadow components-section">
                    <div class="card-body">
                        <nav>
                            <div class="nav nav-tabs border-bottom mb-3" id="nav-tab" role="tablist">
                                @foreach($languages as $language)
                                    <button class="nav-link border-bottom" id="{{ $language->lang }}-tab" data-bs-toggle="tab" data-bs-target="#{{ $language->lang }}" type="button" role="tab" aria-controls="{{ $language->lang }}" aria-selected="true">{{ $language->lang }}</button>
                                @endforeach
                            </div>
                        </nav>
                        <div class="tab-content" id="nav-tabContent">
                            @foreach($languages as $language)
                                <div class="tab-pane fade" id="{{ $language->lang }}" role="tabpanel" aria-labelledby="{{ $language->lang }}-tab">
                                    <div class="row mb-4">
                                        <div class="col-lg-12 col-sm-12">
                                            <!-- Form -->
                                            <div class="my-4">
                                                <label for="textarea">Description 1</label>
                                                <textarea class="form-control ckeditor" placeholder="Tavsifni kiriting..." id="textarea" rows="4" name="desc1[{{ $language->small }}]">{{ old('desc1.'.$language->small) ? old('desc1.'.$language->small) : $about->desc1[$language->small] }}</textarea>
                                            </div>
                                            <div class="my-4">
                                                <label for="textarea">Description 2</label>
                                                <textarea class="form-control ckeditor" placeholder="Tavsifni kiriting..." id="textarea" rows="4" name="desc2[{{ $language->small }}]">{{ old('desc2.'.$language->small) ? old('desc2.'.$language->small) : $about->desc2[$language->small] }}</textarea>
                                            </div>
                                            <div class="my-4">
                                                <label for="textarea">Description 3</label>
                                                <textarea class="form-control ckeditor" placeholder="Tavsifni kiriting..." id="textarea" rows="4" name="desc3[{{ $language->small }}]">{{ old('desc3.'.$language->small) ? old('desc3.'.$language->small) : $about->desc3[$language->small] }}</textarea>
                                            </div>
                                            <div class="mb-4">
                                                <div class="row">
                                                    <div class="col-4">
                                                        <label for="email">Qiymat</label>
                                                        <input type="text" class="form-control mb-3" name="done1[{{ $language->small }}]" value="{{ old('done1.'.$language->small) ? old('done1.'.$language->small) : $about->done1[$language->small] }}" placeholder="title">
                                                        <label for="email">Matn</label>
                                                        <input type="text" class="form-control" name="done1_text[{{ $language->small }}]" value="{{ old('done1_text.'.$language->small) ? old('done1_text.'.$language->small) : $about->done1_text[$language->small] }}" placeholder="title">
                                                    </div>
                                                    <div class="col-4">
                                                        <label for="email">Qiymat</label>
                                                        <input type="text" class="form-control mb-3" name="done2[{{ $language->small }}]" value="{{ old('done2.'.$language->small) ? old('done2.'.$language->small) : $about->done2[$language->small] }}" placeholder="title">
                                                        <label for="email">Matn</label>
                                                        <input type="text" class="form-control" name="done2_text[{{ $language->small }}]" value="{{ old('done2_text.'.$language->small) ? old('done2_text.'.$language->small) : $about->done2_text[$language->small] }}" placeholder="title">
                                                    </div>
                                                    <div class="col-4">
                                                        <label for="email">Qiymat</label>
                                                        <input type="text" class="form-control mb-3" name="done3[{{ $language->small }}]" value="{{ old('done3.'.$language->small) ? old('done3.'.$language->small) : $about->done3[$language->small] }}" placeholder="title">
                                                        <label for="email">Matn</label>
                                                        <input type="text" class="form-control" name="done3_text[{{ $language->small }}]" value="{{ old('done3_text.'.$language->small) ? old('done3_text.'.$language->small) : $about->done3_text[$language->small] }}" placeholder="title">
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- End of Form -->
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="row mb-4">
                            <div class="col-sm-6">
                                <div class="mb-3">
                                    <label for="video">Video</label>
                                    <input type="file" name="video" class="form-control">
                                </div>
                                <div class="mb-3">
                                    @include('app.includes.file_upload', [
                                        'name' => 'images',
                                        'label' => 'Images',
                                        'datas' => [],
                                        'multiple' => true
                                    ])
                                </div>
                            </div>
                        </div>
                        <button class="btn btn-success px-5 text-white" type="submit" style="padding:12px">Saqlash</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

@endsection

@section('scripts')
    <script>
        $(document).on('click', '.openFileDialog', function (e) {
            $(this).parents(".fileUploadBlock").find('input[type=file]').trigger('click');
        });
        $(document).on('click', '.rmFile', function (e) {
            $(this).parent(".d-flex").remove();
        });
        $('.fileUploadInput').on('change', function () {
            var formData = new FormData();
            var hi = $(this).attr("name") + '_hidden[]';
            var pv = $(this).parents(".fileUploadBlock").find('.previewFiles');
            formData.append('file', $(this)[0].files[0]);
            $.ajax({
                method : 'post',
                cache: false,
                contentType: false,
                processData: false,
                url : '/admin/file/upload',
                data : formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success : function (data) {
                    if(data.file_type == 'img'){
                        var fileBlok = `
					<div class="d-flex align-items-center justify-content-center me-2 mb-2" style="width: 100px; height: 100px; background-color: #eeeeee82; border-radius: 12px; border: 1px dashed #ccc; cursor: pointer; position: relative">
						<img src="/images/${data.file_name}" alt="" style="height: 100%; width:100%; border-radius: 12px;object-fit: cover;">
						<input type="hidden" name="${hi}" value="${data.file_name}">
						<button class="btn btn-danger rmFile" style="position: absolute;top: -7px;padding: 0.15rem 0.55rem;right: -7px;"><i class="fas fa-times"></i></button>
					</div>
					`
                    }else{
                        var fileBlok = `
					<div class="d-flex align-items-center justify-content-center me-2 mb-2" style="width: 100px; height: 100px; background-color: #eeeeee82; border-radius: 12px; border: 1px dashed #ccc; cursor: pointer; position: relative">
						<i class="fas fa-file fa-2x" style="color: #29313d;"></i>
						<input type="hidden" name="${hi}" value="${data.file_name}">
						<button class="btn btn-danger rmFile" style="position: absolute;top: -7px;padding: 0.15rem 0.55rem;right: -7px;"><i class="fas fa-times"></i></button>
					</div>
					`
                    }
                    pv.prepend(fileBlok);
                },
                error : function (error) {
                    console.log(error)
                },
            });
        });
    </script>
@endsection
