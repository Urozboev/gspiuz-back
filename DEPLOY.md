# Serverga chiqarish — backend (Laravel)

GSPI sayti **ikkita alohida serverda** ishlaydi:

| Ilova | Nima | Qayerda |
|---|---|---|
| **Frontend** (`gspi-front`) | Saytning oʻzi, `gspi.uz` | ahost.uz hostingi, Next.js (Node.js bor) |
| **Backend** (bu loyiha) | API, admin panel, yuklangan fayllar | Institut serveri, nginx + php-fpm |

Brauzer faqat frontend bilan gaplashadi. Next.js esa backendga **oʻz serveridan** murojaat qiladi — ya'ni maxfiy API prefiksi brauzerga umuman koʻrinmaydi.

```
brauzer  ──HTTPS──▶  gspi.uz (ahost, Next.js)
                          │
                          └──HTTPS──▶  institut serveri (Laravel API)
```

Bu ikki serverli tuzilma uchta narsani majburiy qiladi:

1. **Backend internetdan yetib boradigan boʻlishi kerak** — ahost undan maʼlumot oladi. Demak API endi "ichki" emas.
2. **Backendda HTTPS shart.** Soʻrovlar ochiq internet orqali oʻtadi; sertifikatsiz maxfiy prefiks ham, kontent ham yoʻlda koʻrinadi.
3. **Kirishni ahost IP manzili bilan cheklash kerak** (7-boʻlim). Prefiksning maxfiyligi yagona himoya boʻlib qolmasin.

Admin panel ham backend serverida turadi — unga alohida subdomen orqali kiriladi (masalan `admin.gspi.uz`), `gspi.uz` orqali emas.

Frontend qadamlari alohida hujjatda: `gspi-front/DEPLOY.md`.

## Qaysi tartibda chiqariladi

**Tartib muhim.** Frontend build qilinganda backend manzili (`BACKEND_URL`) `.next/` ichiga **muhrlanadi** — keyin uni oʻzgartirish uchun qayta build qilish kerak boʻladi. Shuning uchun backend va uning subdomeni frontenddan **oldin** tayyor boʻlishi kerak.

| # | Qadam | Qayerda |
|---|---|---|
| 1 | Backendni chiqarish (2–6-boʻlimlar) | Institut serveri |
| 2 | `admin.gspi.uz` DNS yozuvi va sertifikat (7-boʻlim) | ahost DNS paneli |
| 3 | API tashqaridan ishlayotganini tasdiqlash | Istalgan kompyuter |
| 4 | Frontendni **serverning oʻzida** build qilish | ahost |
| 5 | Ahost IP'sini aniqlab, nginx `allow` ga yozish | Institut serveri |

Uchinchi qadam — oʻtish nuqtasi. U bajarilmaguncha frontendni build qilmang:

```bash
curl -s -o /dev/null -w "%{http_code}\n" \
  https://admin.gspi.uz/<API_PREFIKSI>/siteinfo
```

`200` kelsa keyingi qadamga oʻting. Boshqa har qanday javob — avval 7-boʻlimni tugating.

Beshinchi qadam ataylab oxirida: `allow` qoidasi qoʻyilgach faqat ahost kira oladi, ya'ni undan oldin siz oʻzingiz ham tekshira olmay qolasiz.

**Frontendni oldinroq chiqarish mumkin**, lekin unda sayt boʻsh koʻrinadi va subdomen tayyor boʻlgach **qayta build** qilish shart — aks holda u eski manzilga murojaat qilaveradi.

---

## 1. Server talablari

**PHP 8.4 yoki undan yuqori.** Bu qat'iy talab: `vendor/composer/platform_check.php` PHP 8.4 dan pastda ishga tushishni to'xtatadi. Agar serverda 8.2 yoki 8.3 bo'lsa, ikkita yo'l bor — 8.4 ni o'rnatish yoki serverning o'z PHP versiyasida `composer install` ni qayta bajarish.

Kerakli PHP kengaytmalari:

```
gd  fileinfo  mbstring  pdo_mysql  openssl  tokenizer  xml  ctype  json  curl  zip
```

