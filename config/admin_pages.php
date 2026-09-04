<?php

/**
 * Admin paneldagi har bir bo'lim saytning qaysi sahifasini to'ldiradi.
 *
 * Bu izoh bo'lim sarlavhasi tagida bitta qator bo'lib chiqadi, masalan:
 * "Bu maʼlumot saytning Fotogalereya (/gallery) sahifasida koʻrinadi."
 *
 * Kalit — kontrollerdagi `$route_name`. Yangi bo'lim qo'shilganda shu yerga
 * bitta qator qo'shish kifoya; xaritada bo'lmagan bo'lim uchun izoh
 * ko'rsatilmaydi.
 *
 *   'page' — saytdagi sahifa nomi
 *   'path' — saytdagi manzil (null bo'lsa faqat nom ko'rsatiladi)
 *   'note' — qo'shimcha tushuntirish (ixtiyoriy)
 */
return [

    'posts' => [
        'page' => 'Yangiliklar',
        'path' => '/news',
        'note' => 'Bosh sahifadagi yangiliklar lentasida ham koʻrinadi.',
    ],
    'posts_categories' => [
        'page' => 'Yangiliklar',
        'path' => '/news',
        'note' => 'Yangiliklarni turkumlarga ajratish uchun. Saytda filtr sifatida chiqadi.',
    ],
    'works' => [
        'page' => 'Fotogalereya',
        'path' => '/gallery',
        'note' => 'Har bir albom alohida sahifada ochiladi.',
    ],
    'services' => [
        'page' => 'Ilmiy jurnallar',
        'path' => '/journals',
    ],
    'questions' => [
        'page' => 'Koʻp beriladigan savollar',
        'path' => '/faq',
    ],
    'vacancies' => [
        'page' => 'Boʻsh ish oʻrinlari',
        'path' => '/vacancies',
    ],
    'documents' => [
        'page' => 'Meʼyoriy hujjatlar',
        'path' => '/documents',
    ],
    'document_categories' => [
        'page' => 'Meʼyoriy hujjatlar',
        'path' => '/documents',
        'note' => 'Hujjatlarni turkumlarga ajratish uchun.',
    ],
    'employs' => [
        'page' => 'Rahbariyat, tyutorlar va kafedra xodimlari',
        'path' => '/leadership',
        'note' => 'Bitta xodim bir nechta sahifada koʻrinishi mumkin — bu uning lavozimiga bogʻliq.',
    ],
    'employ_meta' => [
        'page' => 'Xodimlar sahifalari',
        'path' => '/leadership',
        'note' => 'Xodimni boʻlim va lavozim bilan bogʻlaydi. Aynan shu bogʻlanish xodim qaysi sahifada chiqishini belgilaydi. Bir xodim bir nechta boʻlimda boʻlishi mumkin — har bir boʻlim uchun alohida yozuv qoʻshing, u ikkala sahifada ham oʻz lavozimi bilan koʻrinadi.',
    ],
    'departaments' => [
        'page' => 'Fakultetlar va kafedralar',
        'path' => '/faculties',
    ],
    'positions' => [
        'page' => 'Xodimlar sahifalari',
        'path' => '/leadership',
        'note' => 'Lavozim nomlari roʻyxati — xodim kartochkasida koʻrinadi.',
    ],
    'partners' => [
        'page' => 'Hamkorlar',
        'path' => '/partners',
    ],
    'students' => [
        'page' => 'Iqtidorli talabalar',
        'path' => '/talented-students',
    ],
    'student_counts' => [
        'page' => 'Raqamlarda institut',
        'path' => '/about',
        'note' => 'Bosh sahifadagi statistika bloki.',
    ],
    'kampuses' => [
        'page' => 'Talabalar turar joyi',
        'path' => '/dormitory',
    ],
    'banners' => [
        'page' => 'Bosh sahifa',
        'path' => '/',
        'note' => 'Bosh sahifaning yuqorisidagi katta rasmlar.',
    ],
    'certificates' => [
        'page' => 'Litsenziya va sertifikatlar',
        'path' => '/about',
    ],
    'appeals' => [
        'page' => 'Murojaatlar',
        'path' => '/murojaat',
        'note' => 'Saytdan yuborilgan murojaatlar shu yerga tushadi. Bu roʻyxat faqat oʻqish uchun.',
    ],
    'applications' => [
        'page' => 'Saytdan yuborilgan soʻrovnomalar',
        'path' => null,
        'note' => 'Foydalanuvchilar toʻldirgan shakllar. Bu roʻyxat faqat oʻqish uchun.',
    ],
    'feedbacks' => [
        'page' => 'Fikrlar',
        'path' => '/',
    ],
    'educational-programs' => [
        'page' => 'Taʼlim yoʻnalishlari',
        'path' => '/educational-programs',
    ],
    'entrance-requirements' => [
        'page' => 'Qabul',
        'path' => '/admissions',
    ],
    'menus' => [
        'page' => 'Saytning yuqori menyusi',
        'path' => null,
        'note' => 'Bu yerdagi tartib va nomlar saytning menyusida aynan shunday koʻrinadi. Manzil (masalan /citizen_appeal) sahifa qaysi manzilda ochilishini belgilaydi — band manzillar roʻyxati forma ichida koʻrsatilgan.',
    ],
    'dynamic-menus' => [
        'page' => 'Menyudagi sahifalar',
        'path' => null,
        'note' => 'Har bir menyu bandiga sahifa biriktiriladi. Sahifa koʻrinishini uch turdan tanlaysiz: matn, kartochkalar yoki fayllar roʻyxati.',
    ],
    'site_infos' => [
        'page' => 'Sayt boʻylab: sarlavha, logotip, aloqa maʼlumotlari va rekvizitlar',
        'path' => null,
        'note' => 'Bu maʼlumotlar saytning pastki qismida va aloqa sahifasida koʻrinadi.',
    ],
    'langs' => [
        'page' => 'Sayt tillari',
        'path' => null,
        'note' => 'Qaysi tillar mavjudligini belgilaydi. Asosiy tilni oʻchirib boʻlmaydi.',
    ],
    'translations' => [
        'page' => 'Sayt boʻylab tarjimalar',
        'path' => null,
    ],
    'users' => [
        'page' => 'Admin panelga kira oladigan xodimlar',
        'path' => null,
        'note' => 'Saytda koʻrinmaydi.',
    ],
    'events' => [
        'page' => 'Tadbirlar kalendari',
        'path' => '/events',
        'note' => 'Bosh sahifadagi "yaqin tadbirlar" blokida ham koʻrinadi. Oʻtgan tadbirlar oʻchirilmaydi — kalendar orqaga varaqlanadi.',
    ],
    'popups' => [
        'page' => 'Saytga kirilganda ochiladigan modal xabar',
        'path' => '/',
        'note' => 'Bayram tabriklari va muhim eʼlonlar. Sana oraligʻi berilsa, xabar oʻsha kunlarda oʻzi paydo boʻlib, keyin oʻzi yoʻqoladi.',
    ],
    'advertisements' => [
        'page' => 'Bosh sahifadagi reklama bloki',
        'path' => '/',
    ],
    'skills' => [
        'page' => 'Taʼlim yoʻnalishlari',
        'path' => '/educational-programs',
        'note' => 'Yoʻnalish kartochkasida koʻrsatiladigan koʻnikmalar.',
    ],
    'activities' => [
        'page' => 'Taʼlim yoʻnalishlari',
        'path' => '/educational-programs',
    ],

    // Ma'lumotnomalar — kamdan-kam tegiladigan ro'yxatlar.
    'employ_staff' => [
        'page' => 'Maʼlumotnoma',
        'path' => null,
        'note' => 'Xodim shtatda yoki shtatdan tashqari ekanini belgilaydigan roʻyxat. Odatda oʻzgartirilmaydi.',
    ],
    'employ_forms' => [
        'page' => 'Maʼlumotnoma',
        'path' => null,
        'note' => 'Ish shakli (doimiy, muddatli). Odatda oʻzgartirilmaydi.',
    ],
    'employ_types' => [
        'page' => 'Maʼlumotnoma',
        'path' => null,
        'note' => 'Bandlik turi (asosiy ish joyi, oʻrindoshlik). Odatda oʻzgartirilmaydi.',
    ],
    'stracture_types' => [
        'page' => 'Maʼlumotnoma',
        'path' => null,
        'note' => 'Boʻlim turi (rahbariyat, fakultet, kafedra). Odatda oʻzgartirilmaydi.',
    ],
    'additional_functions' => [
        'page' => 'Texnik sozlamalar',
        'path' => null,
        'note' => 'Google va Yandex hisoblagichlari. Saytda koʻrinmaydi.',
    ],
];
