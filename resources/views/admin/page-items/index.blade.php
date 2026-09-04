@extends('layouts.app')

@section('content')

    <div class="header">
        <div class="container-fluid">
            <div class="header-body">
                <div class="row align-items-end">
                    <div class="col">
                        <h1 class="header-title">{{ $title }}</h1>

                        <p class="text-muted mb-0 mt-1" style="font-size: .8125rem;">
                            <i class="fe fe-info"></i>
                            @if ($page->layout === 'files')
                                Bu yerga yuklangan fayllar saytda yillar boʻyicha guruhlanib koʻrsatiladi.
                            @elseif ($page->layout === 'cards')
                                Har bir kartochka saytda alohida sahifada ochiladi.
                            @else
                                Sahifa matnidan tashqari koʻrsatiladigan boʻlimlar.
                            @endif
                            Sahifa manzili: <code>/{{ $page->menu->slug ?? '' }}</code>
                        </p>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('dynamic-menus.index') }}" class="btn btn-white">Sahifalar</a>
                        <a href="{{ route('page-items.create', $page->id) }}" class="btn btn-primary lift">
                            Qoʻshish
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Nomi</th>
                            @if ($page->layout === 'files')
                                <th>Sana</th>
                                <th>Fayl</th>
                            @elseif ($page->layout === 'cards')
                                <th>Manzil</th>
                                <th>Sana</th>
                            @else
                                <th>Ikonka</th>
                            @endif
                            <th>Holati</th>
                            <th>Tartib</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse ($items as $item)
                            <tr>
                                <td>{{ $item->id }}</td>
                                <td>
                                    <strong>{{ data_get($item->title, $main_lang->code) }}</strong>
                                    @if (data_get($item->text, $main_lang->code))
                                        <br><small class="text-muted">{{ Str::limit(strip_tags(data_get($item->text, $main_lang->code)), 70) }}</small>
                                    @endif
                                </td>

                                @if ($page->layout === 'files')
                                    <td>{{ optional($item->date)->format('d.m.Y') ?: '—' }}</td>
                                    <td>
                                        @if ($item->file)
                                            <a href="{{ url('/upload/files/' . $item->file) }}" target="_blank">Yuklab olish</a>
                                        @else
                                            <span class="text-danger">fayl yoʻq</span>
                                        @endif
                                    </td>
                                @elseif ($page->layout === 'cards')
                                    <td><code>{{ $item->slug }}</code></td>
                                    <td>{{ optional($item->date)->format('d.m.Y') ?: '—' }}</td>
                                @else
                                    <td>{{ $item->icon ?: '—' }}</td>
                                @endif

                                <td>
                                    @if ($item->active)
                                        <span class="text-success fw-bold">Koʻrinadi</span>
                                    @else
                                        <span class="text-muted">Yashirilgan</span>
                                    @endif
                                </td>
                                <td>{{ $item->order }}</td>
                                <td class="text-end">
                                    <a href="{{ route('page-items.edit', [$page->id, $item->id]) }}"
                                       class="btn btn-sm btn-white">Tahrirlash</a>

                                    <form action="{{ route('page-items.destroy', [$page->id, $item->id]) }}"
                                          method="post" class="d-inline"
                                          onsubmit="return confirm('Oʻchirilsinmi?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-white text-danger">Oʻchirish</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    Hozircha yozuv yoʻq. “Qoʻshish” tugmasi orqali boshlang.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{ $items->links() }}
    </div>

@endsection
