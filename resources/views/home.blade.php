@extends('layouts.app')

@section('content')

    <div class="header">
        <div class="container-fluid">
            <div class="header-body">
                <h1 class="header-title">
                    Boshqaruv paneli
                </h1>

                <p class="text-muted mb-0 mt-1" style="font-size: .8125rem;">
                    <i class="fe fe-info"></i>
                    Bu yerdan sayt maʼlumotlarini toʻldirasiz. Har bir boʻlim sarlavhasi tagida
                    oʻsha maʼlumot saytning qaysi sahifasida koʻrinishi yozib qoʻyilgan.
                </p>
            </div>
        </div>
    </div>

    <div class="container-fluid">

        @if ($new_appeals > 0)
            <div class="alert alert-warning d-flex align-items-center justify-content-between" role="alert">
                <span>
                    <i class="fe fe-mail"></i>
                    Javob berilmagan murojaatlar: <strong>{{ $new_appeals }}</strong> ta.
                </span>
                <a href="{{ route('appeals.index') }}" class="btn btn-sm btn-warning">Koʻrish</a>
            </div>
        @endif

        {{-- Sayt qanchalik to'ldirilgani. --}}
        <div class="row">
            @foreach ($stats as $stat)
                <div class="col-12 col-lg-6 col-xl-3">
                    <a href="{{ route($stat['route']) }}" class="text-decoration-none">
                        <div class="card">
                            <div class="card-body">
                                <div class="row align-items-center gx-0">
                                    <div class="col">
                                        <h6 class="text-uppercase text-muted mb-2">
                                            {{ $stat['label'] }}
                                        </h6>
                                        <span class="h2 mb-0 text-dark">
                                            {{ $stat['count'] }}
                                        </span>
                                    </div>
                                    <div class="col-auto">
                                        <span class="h2 text-muted mb-0">
                                            <i class="fe {{ $stat['icon'] }}"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>

        {{-- Eng ko'p bajariladigan amallar — yon menyudan qidirmaslik uchun. --}}
        <div class="card">
            <div class="card-header">
                <h4 class="card-header-title">Tez-tez bajariladigan amallar</h4>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @php
                        $actions = [
                            ['Yangilik qoʻshish', 'posts.create', 'fe-file-plus', 'Saytning /news sahifasida chiqadi'],
                            ['Fotoalbom qoʻshish', 'works.create', 'fe-image', 'Fotogalereyaga yangi albom'],
                            ['Sahifa matnini tahrirlash', 'dynamic-menus.index', 'fe-edit', 'Menyudagi sahifalar matni'],
                            ['Menyuni oʻzgartirish', 'menus.index', 'fe-menu', 'Saytning yuqori menyusi'],
                            ['Xodim qoʻshish', 'employs.create', 'fe-user-plus', 'Rahbariyat va kafedra xodimlari'],
                            ['Hujjat yuklash', 'documents.create', 'fe-file', 'Meʼyoriy hujjatlar boʻlimi'],
                            ['Boʻsh ish oʻrni', 'vacancies.create', 'fe-briefcase', 'Vakansiyalar sahifasi'],
                            ['Sayt maʼlumotlari', 'site_infos.index', 'fe-settings', 'Logotip, aloqa, rekvizitlar'],
                        ];
                    @endphp

                    @foreach ($actions as [$label, $route, $icon, $note])
                        <div class="col-12 col-md-6 col-xl-3">
                            <a href="{{ route($route) }}"
                               class="d-block p-3 border rounded h-100 text-decoration-none text-dark">
                                <div class="mb-1">
                                    <i class="fe {{ $icon }}"></i>
                                    <strong>{{ $label }}</strong>
                                </div>
                                <small class="text-muted">{{ $note }}</small>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Qisqa yo'riqnoma: yangi sahifa qanday qo'shiladi. --}}
        <div class="card">
            <div class="card-header">
                <h4 class="card-header-title">Saytga yangi sahifa qanday qoʻshiladi</h4>
            </div>
            <div class="card-body">
                <ol class="mb-0 ps-3">
                    <li class="mb-2">
                        <strong>Menyular</strong> boʻlimidan yangi band qoʻshasiz —
                        nomi va manzilini yozasiz. Qaysi menyu ostida turishini
                        “Yuqori menyu” maydonidan tanlaysiz.
                    </li>
                    <li class="mb-2">
                        <strong>Sahifalar</strong> boʻlimiga oʻtib, oʻsha menyu bandiga
                        sahifa biriktirasiz.
                    </li>
                    <li class="mb-2">
                        <strong>Sahifa koʻrinishi</strong>ni tanlaysiz:
                        <em>bitta sahifa</em> (sarlavha va matn),
                        <em>kartochkalar</em> (har biri alohida sahifada ochiladi) yoki
                        <em>fayllar roʻyxati</em> (yuklab olish uchun).
                    </li>
                    <li>
                        Matnni har bir til uchun alohida yozasiz. Word hujjatidan
                        nusxa koʻchirib qoʻysangiz, formatlash va rasmlar saqlanadi.
                    </li>
                </ol>
            </div>
        </div>

    </div>

@endsection
