@extends('layouts.app')

@section('content')

    <h1 class="text-uppercase mt-5">Services</h1>

    <d class="row mb-3">
        <div class="col-6">
            <div class="headcrumbs d-flex">
                <a href="{{ route('services.index') }}" class="d-flex text-uppercase me-2" style="opacity:25%">Services</a> >> <a class="d-flex text-uppercase ms-2">show</a>
            </div>
        </div>
        <div class="col-6">
            <div class="btns d-flex justify-content-end">
                <a href="{{ route('services.service_processes.create', ['service' => $service]) }}" class="btn btn-success text-white">Bosqich qoʻshish</a>
                <a href="{{ route('services.service_images.create', ['service' => $service]) }}" class="btn btn-success text-white ms-3">Rasm qoʻshish</a>
            </div>
        </div>
    </d>
    

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <div class="col-6">
            <form action="{{ route('services.update', ['service' => $service]) }}" method="post" enctype='multipart/form-data'>
                @csrf
                @method('put')
                <div class="card border-0 shadow components-section">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Service</h4>
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
                                        <div class="col-12">
                                            <!-- Form -->
                                            <div class="mb-4">
                                                <label for="email">Sarlavha</label>
                                                <input type="text" class="form-control" name="title[{{ $language->small }}]" placeholder="title" value="{{ old('title.'.$language->small, $service->title[$language->small]) }}">
                                            </div>
                                            <div class="my-4">
                                                <label for="textarea">Tavsif</label>
                                                <textarea class="form-control ckeditor" placeholder="Tavsifni kiriting..." id="textarea" rows="4" name="desc[{{ $language->small }}]">{{ old('desc.'.$language->small, $service->desc[$language->small]) }}</textarea>
                                            </div>
                                            <!-- End of Form -->
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="main_img" class="form-label">Asosiy rasm</label>
                                    <input class="form-control" type="file" id="main_img" name="main_img">
                                </div>
                            </div>
                        </div>
                        <button class="btn btn-success text-white px-5" type="submit" style="padding:12px">Saqlash</button>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-6">
            <div class="card border-0 shadow mb-4">
                <div class="card-body">
                    <h4 class="card-title mb-4">Processes</h4>
                    <div class="table-responsive">
                        <table class="table table-centered table-nowrap mb-0 rounded">
                            <thead class="thead-light">
                            <tr>
                                <th class="border-0 rounded-start">#</th>
                                <th class="border-0">Sarlavha</th>
                                <th class="border-0">Tavsif</th>
                                <th class="border-0 rounded-end">Amallar</th>
                            </tr>
                            </thead>
                            <tbody>
                            @isset($service_processes)
                            @foreach($service_processes as $service_process)
                                <!-- Item -->
                                <tr>
                                    <td><a href="#" class="text-primary fw-bold">{{ $loop->iteration }}</a></td>
                                    <td class="fw-bold">{{ $service_process->title['en'] }}</td>
                                    <td>{!! $service_process->desc['en'] ?? '--' !!}</td>
                                    <td>
                                        <div class="actions d-flex">
                                            <form class="" action="{{ route('services.service_processes.destroy', ['service' => $service, 'service_process' => $service_process]) }}" method="post">
                                                @csrf
                                                @method('delete')
                                                <button type="submit" class="text-danger bg-transparent border-0 p-0 m-0 mb-3 fw-bolder"><i class="fa-solid fa-trash"></i></button>
                                            </form>
                                            <a href="{{ route('services.service_processes.edit', ['service' => $service, 'service_process' => $service_process]) }}" class="text-info fw-bolder ms-3"><i class="fa-solid fa-pen"></i></a>
                                        </div>
                                    </td>
                                </tr>
                                <!-- End of Item -->
                            @endforeach
                            @endisset
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow mb-4">
                <div class="card-body">
                    <h4 class="card-title mb-4">Images</h4>
                    <div class="table-responsive">
                        <table class="table table-centered table-nowrap mb-0 rounded">
                            <thead class="thead-light">
                            <tr>
                                <th class="border-0 rounded-start">#</th>
                                <th class="border-0">Rasm</th>
                                <th class="border-0 rounded-end">Amallar</th>
                            </tr>
                            </thead>
                            <tbody>
                            @isset($service_images)
                            @foreach($service_images as $service_image)
                                <!-- Item -->
                                <tr>
                                    <td><a class="text-primary fw-bold">{{ $loop->iteration }}</a></td>
                                    <td>
                                        @isset($service_image->img)
                                        <img src="{{ asset($service_image->img) }}" alt="" style="max-width: 250px">
                                        @else
                                        --
                                        @endisset
                                    </td>
                                    <td>
                                        <div class="actions d-flex">
                                            <form class="" action="{{ route('services.service_images.destroy', ['service' => $service, 'service_image' => $service_image]) }}" method="post">
                                                @csrf
                                                @method('delete')
                                                <button type="submit" class="text-danger bg-transparent border-0 p-0 m-0 mb-3 fw-bolder"><i class="fa-solid fa-trash"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <!-- End of Item -->
                            @endforeach
                            @endisset
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
