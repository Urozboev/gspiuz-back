@extends('layouts.app')

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
                <div class="col-auto">

                    <!-- Button -->
                    <a class="btn btn-primary lift" data-bs-toggle="modal" data-bs-target="#add">
                        Guruh qoʻshish
                    </a>

                </div>
            </div> <!-- / .row -->
        </div> <!-- / .header-body -->
        @include('app.components.breadcrumb', [
        'datas' => [
        [
        'active' => true,
        'url' => '',
        'name' => $title,
        'disabled' => false
        ]
        ]
        ])
    </div>
</div> <!-- / .header -->

<!-- CARDS -->
<div class="container-fluid">
    <div class="search">
        <div class="card">
            <div class="card-body">
                <form action="{{ route($route_name.'.show', [$route_parameter => $group->id]) }}" class="d-flex">
                    <input type="text" class="form-control" name="search" value="{{ $search }}" placeholder="Kiriting...">
                    <button type="submit" class="btn btn-success ms-3" style="width: 300px;">Qidirish</button>
                </form>
            </div>
        </div>
    </div>
    <div class="groups">
        <div class="card">
            <!-- <p class="mb-0 ps-4 pt-3">Guruhlar</p> -->
            <div class="card-body">
                <div class="links">
                    @foreach($translation_groups as $item)
                    <a href="{{ route($route_name.'.show', [$route_parameter => $item->id]) }}" class="btn btn-info me-3 {{ request()->segment(count(request()->segments())) != $item->id ? 'bg-transparent text-info' : '' }}">{{ $item->title }}</a>
                    @endforeach
                </div>
                <a href="{{ route($route_name.'.edit', [$route_parameter => $group->id]) }}" class="d-flex p-1 mt-4" style="width: max-content;text-decoration: underline;"><i class="fe fe-edit-2 d-flex align-items-center justify-content-center" style="width: 20px;height:20px"></i> tahrirlash</a>
            </div>
        </div>
    </div>
    <div class="card mt-4">
        <div class="card-body">
            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Kalit</th>
                            @foreach($languages as $language)
                            <th scope="col">Qiymat ({{ $language->code }})</th>
                            @endforeach
                            <th scope="col"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($translations as $key => $item)
                        <tr>
                            <th scope="row" style="width: 100px">{{ $loop->iteration }}</th>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="input-group-text border-0 bg-transparent p-0">
                                        <span onclick="copy(this)" class="fe fe-copy" style="cursor: pointer;"><span class="text-dark ms-3">{{ $item->translationGroup->sub_text.'.'.$item->key }}</span></span>
                                    </div>
                                </div>
                            </td>
                            @foreach($languages as $language)
                            <td>{{ $item->val[$language->code] ?? '--' }}</td>
                            @endforeach
                            <td style="width: 200px;">
                                <div class="d-flex justify-content-end">
                                    <a class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#edit{{ $item->id }}"><i class="fe fe-edit-2"></i></a>
                                    <a class="btn btn-sm btn-danger ms-3" onclick="var result = confirm('Want to delete?');if (result){event.preventDefault();document.getElementById('delete-form{{ $item->id }}').submit();}"><i class="fe fe-trash"></i></a>
                                    <form action="{{ route($route_name.'.destroy', [$route_parameter => $item]) }}" id="delete-form{{ $item->id }}" method="POST" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- modal for create -->
<!-- Modal -->
<div class="modal fade" id="add" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('translation_groups.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <h2 class="mb-4 fw-bold">Guruh qoʻshish</h2>
                            <div class="form-group">
                                <label for="title" class="form-label required">Nomi</label>
                                <input type="text" required class="form-control @error('title') is-invalid @enderror" name="title" value="{{ old('title') }}" id="title" placeholder="Nomi...">
                                @error('title')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="sub_text" class="form-label required">Qoʻshimcha matn (main.)</label>
                                <input type="text" required class="form-control @error('sub_text') is-invalid @enderror" name="sub_text" value="{{ old('sub_text') }}" id="sub_text" placeholder="Kod...">
                                @error('sub_text')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                            <div class="model-btns d-flex justify-content-end">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Bekor qilish</button>
                                <button type="submit" class="btn btn-primary ms-2">Saqlash</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@foreach($translations as $key => $item)
<!-- modal for edit -->
<!-- Modal -->
<div class="modal fade" id="edit{{ $item->id }}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route($route_name.'.update', [$route_parameter => $item]) }}">
                @csrf
                @method('put')
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <h2 class="mb-4 fw-bold">Tarjimani tahrirlash</h2>
                            <div class="form-group">
                                <label for="key" class="form-label required">Kalit</label>
                                <input type="text" required class="form-control @error('key') is-invalid @enderror" name="key" value="{{ old('key') ?? $item->key }}" id="key" placeholder="Kalit...">
                                @error('key')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                            @foreach($languages as $language)
                            <div class="form-group">
                                <label for="val{{$language->code}}" class="form-label required">Qiymat <span class="text-uppercase">({{$language->code}})</span></label>
                                <input type="text" required class="form-control @error('val') is-invalid @enderror" name="val[{{$language->code}}]" value="{{ old('val.'.$language->code) ?? $item->val[$language->code] ?? '' }}" id="val{{$language->code}}" placeholder="Kod...">
                                @error('val.'.$language->code)
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                            @endforeach
                            <div class="model-btns d-flex justify-content-end">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Bekor qilish</button>
                                <button type="submit" class="btn btn-primary ms-2">Saqlash</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

@section('scripts')

<script>
    function copy(that) {
        var inp = document.createElement('input');
        document.body.appendChild(inp)
        inp.value = '\{\{ translation("' + that.textContent + '") \}\}'
        inp.select();
        document.execCommand('copy', false);
        inp.remove();

        const notyf = new Notyf({
            position: {
                x: 'right',
                y: 'top',
            },
            types: [{
                type: 'success',
                background: '#3366CC',
                icon: {
                    className: 'fe fe-check-circle',
                    tagName: 'span',
                    color: '#fff'
                },
                dismissible: false
            }]
        });
        notyf.open({
            type: 'success',
            message: 'Nusxa olindi'
        });
    }
</script>

@endsection

@endsection