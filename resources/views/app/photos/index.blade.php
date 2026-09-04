@extends('layouts.app')

@section('content')

    <h1 class="text-uppercase mb-4">Rasmlar</h1>

    <a href="{{ route('photos.create') }}" class="btn btn-success mb-3 text-white">Rasm qoʻshish</a>

    <div class="card border-0 shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-centered table-nowrap mb-0 rounded">
                    <thead class="thead-light">
                    <tr>
                        <th class="border-0 rounded-start">#</th>
                        <th class="border-0">Tavsif</th>
                        <th class="border-0">Rasm</th>
                        <th class="border-0 rounded-end">Amallar</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($photos as $photo)
                        <!-- Item -->
                        <tr>
                            <td><a href="#" class="text-primary fw-bold">{{ ($photos ->currentpage()-1) * $photos ->perpage() + $loop->index + 1 }}</a></td>
                            <td>{{ $photo->desc['ru'] }}</td>
                            <td>
                                <img src="{{ asset($photo->img) }}" alt="" style="max-width: 250px">
                            </td>
                            <td>
                                <div class="actions d-flex flex-column">
                                    <form class="" action="{{ route('photos.destroy', ['photo' => $photo->id]) }}" method="post">
                                        @csrf
                                        @method('delete')
                                        <button type="submit" class="text-danger bg-transparent border-0 p-0 m-0 mb-3 fw-bolder">delete</button>
                                    </form>
                                    <a href="{{ route('photos.edit', ['photo' => $photo->id]) }}" class="text-info fw-bolder">tahrirlash</a>
                                </div>
                            </td>
                        </tr>
                        <!-- End of Item -->
                    @endforeach
                    </tbody>
                </table>
                <div class="mt-4">
                    {!! $photos->links() !!}
                </div>
            </div>
        </div>
    </div>

@endsection