**`exif` ixtiyoriy.** U bo'lsa, telefonda olingan rasmlar avtomatik to'g'ri burchakka buriladi. Bo'lmasa ham rasm yuklash ishlaydi — kod buni tekshiradi.

**`opcache` — majburiy.** Usiz PHP har bir so'rovda Laravel'ning 450+ faylini qaytadan o'qib kompilyatsiya qiladi. Bu o'lchangan farq: **so'rov 490 ms o'rniga 83 ms**, ya'ni olti barobar. `php.ini` da:

```ini
zend_extension=opcache
opcache.enable=1
opcache.memory_consumption=192
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=20000
opcache.validate_timestamps=1
opcache.revalidate_freq=2
```

Prodda `validate_timestamps=0` qo'yish yanada tezroq, ammo unda kod yangilangach `php-fpm` ni qayta ishga tushirish shart bo'ladi.

Bulardan tashqari: **MySQL 8 yoki MariaDB 10.4+**, **nginx**, **Node.js 20+** (frontend uchun).

---

## 2. Fayllarni joylashtirish

```bash
# Masalan:
/var/www/gspi-backend      ← bu loyiha
/var/www/gspi-front        ← frontend
```

`vendor/` katalogi repozitoriyda saqlanmaydi (`.gitignore` da), shuning uchun uni serverda o'rnatish **majburiy**:

```bash
cd /var/www/gspi-backend
composer install --no-dev --optimize-autoloader
```

`--no-dev` muhim: test kutubxonalari prodda kerak emas.

Yuklangan fayllar (`public/upload/`, `storage/app/public/`) ham repozitoriyda yo'q — ular faqat serverda yashaydi. Shu sababdan zaxira nusxa (10-bo'lim) haqiqatan zarur.

---

## 3. `.env` faylini sozlash

`.env.example` dan nusxa oling va quyidagilarni to'ldiring:

```ini
APP_NAME="GSPI"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://gspi.uz

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gspi
DB_USERNAME=<foydalanuvchi>
DB_PASSWORD=<parol>

# Saytdan yuborilgan murojaatlar shu kanalga tushadi.
# Bo'sh qoldirilsa xabar yuborilmaydi — bu xatolik emas.
TELEGRAM_BOT_TOKEN=
TELEGRAM_CHAT_ID=

# Sukut bo'yicha https://gspi.uz va https://www.gspi.uz.
# O'zgartirish kerak bo'lsa, vergul bilan ajratib yozing.
CORS_ALLOWED_ORIGINS=https://gspi.uz,https://www.gspi.uz

# API marshrutlari prefiksi — MAXFIY. Uzun tasodifiy satr.
# Frontenddagi `BACKEND_API_PREFIX` bilan bir xil boʻlishi shart.
API_PREFIX=<uzun-tasodifiy-satr>
```

**`APP_DEBUG=false` — eng muhim qator.** Debug yoqiq qolsa, har qanday xatolik sahifasi `.env` dagi hamma narsani, jumladan baza parolini, ochiq ko'rsatadi.

**`APP_URL` to'g'ri bo'lishi shart.** Rasm va fayl manzillari shundan quriladi.

Kalitni yarating (agar `.env` da `APP_KEY` bo'sh bo'lsa):

```bash
php artisan key:generate
```

---

## 4. Baza

```bash
mysql -u root -p -e "CREATE DATABASE gspi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
php artisan migrate --force
```

`--force` prodda majburiy — Laravel tasdiq so'ramaydi.

### Birinchi marta: boshlang'ich ma'lumot

Bu buyruqlar sayt bo'sh bo'lmasligi uchun. **Faqat birinchi o'rnatishda**, tartib bilan:

```bash
php artisan db:seed --class=GspiSeeder          # asosiy ma'lumotlar
php artisan db:seed --class=GspiContentSeeder   # kontent
php artisan db:seed --class=GspiStaffSeeder     # xodimlar tuzilmasi
php artisan db:seed --class=GspiPagesSeeder     # menyu va sahifalar
php artisan db:seed --class=GspiAdminUzSeeder   # yon menyu nomlari
php artisan db:seed --class=GspiBrandingSeeder  # logotip
```

