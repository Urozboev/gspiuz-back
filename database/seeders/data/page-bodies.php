<?php

/**
 * Sahifalarning boshlang'ich matni (`dinamik_menus.text`).
 *
 * Bular o'rinbosar: sahifa bo'sh turmasligi va "Sahifa matni" maydoni
 * ishlayotganini ko'rish uchun. Haqiqiy matn admin paneldan kiritiladi va
 * seeder uni ustidan yozmaydi — matn faqat sahifa bo'sh bo'lsa qo'yiladi.
 *
 * Kalit — menyu slug'i.
 */

$note = [
    'uz' => '<p><em>Bu matn admin panelning “Sahifalar” boʻlimidan tahrirlanadi.</em></p>',
    'ru' => '<p><em>Этот текст редактируется в разделе «Страницы» админ-панели.</em></p>',
    'en' => '<p><em>This text is edited in the “Pages” section of the admin panel.</em></p>',
];

return [

    'research' => [
        'uz' => '<p>Institutda ilmiy faoliyat uch yoʻnalishda olib boriladi: ilmiy kengashlar '
            . 'ishi, ilmiy-metodik nashrlar va laboratoriya tadqiqotlari. Professor-oʻqituvchilar '
            . 'hamda magistrantlar respublika va xalqaro anjumanlarda ishtirok etadi.</p>'
            . '<p>Ilmiy ishlar natijalari institut nashrlarida eʼlon qilinadi va oʻquv jarayoniga '
            . 'joriy etiladi.</p>' . $note['uz'],
        'ru' => '<p>Научная деятельность института ведётся по трём направлениям: работа научных '
            . 'советов, научно-методические издания и лабораторные исследования. Преподаватели и '
            . 'магистранты участвуют в республиканских и международных конференциях.</p>'
            . '<p>Результаты научных работ публикуются в изданиях института и внедряются в '
            . 'учебный процесс.</p>' . $note['ru'],
        'en' => '<p>Research at the institute follows three directions: the work of academic '
            . 'councils, methodology publications and laboratory studies. Staff and postgraduate '
            . 'students take part in national and international conferences.</p>'
            . '<p>Research results are published in the institute\'s journals and applied in '
            . 'teaching.</p>' . $note['en'],
    ],

    'dormitory' => [
        'uz' => '<p>Talabalar turar joyi institut kampusida joylashgan. Joylar navbat asosida '
            . 'ajratiladi; ijtimoiy himoyaga muhtoj talabalar qonunchilikda belgilangan tartibda '
            . 'birinchi navbatda joylashtiriladi.</p>'
            . '<p>Turar joy uchun toʻlov miqdori har oʻquv yili boshida belgilanadi va eʼlon '
            . 'qilinadi. Batafsil maʼlumot uchun institut yoshlar bilan ishlash boʻlimiga '
            . 'murojaat qiling.</p>' . $note['uz'],
        'ru' => '<p>Студенческое общежитие расположено на территории кампуса. Места '
            . 'предоставляются в порядке очереди; студенты, нуждающиеся в социальной защите, '
            . 'заселяются в первую очередь в порядке, установленном законодательством.</p>'
            . '<p>Размер оплаты за проживание определяется и объявляется в начале каждого '
            . 'учебного года.</p>' . $note['ru'],
        'en' => '<p>The student residence is located on the institute campus. Places are '
            . 'allocated in order of priority; students in need of social protection are housed '
            . 'first, as established by law.</p>'
            . '<p>The accommodation fee is set and announced at the start of each academic '
            . 'year.</p>' . $note['en'],
    ],

    'international' => [
        'uz' => '<p>Institut xorijiy oliygohlar va taʼlim tashkilotlari bilan hamkorlik qiladi. '
            . 'Hamkorlik memorandumlari asosida qoʻshma taʼlim dasturlari amalga oshiriladi, '
            . 'talaba va professor-oʻqituvchilar almashinuvi tashkil etiladi.</p>'
            . '<p>Institut xodimlari xalqaro grant dasturlarida ishtirok etadi.</p>' . $note['uz'],
        'ru' => '<p>Институт сотрудничает с зарубежными вузами и образовательными организациями. '
            . 'На основе меморандумов реализуются совместные образовательные программы, '
            . 'организуется обмен студентами и преподавателями.</p>'
            . '<p>Сотрудники института участвуют в международных грантовых программах.</p>' . $note['ru'],
        'en' => '<p>The institute cooperates with foreign universities and educational '
            . 'organisations. Joint programmes are delivered under memoranda of understanding, '
            . 'and exchanges are arranged for students and staff.</p>'
            . '<p>Institute staff take part in international grant programmes.</p>' . $note['en'],
    ],

    'anti-corruption' => [
        'uz' => '<p>Institutda korrupsiyaviy xatarlarni aniqlash, baholash va oldini olish '
            . 'boʻyicha ichki nazorat tizimi joriy etilgan. Qabul, baholash, grant taqsimoti va '
            . 'xaridlar jarayonlari belgilangan tartibda, ochiq tarzda amalga oshiriladi.</p>'
            . '<p>Korrupsiya holatlari va manfaatlar toʻqnashuvi haqida komplayens-nazorat '
            . 'boʻlimiga xabar berish mumkin.</p>' . $note['uz'],
        'ru' => '<p>В институте внедрена система внутреннего контроля по выявлению, оценке и '
            . 'предупреждению коррупционных рисков. Приём, оценивание, распределение грантов и '
            . 'закупки проводятся открыто, в установленном порядке.</p>'
            . '<p>О случаях коррупции и конфликта интересов можно сообщить в отдел '
            . 'комплаенс-контроля.</p>' . $note['ru'],
        'en' => '<p>The institute operates an internal control system for identifying, assessing '
            . 'and preventing corruption risks. Admissions, assessment, grant allocation and '
            . 'procurement are carried out openly and by the established procedure.</p>'
            . '<p>Cases of corruption or conflict of interest can be reported to the compliance '
            . 'control unit.</p>' . $note['en'],
    ],

    'murojaat' => [
        'uz' => '<p>Institut faoliyatiga oid taklif, ariza va shikoyatlaringizni shu sahifadagi '
            . 'shakl orqali yuborishingiz mumkin. Murojaat turini tanlang: rektorga, tyutorga '
            . 'yoki komplayens-nazorat boʻlimiga.</p>'
            . '<p>Har bir murojaatga raqam beriladi — uning holatini shu raqam boʻyicha '
            . 'kuzatib borishingiz mumkin.</p>' . $note['uz'],
        'ru' => '<p>Предложения, заявления и жалобы по деятельности института можно направить '
            . 'через форму на этой странице. Выберите тип обращения: ректору, тьютору или в '
            . 'отдел комплаенс-контроля.</p>'
            . '<p>Каждому обращению присваивается номер — по нему можно отслеживать '
            . 'состояние.</p>' . $note['ru'],
        'en' => '<p>Proposals, applications and complaints about the institute can be submitted '
            . 'through the form on this page. Choose the type of appeal: to the rector, to a '
            . 'tutor, or to the compliance control unit.</p>'
            . '<p>Every appeal receives a reference number that can be used to track its '
            . 'status.</p>' . $note['en'],
    ],
];
