@extends('layouts.app')

@section('content')
<div class="header">
    <div class="container-fluid">
        <div class="header-body">
            <div class="row align-items-end">
                <div class="col">
                    <h6 class="header-pretitle">{{ $title }}</h6>
                    <h1 class="header-title">{{ $appeal->ticket }}</h1>
                </div>
                <div class="col-auto">
                    <a class="btn btn-secondary" href="{{ route($route_name.'.index') }}">Ro'yxatga qaytish</a>
                </div>
            </div>
        </div>
        @include('app.components.breadcrumb', [
            'datas' => [
                ['active' => false, 'url' => route($route_name.'.index'), 'name' => $title, 'disabled' => false],
                ['active' => true, 'url' => '', 'name' => $appeal->ticket, 'disabled' => false]
            ]
        ])
    </div>
</div>

<div class="container-fluid">
    <div class="row mt-4">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header"><h4 class="card-header-title">Murojaat matni</h4></div>
                <div class="card-body">
                    <p style="white-space: pre-wrap">{{ $appeal->message }}</p>
                    @if ($appeal->file)
                        <a href="{{ $appeal->fileUrl() }}" target="_blank" class="btn btn-sm btn-outline-primary mt-3">
                            <i class="fe fe-paperclip"></i> Ilova qilingan fayl
                        </a>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h4 class="card-header-title">Javob va holat</h4></div>
                <div class="card-body">
                    <form method="POST" action="{{ route($route_name.'.update', [$route_parameter => $appeal]) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Holati</label>
                            <select name="status" class="form-select">
                                @foreach ($statuses as $key => $labels)
                                    <option value="{{ $key }}" @selected($appeal->status === $key)>{{ $labels['uz'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Rasmiy javob</label>
                            <textarea name="answer" rows="6" class="form-control">{{ old('answer', $appeal->answer) }}</textarea>
                            <small class="form-text text-muted">
                                Bu matn ariza raqami orqali murojaat qiluvchiga ko'rinadi.
                            </small>
                        </div>

                        <button class="btn btn-primary" type="submit">Saqlash</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card">
                <div class="card-header"><h4 class="card-header-title">Murojaatchi</h4></div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr><th style="width: 40%">Turi</th><td>{{ $types[$appeal->type]['uz'] ?? $appeal->type }}</td></tr>
                        <tr><th>F.I.Sh.</th><td>{{ $appeal->name }}</td></tr>
                        <tr><th>Telefon</th><td>{{ $appeal->phone_number ?? '--' }}</td></tr>
                        <tr><th>Elektron pochta</th><td>{{ $appeal->email ?? '--' }}</td></tr>
                        <tr><th>Manzil</th><td>{{ $appeal->address ?? '--' }}</td></tr>
                        <tr><th>Kelgan sana</th><td>{{ $appeal->created_at->format('d.m.Y H:i') }}</td></tr>
                        <tr><th>Javob sanasi</th><td>{{ $appeal->answered_at ? $appeal->answered_at->format('d.m.Y H:i') : '--' }}</td></tr>
                        <tr><th>IP</th><td>{{ $appeal->ip ?? '--' }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