Rasm o'rinbosarlari kerak bo'lsa (haqiqiy rasmlar yuklangunicha):

```bash
php artisan db:seed --class=GspiMediaSeeder     # yangilik va albom rasmlari
php artisan db:seed --class=GspiPortraitSeeder  # xodim portretlari
php artisan db:seed --class=GspiNoticeSeeder    # banner, video, tadbir, modal xabar
```

Hamma seeder **idempotent** — qayta ishga tushirilsa nusxa yaratmaydi.

> **Diqqat.** `GspiSeeder`, `GspiContentSeeder` va `GspiStaffSeeder` ichidagi matnlarning katta qismi **o'rinbosar** — xodim ismlari, telefon raqamlari va bank rekvizitlari o'ylab topilgan, haqiqiy emas. Ularni faqat sayt bo'sh ko'rinmasligi uchun ishlating. Serverga chiqishdan oldin 8-bo'limdagi `php artisan demo:audit` ni bajaring va haqiqiy ma'lumot bilan almashtiring.

---

## 5. Fayl havolasi va ruxsatlar

```bash
php artisan storage:link
```

Bu qadam **majburiy**. Usiz admin paneldan sahifaga biriktirilgan fayllar (`/storage/…`) saytda ochilmaydi. Hozirgi ishlab chiqish muhitida bu bajarilmagan.

Yozish huquqi kerak bo'lgan kataloglar:

```bash
chown -R www-data:www-data storage bootstrap/cache public/upload
chmod -R 775 storage bootstrap/cache public/upload
```

`public/upload/` — admin paneldan yuklangan barcha rasmlar shu yerda. Uni zaxiralashni unutmang.

---

## 6. Keshlash

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**Diqqat:** `config:cache` `.env` qiymatlarini fayl ichiga muhrlaydi. Shundan keyin `.env` ni o'zgartirsangiz, keshni qayta qurmaguningizcha o'zgarish ta'sir qilmaydi:

```bash
php artisan config:clear && php artisan config:cache
```

Shu sababdan kodda `env()` ishlatilmaydi — hamma sozlama `config/` orqali o'qiladi.

---

## 7. Nginx, SSL va kirishni cheklash

Backend alohida serverda turgani uchun nginx ikkita narsani beradi: **API** (faqat ahost uchun) va **admin panel** (xodimlar uchun).

Tayyor qoralama: `deploy/nginx.conf.example`. Uni koʻchiring va toʻrtta narsani toʻgʻrilang: `server_name`, `root` yoʻli, php-fpm socket nomi, sertifikat yoʻllari.

```bash
cp deploy/nginx.conf.example /etc/nginx/sites-available/admin.gspi.uz
ln -s /etc/nginx/sites-available/admin.gspi.uz /etc/nginx/sites-enabled/
nginx -t && systemctl reload nginx
```

### Domen

Backend serveriga subdomen kerak — masalan `admin.gspi.uz`. Uni ahost DNS panelidan institut serverining IP manziliga (`A` yozuvi) yoʻnaltiring.

Subdomensiz ham ishlaydi (IP + port), lekin unda **Let's Encrypt sertifikat bera olmaydi**, HTTPS esa bu tuzilmada majburiy. Shuning uchun subdomen amalda shart.

### Sertifikat

```bash
certbot --nginx -d admin.gspi.uz
```

DNS yozuvi tarqalgandan keyin bajaring, aks holda tekshiruv oʻtmaydi.

### Kirishni cheklash — eng muhim qadam

API endi internetdan koʻrinadi. Prefiksning maxfiyligi himoyaning bir qismi, lekin **yagonasi boʻlmasligi kerak**. API'ga faqat ahost serveri kira olsin:

```nginx
# API — faqat frontend serveri uchun.
location ^~ /<API_PREFIKSI>/ {
    allow <ahost-server-IP>;
    deny  all;

    try_files $uri /index.php?$query_string;
}
```

Ahost IP manzilini hostingdan soʻrang. Ahost shared hostingda — u yerda chiquvchi IP odatda barqaror, lekin **kafolat yoʻq**: hosting infratuzilmani oʻzgartirsa IP ham oʻzgaradi va sayt toʻsatdan boʻsh maʼlumot koʻrsata boshlaydi. Bu holat 11-boʻlimda tasvirlangan — birinchi tekshiriladigan narsa aynan shu.

