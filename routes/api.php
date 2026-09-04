<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiController;




/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::middleware(['api', 'locale','throttle:150,1'])->group(function () {

        Route::get('/news', [\App\Http\Controllers\Api\NewsController::class, 'get_posts']);
        Route::get('/news/{slug}', [\App\Http\Controllers\Api\NewsController::class, 'show_post']);

        Route::get('/kampus', [\App\Http\Controllers\Api\NewsController::class, 'get_kampus']);
        Route::get('/kampus/{slug}', [\App\Http\Controllers\Api\NewsController::class, 'show_kampus']);

        Route::get('/test', [\App\Http\Controllers\Api\NewsController::class, 'get_test']);
        Route::get('/test/{slug}', [\App\Http\Controllers\Api\NewsController::class, 'show_test']);

        Route::get('/video_news', [\App\Http\Controllers\Api\NewsController::class, 'get_video_post']);
        Route::get('/video_news/{slug}', [\App\Http\Controllers\Api\NewsController::class, 'show_video_post']);

        Route::get('/categories', [\App\Http\Controllers\Api\NewsController::class, 'get_categories']);

        Route::get('/categories/{slug}', [\App\Http\Controllers\Api\NewsController::class, 'show_categories']);

        Route::get('/students', [\App\Http\Controllers\Api\ApiController::class, 'get_students']);
        Route::get('/students/{slug}', [\App\Http\Controllers\Api\ApiController::class, 'show_students']);

        Route::get('/students/filter/{filer}', [\App\Http\Controllers\Api\ApiController::class, 'show_students_filter']);
        Route::get('reklama', [\App\Http\Controllers\Api\ApiController::class, 'reklama']);

        // Har sahifada kerak boʻladigan uchtasi bitta javobda:
        // siteinfo + menu + popups.
        Route::get('/bootstrap', \App\Http\Controllers\Api\BootstrapController::class);

        // Tadbirlar kalendari.
        Route::get('/events', [\App\Http\Controllers\Api\EventController::class, 'index']);
        Route::get('/events/{slug}', [\App\Http\Controllers\Api\EventController::class, 'show']);

        // Saytga kirilganda ochiladigan modal xabarlar va tugʻilgan kunlar.
        Route::get('/popups', [\App\Http\Controllers\Api\NoticeController::class, 'popups']);
        Route::get('/birthdays', [\App\Http\Controllers\Api\NoticeController::class, 'birthdays']);

        // Dinamik sahifalar — menyudagi bandlarning kontenti.
        Route::get('/pages', [\App\Http\Controllers\Api\PageController::class, 'index']);
        Route::get('/pages/{slug}', [\App\Http\Controllers\Api\PageController::class, 'show']);
        Route::get('/pages/{slug}/{item}', [\App\Http\Controllers\Api\PageController::class, 'item']);

        Route::get('/menu', [\App\Http\Controllers\Api\MenuMontroller::class, 'get_menu']);
        Route::get('/menu/{id}', [\App\Http\Controllers\Api\MenuMontroller::class, 'show_menu']);

        Route::get('/faq', [ApiController::class, 'get_faq']);
        Route::get('/faq/{id}', [ApiController::class, 'show_faq']);

        Route::get('/partners', [ApiController::class, 'get_partners']);
        Route::get('/partners/{id}', [ApiController::class, 'show_partners']);

        Route::get('/educational-programs', [\App\Http\Controllers\Api\EducationalProgramsController::class, 'get_educational_programs']);
        Route::get('/educational-programs/{id}', [\App\Http\Controllers\Api\EducationalProgramsController::class, 'show_educational_program']);

        Route::get('/partner-link', [ApiController::class, 'get_partners_link']);
        Route::get('/partner-link/{id}', [ApiController::class, 'show_partners_link']);

        Route::get('/certificates', [ApiController::class, 'get_certificates']);
        Route::get('/certificates/{id}', [ApiController::class, 'show_certificates']);

        Route::get('/certificates-other', [ApiController::class, 'get_certificates_other']);
        Route::get('/certificates-other/{id}', [ApiController::class, 'show_certificates_other']);

        Route::get('/documents', [ApiController::class, 'get_documents']);
        Route::get('/documents/{id}', [ApiController::class, 'show_documents']);

        Route::get('/journals', [ApiController::class, 'get_journals']);
        Route::get('/journals/{slug}', [ApiController::class, 'show_journals']);

        Route::get('/leaderships', [\App\Http\Controllers\Api\LeadershipController::class, 'getRahbariyatEmployees']);
        Route::get('/leaderships/{slug}', [\App\Http\Controllers\Api\LeadershipController::class, 'getEmployeeDetails']);

        Route::get('/department', [\App\Http\Controllers\Api\LeadershipController::class, 'showEmployeesByPosition']);
        Route::get('/department/{slug}', [\App\Http\Controllers\Api\LeadershipController::class, 'getDepartmentEmployees']);
        Route::get('/department/user/{slug}', [\App\Http\Controllers\Api\LeadershipController::class, 'getDepartmentEmployeesuser']);


        Route::get('/fakultet', [\App\Http\Controllers\Api\LeadershipController::class, 'getfakultet']);
        Route::get('/fakultet/{slug}', [\App\Http\Controllers\Api\LeadershipController::class, 'showfakultet']);
        Route::get('/fakultet/user/{slug}', [\App\Http\Controllers\Api\LeadershipController::class, 'showfakultetuser']);

        Route::get('/kafedralar', [\App\Http\Controllers\Api\LeadershipController::class, 'getKafedralar']);
        Route::get('/kafedralar/{slug}', [\App\Http\Controllers\Api\LeadershipController::class, 'showKafedralar']);
        Route::get('/kafedralar/user/{slug}', [\App\Http\Controllers\Api\LeadershipController::class, 'showkafedralaruser']);
        Route::get('/banners', [ApiController::class, 'get_banner']);

        Route::get('/vacancies', [ApiController::class, 'get_vacancies']);
        Route::get('/vacancies/{id}', [ApiController::class, 'show_vacancies']);
        Route::get('siteinfo', [ApiController::class, 'getCompany']);

        Route::get('/categories', [\App\Http\Controllers\Api\NewsController::class, 'get_categories']);
        Route::get('/categories/{slug}', [\App\Http\Controllers\Api\NewsController::class, 'show_categories']);
        Route::get('/categories/filter/{id}', [\App\Http\Controllers\Api\NewsController::class, 'show_categor_product']);


        Route::get('/translations', [ApiController::class, 'translations']);
        // Ommaviy forma: bitta IP daqiqasiga 5 marta yubora oladi.
        Route::post('/contacts', [ApiController::class, 'store'])->middleware('throttle:5,1');

        // Murojaatlar (rektorga / tyutorga / komplayensga)
        Route::get('/murojaat/meta', [\App\Http\Controllers\Api\AppealController::class, 'meta']);
        // Ommaviy forma: bitta IP daqiqasiga 5 marta yubora oladi.
        Route::post('/murojaat', [\App\Http\Controllers\Api\AppealController::class, 'store'])->middleware('throttle:5,1');
        Route::get('/murojaat/{ticket}', [\App\Http\Controllers\Api\AppealController::class, 'show']);

        // Hujjat turkumlari (/documents?category=slug uchun)
        Route::get('/document-categories', [ApiController::class, 'get_document_categories']);

        // Galereya
        Route::get('/gallery', [\App\Http\Controllers\Api\GalleryController::class, 'albums']);
        Route::get('/photos', [\App\Http\Controllers\Api\GalleryController::class, 'photos']);
        Route::get('/gallery/{id}', [\App\Http\Controllers\Api\GalleryController::class, 'album']);

        // Lavozimlar va tyutorlar
        Route::get('/positions', [\App\Http\Controllers\Api\LeadershipController::class, 'getPositions']);
        Route::get('/tutors', [\App\Http\Controllers\Api\LeadershipController::class, 'getTutors']);




});
