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
                    <a href="{{ route('education_faqs.create', $id) }}" class="btn btn-primary lift">
                        Qoʻshish
                    </a>
                    <!-- Button -->
{{--                    @php--}}
{{--                        $faqCount = \App\Models\EducationFaq::where('educational_program_id', $id)->count();--}}
{{--                    @endphp--}}
{{--                    @if ($faqCount < 5)--}}
{{--                   --}}
{{--                    @endif--}}

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

    <div class="card mt-4">
        <div class="card-body">
            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Sarlavha</th>
                            <th scope="col">Taʼlim dasturi</th>
                            <th scope="col"></th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ($educational_programs as $programItem)
                        <tr>
                            <td>{{ $programItem['menu']->id ?? '-' }}</td>
                            <td><strong>{{ $programItem['menu']->question[$languages->first()->code] ?? 'No Name' }}</strong></td>
                            <td>Yuqori turkum </td>
                            <td>
                                <a href="{{ route('education_faqs.edit', [$programItem['menu']->educational_program_id, $programItem['menu']->id]) }}" class="btn btn-sm btn-info">
                                    <i class="fe fe-edit-2"></i>
                                </a>

                                <a href="#" class="btn btn-sm btn-danger"
                                   onclick="if(confirm('Are you sure?')) { event.preventDefault(); document.getElementById('delete-form-{{ $programItem['menu']->id }}').submit(); }">
                                    <i class="fe fe-trash"></i>
                                </a>
                                <form id="delete-form-{{ $programItem['menu']->id }}" action="{{ route('education_faqs.destroy', $programItem['menu']->id) }}" method="POST" style="display: none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </td>
                        </tr>

                        @if (!empty($programItem['children']))
                            @foreach ($programItem['children'] as $child)
                                <tr>
                                    <td>{{ $child->id ?? '-' }}</td>
                                    <td>&mdash; {{ $child->question[$languages->first()->code] ?? 'No Name' }}</td>
                                    <td>{{ $programItem['menu']->question[$languages->first()->code] ?? 'No Parent' }}</td>
                                    <td>
                                        <a href="{{ route('education_faqs.edit', [$child->educational_program_id, $child->id]) }}" class="btn btn-sm btn-info">
                                            <i class="fe fe-edit-2"></i>
                                        </a>

                                        <a href="#" class="btn btn-sm btn-danger"
                                           onclick="if(confirm('Are you sure?')) { event.preventDefault(); document.getElementById('delete-form-{{ $child->id }}').submit(); }">
                                            <i class="fe fe-trash"></i>
                                        </a>
                                        <form id="delete-form-{{ $child->id }}" action="{{ route('education_faqs.destroy', $child->id) }}" method="POST" style="display: none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>
                                </tr>
                            @endforeach

                            {{-- Bolalar menyusi sahifalash --}}
                            @if ($programItem['child_pagination'] && $programItem['child_pagination']->hasPages())
                                <tr>
                                    <td colspan="4" class="text-center">
                                        {{ $programItem['child_pagination']->appends(request()->query())->links() }}
                                    </td>
                                </tr>
                            @endif
                        @endif
                    @endforeach

                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $count->links() }}
            </div>
        </div>
    </div>
</div>

@endsection