Agar IP tez-tez oʻzgarsa, cheklashning oʻrniga API kaliti kerak boʻladi; ayting, alohida qilamiz.


Admin panel esa xodimlar uchun ochiq qoladi (`/admin/`, `/login`), uni IP bilan cheklamang.

### Nima qayerga ketadi

| Manzil | Qayerga |
|---|---|
| `/upload/`, `/storage/`, `/assets/` | Toʻgʻridan-toʻgʻri diskdan, 30 kun kesh |
| `/admin/`, `/login`, `/logout` | php-fpm (admin panel) |
| `/<API_PREFIKSI>/` | php-fpm, **faqat ahost IP'sidan** |
| `index.php` dan boshqa `.php` | `deny all` (adminer kabi begona skriptlar uchun) |

### Frontend tomonida

`gspi-front/.env.local` da backend manzili shu subdomen boʻladi:

```ini
BACKEND_URL=https://admin.gspi.uz
BACKEND_API_PREFIX=<xuddi shu maxfiy prefiks>
```

`BACKEND_URL` da **`https://` boʻlishi shart** — `http://` boʻlsa soʻrovlar ochiq oʻtadi.

---
## 8. Xavfsizlik ro'yxati

### Avval: namunaviy ma'lumotni tozalash

**Bu qadam majburiy.** Sayt ishlab chiqilayotganda bazaga o'rinbosar kontent qo'yilgan — xodim ismlari, telefon raqamlari, email manzillari va **bank rekvizitlari**. Ular haqiqiy emas, o'ylab topilgan.

Hozirgi holatni ko'rish (hech narsa o'zgarmaydi):

```bash
php artisan demo:audit
```

