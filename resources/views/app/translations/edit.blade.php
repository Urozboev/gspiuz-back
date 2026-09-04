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

                </div>
            </div> <!-- / .row -->
        </div> <!-- / .header-body -->
        @include('app.components.breadcrumb', [
        'datas' => [
        [
        'active' => false,
        'url' => route($route_name.'.show', [$route_parameter => $group->id]),
        'name' => $title,
        'disabled' => false
        ],
        [
        'active' => true,
        'url' => '',
        'name' => $group->title,
        'disabled' => false
        ]
        ]
        ])
    </div>
</div> <!-- / .header -->

<!-- CARDS -->
<div class="container-fluid">
    <div class="card mt-4">
        <form action="{{ route($route_name.'.store') }}" method="post">
            @csrf
            <input type="hidden" name="group" value="{{ $group->id }}">
            <div class="card-body">
                <div class="btns d-flex justify-content-end mb-4">
                    <a href="" class="btn btn-danger me-3">Bekor qilish</a>
                    <button class="btn btn-success">Saqlash</button>
                </div>
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
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($group->translations[0]))
                            @foreach($group->translations as $translation)
                            <tr>
                                <th scope="row" style="width: 100px">{{ $loop->iteration }}</th>
                                <td><input type="text" value="{{ $translation->key }}" class="form-control" name="translations[{{$loop->iteration}}][key]" placeholder="key..."></td>
                                @foreach($languages as $language)
                                <td><input type="text" value="{{ $translation->val[$language->code] ?? '' }}" class="form-control" name="translations[{{$loop->parent->iteration}}][val][{{$language->code}}]" placeholder="{{ $language->code }}..."></td>
                                @endforeach
                            </tr>
                            @endforeach
                            @else
                            @for ($i=1; $i<6; $i++) <tr>
                                <th scope="row" style="width: 100px">{{ $i }}</th>
                                <td>
                                    <div class="input-group input-group-merge input-group-reverse">
                                        <input class="form-control" name="translations[{{$i}}][key]" type="text" aria-label="Input group reverse" aria-describedby="inputGroupReverse">
                                        <div class="input-group-text pe-0" id="inputGroupReverse">
                                            <span>{{ $group->sub_text }}.</span>
                                        </div>
                                    </div>
                                    <!-- <input type="text" class="form-control" name="translations[{{$i}}][key]" placeholder="key..."> -->
                                </td>
                                @foreach($languages as $language)
                                <td><input type="text" class="form-control" name="translations[{{$i}}][val][{{$language->code}}]" placeholder="{{ $language->code }}..."></td>
                                @endforeach
                                </tr>
                                @endfor
                                @endif
                        </tbody>
                    </table>
                    <button id="add_item" type="button" class="btn btn-info w-100 my-4">Yana qoʻshish</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')

<script>
    let add_item = document.getElementById('add_item');
    let tbody = document.querySelector('tbody');
    var ids = document.querySelectorAll('th');
    var last_id = parseInt(document.querySelectorAll('th')[ids.length - 1].innerText) + 1;

    add_item.addEventListener('click', function() {

        tbody.insertAdjacentHTML('beforeend', '<tr>' +
            '<th scope="row" style="width: 100px">' + last_id + '</th>' +
            '<td>' +
            '<div class="input-group input-group-merge input-group-reverse">' +
                '<input class="form-control" name="translations[' + last_id + '][key]" type="text" aria-label="Input group reverse" aria-describedby="inputGroupReverse">' +
                '<div class="input-group-text pe-0" id="inputGroupReverse">' +
                    '<span>{{ $group->sub_text }}.</span>' +
                '</div>' +
            '</div>' +
            '</td>' +
            '@foreach($languages as $language)' +
            '<td><input type="text" class="form-control" name="translations[' + last_id + '][val][{{$language->code}}]" placeholder="{{ $language->code }}..."></td>' +
            '@endforeach' +
            '</tr>');

        last_id = parseInt(last_id) + 1;

    });
</script>

@endsection