Eng xavflisi — bank rekvizitlari (hisob raqami, g'aznachilik, MFO, STIR). Ular pul o'tkazish uchun ishlatiladi; noto'g'ri raqam saytda turishi jiddiy oqibatga olib keladi. Buxgalteriyadan tasdiqlatib, haqiqiysini qo'ying.

Agar haqiqiy ma'lumot hali tayyor bo'lmasa, namunaviy yozuvlarni o'chiring:

```bash
php artisan demo:audit --clean
```

Bu bo'limlar, fakultetlar va menyularni saqlab qoladi — sayt navigatsiyasi buzilmaydi. Bo'sh ro'yxatlarda sahifalar "ma'lumot kiritilmagan" holatini ko'rsatadi.

**Soxta ma'lumot bilan saytni ochmang.** Bu davlat oliygohining rasmiy sayti: soxta rektor ismi yoki soxta hisob raqami u yerda tursa, uni haqiqiy deb qabul qilishadi.

### Shuningdek: serverga chiqmasligi kerak bo'lgan fayllar

Loyihada bazaga to'g'ridan-to'g'ri kirish beradigan uchta fayl bor:

| Fayl | Nima |
|---|---|
| `public/adminer.php` | Brauzerdan ishlaydigan baza boshqaruvchisi |
| `public/adminer-4.17.1-mysql.php` | O'shaning ikkinchi nusxasi |
| `tmi.sql` | Eski loyihadan qolgan baza dumpi |

**Birinchi ikkitasi eng jiddiy xavf.** Ular `public/` ichida, ya'ni serverga chiqsa `https://gspi.uz/adminer.php` manzilidan **istalgan odam** bazaga kirish oynasini ochadi.

Serverga yuborishdan oldin o'chiring:

```bash
rm -f public/adminer*.php tmi.sql
```

Ular `.gitignore` ga qo'shilgan va `deploy/nginx.conf.example` da `index.php` dan boshqa hamma PHP fayl bloklangan — ya'ni himoya uch qatlamli. Lekin eng ishonchlisi baribir faylni umuman yubormaslik.

### Keyin: qolgan tekshiruvlar

Serverga chiqishdan oldin har birini tekshiring:

- [ ] **Admin hisobi parolini tekshirish.** Migratsiya `.env` dagi `ADMIN_USERNAME` / `ADMIN_PASSWORD` ni ishlatadi; parol berilmasa tasodifiy yaratiladi va konsolga bir marta chiqariladi. Uni saqlab qo'ying yoki darhol o'zingiznikiga almashtiring.
- [ ] **API faqat ahost IP manzilidan ochiq** (7-boʻlim). Backend endi internetdan koʻrinadi — buni tekshirmasangiz, prefiksni topgan har kim maʼlumotni oʻqiy oladi.
- [ ] **HTTPS ishlayapti** va `BACKEND_URL` da `https://` turibdi. Soʻrovlar ochiq internet orqali oʻtadi.
- [ ] `APP_DEBUG=false`
- [ ] `APP_ENV=production`
- [ ] `APP_KEY` yaratilgan va `.env` dan tashqariga chiqmagan
- [ ] `.env` fayli veb orqali ochilmasligi (nginx konfiguratsiyasida bloklangan)
- [ ] Baza foydalanuvchisi faqat `gspi` bazasiga kirish huquqiga ega
- [ ] `CORS_ALLOWED_ORIGINS` faqat sayt domeni

Parolni almashtirish:

```bash
php artisan tinker --execute="App\Models\User::where('role','admin')->orderBy('id')->first()->update(['password'=>Hash::make('YANGI_PAROL')]);"
```

Yoki admin panelning "Moderatorlar" bo'limidan.

---

## 9. Keyingi yangilanishlar

Kod o'zgargandan keyin:

```bash
cd /var/www/gspi-backend

php artisan down                    # texnik ishlar rejimi

# yangi kodni qo'ying, keyin:
composer install --no-dev --optimize-autoloader
php artisan migrate --force

php artisan config:clear && php artisan config:cache
php artisan route:clear  && php artisan route:cache
php artisan view:clear   && php artisan view:cache

php artisan up
```

Seederlarni **qayta ishga tushirmang** — ular boshlang'ich ma'lumot uchun. Yangi seeder qo'shilgan bo'lsagina, faqat o'shanisini ishga tushiring.

---

## 10. Zaxira nusxa

Kunlik zaxiralanishi kerak bo'lgan ikkita narsa:

```bash
# Baza
mysqldump -u root -p gspi | gzip > gspi-$(date +%F).sql.gz

# Yuklangan fayllar
tar czf gspi-upload-$(date +%F).tar.gz public/upload storage/app/public
```

Kod zaxiralanmasa ham bo'ladi — u repozitoriyda. Yuklangan fayllar esa faqat serverda.

---

## 11. Tez-tez uchraydigan nosozliklar

Quyidagi ikkitasi eng koʻp vaqt yeydi, chunki ikkalasida ham **sozlama toʻgʻri koʻrinadi, lekin ishlamaydi**. Nimadir tushunarsiz buzilgan boʻlsa, avval shularni tekshiring.

### ⚠️ Sayt toʻsatdan boʻsh maʼlumot koʻrsatyapti

Sahifalar ochiladi, lekin yangiliklar, xodimlar va boshqa roʻyxatlar boʻsh. Xatolik xabari yoʻq.

**Birinchi navbatda ahost serverining IP manzili oʻzgarmaganini tekshiring.** API faqat oʻsha IP'dan ochiq (7-boʻlim); hosting infratuzilmani oʻzgartirsa, IP ham oʻzgaradi va backend ahostga `403` qaytara boshlaydi. Frontend buni "maʼlumot yoʻq" deb koʻrsatadi — ya'ni sabab hech qayerda yozilmaydi.

Tekshirish — backend serverida:

```bash
grep -E "403" /var/log/nginx/gspi-access.log | tail -20
```

Agar `403` lar bir xil notanish IP'dan kelayotgan boʻlsa, oʻsha ahostning yangi manzili. Nginx konfiguratsiyasidagi `allow` ni yangilang va `systemctl reload nginx` qiling.

Ahost shared hostingda, u yerda chiquvchi IP odatda barqaror — lekin kafolat yoʻq. Agar bu tez-tez takrorlansa, IP cheklovi oʻrniga API kaliti kerak boʻladi; ayting, qoʻshamiz.

### ⚠️ `.env` ni oʻzgartirdim, lekin hech narsa oʻzgarmadi

`config:cache` qiymatlarni fayl ichiga muhrlagan. `.env` ni tahrirlash oʻz-oʻzidan yetarli emas — keshni qayta qurish shart:

```bash
php artisan config:clear && php artisan config:cache
```

Bu **har safar** `.env` oʻzgarganda kerak, jumladan `API_PREFIX` yoki `CORS_ALLOWED_ORIGINS` ni tuzatganda.

> Frontendda ham xuddi shunday tuzoq bor: `BACKEND_URL` build paytida `.next/` ichiga yoziladi. Noutbukda dev qiymatlari bilan qurilgan build serverga yuklansa, sayt `localhost` ga murojaat qiladi. Buildni serverning oʻzida qiling — batafsil `gspi-front/DEPLOY.md` da.

### Qolganlari

**"Reading Exif data is not supported"** — `exif` kengaytmasi yoʻq. Kod buni endi oʻzi hal qiladi, lekin agar eski versiya ishlab tursa, `php artisan config:clear` qilib koʻring yoki kengaytmani oʻrnating.

**Rasm yuklanadi, lekin koʻrinmaydi** — `php artisan storage:link` bajarilmagan yoki `public/upload` ga yozish huquqi yoʻq.

**Admin panel 419 xatosi beradi** — sessiya sozlamasi. `SESSION_DRIVER=file` boʻlsa `storage/framework/sessions` ga yozish huquqini tekshiring.

**Sayt "500" beradi, sabab koʻrinmaydi** — bu toʻgʻri holat (`APP_DEBUG=false`). Sababi `storage/logs/laravel.log` da.

---
## 12. Tekshirish

Backend serverida, chiqargandan keyin:

```bash
# 1. API serverning oʻzidan javob beryaptimi (200 kutiladi)
curl -s -o /dev/null -w "%{http_code}\n" \
  -H "Accept-Language: uz" \
  http://127.0.0.1:8000/<API_PREFIKSI>/menu

# 2. HTTPS orqali ham ishlaydimi (200 kutiladi)
curl -s -o /dev/null -w "%{http_code}\n" \
  https://admin.gspi.uz/<API_PREFIKSI>/menu

# 3. Admin panel ochiladimi (200 kutiladi)
curl -s -o /dev/null -w "%{http_code}\n" https://admin.gspi.uz/login

# 4. Begona PHP skriptlar yopiqmi (403 yoki 404 kutiladi)
curl -s -o /dev/null -w "%{http_code}\n" https://admin.gspi.uz/adminer.php
```

Ikkinchi buyruqni **boshqa kompyuterdan** ham bajaring: IP cheklash yoqilgan boʻlsa u yerdan `403` kelishi kerak. Agar `200` kelsa — cheklash ishlamayapti, 7-boʻlimga qayting.

Frontend tomondan (ahost serverida):

```bash
# Frontend backendni koʻra olyaptimi
curl -s -o /dev/null -w "%{http_code}\n" \
  https://admin.gspi.uz/<API_PREFIKSI>/menu

# Sayt ochiladimi
curl -s -o /dev/null -w "%{http_code}\n" https://gspi.uz
```

API prefiksi `.env` dagi `API_PREFIX` dan olinadi (kodda yozilmagan — repozitoriyga tushmasligi uchun). U maxfiy: hujjatlarga, xabarlarga yoki repozitoriyga yozmang.

`API_PREFIX` boʻsh qoldirilsa oddiy `api` ishlatiladi — bu ishlab chiqish uchun, prodda albatta uzun tasodifiy satr qoʻying:

```bash
php -r "echo bin2hex(random_bytes(24)), PHP_EOL;"
```

Xuddi shu qiymat frontendda ham turishi shart — ikkalasi **bir xil** boʻlmasa, Next.js proksisi backendni topolmaydi va sayt 404 ga uradi:

| Ilova | Fayl | Sozlama |
|---|---|---|
| Backend (bu loyiha) | `.env` | `API_PREFIX=<maxfiy>` |
| Frontend (`gspi-front`) | `.env.local` | `BACKEND_API_PREFIX=<xuddi shu maxfiy>` |
