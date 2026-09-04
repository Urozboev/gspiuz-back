<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    ApplicationsController,
    TranslationsController,
    TranslationGroupController,
    LangsController,
    ProductController,
    ProductsCategoryController,
    CertificateController,
    PartnerController,
    PostController,
    ServiceController,
    MemberController,
    FeedbackController,
    PostsCategoryController,
    WorkController,
    DocumentController,
    DocumentCategoryController,
    UserController,
    BrandController,
    LogController,
    SiteInfoController,
    VacancyController,
    AdditionalFunctionController,
    HomeController,
    AdvantageCategoryController,
    AdvantageController,
    QuestionController,
    DevelopmentController,
};
use App\Http\Controllers\Admin\MenusController;
use App\Http\Controllers\Admin\AdvertisementController;

Auth::routes(['register' => false]);

Route::get('/admin', [HomeController::class, 'index'])->name('admin');

Route::middleware(['auth'])->prefix('admin')->group(function () {

    Route::resource('posts', PostController::class);

    Route::get('/menus/detele_file', [MenusController::class, 'detele_file'])->name('menus.detele_file');

    Route::get('/menus/{id}/restore', [MenusController::class, 'restore'])->name('menus.restore');

    Route::delete('/menus/{id}/force-destroy', [MenusController::class, 'forceDestroy'])->name('menus.forceDestroy');

    Route::post('/menu/update-order', [MenusController::class, 'updateOrder'])->name('menu.updateOrder');


    Route::resource('menus', \App\Http\Controllers\Admin\MenusController::class);

    Route::post('upload',[\App\Http\Controllers\Admin\DynamicMenuController::class,'upload'])->name('upload');

    Route::resource('dynamic-menus', \App\Http\Controllers\Admin\DynamicMenuController::class);


    Route::resource('posts_categories', PostsCategoryController::class);


    Route::resource('langs', LangsController::class);

    Route::resource('skills', \App\Http\Controllers\Admin\SkillController::class);

    Route::resource('activities', \App\Http\Controllers\Admin\ActivitiesController::class);

    Route::resource('entrance-requirements', \App\Http\Controllers\Admin\EntranceRequirementController::class);


    Route::get('education-faqs/{id}', [\App\Http\Controllers\Admin\EducationFaqController::class,'index'])->name('education_faqs.index');
    Route::get('education-faqs/create/{id}', [\App\Http\Controllers\Admin\EducationFaqController::class,'create'])->name('education_faqs.create');
    Route::post('education-faqs/', [\App\Http\Controllers\Admin\EducationFaqController::class,'store'])->name('education_faqs.store');
    Route::get('education-faqs/{main}/{id}/edit', [\App\Http\Controllers\Admin\EducationFaqController::class, 'edit'])
        ->name('education_faqs.edit');

    Route::put('education-faqs/{id}', [\App\Http\Controllers\Admin\EducationFaqController::class, 'update'])
        ->name('education_faqs.update');

    Route::delete('education-faqs/{id}', [\App\Http\Controllers\Admin\EducationFaqController::class, 'destroy'])->name('education_faqs.destroy');

    Route::delete('/delete-formmenu/{id}', [\App\Http\Controllers\Admin\DynamicMenuController::class, 'delete'])->name('formmenu.destroy');


//    Route::get('education-faqs/create/{id}', \App\Http\Controllers\Admin\EducationFaqController::class,'index')->name('education_faqs.index');



    Route::resource('positions', \App\Http\Controllers\Admin\PositionController::class);

    Route::resource('kampuses', \App\Http\Controllers\Admin\KampusController::class);

    Route::resource('employ_staff', \App\Http\Controllers\Admin\EmployStaffController::class);

    Route::resource('employ_forms', \App\Http\Controllers\Admin\EmployFormController::class);

    Route::resource('employ_types', \App\Http\Controllers\Admin\EmployTypeController::class);

    Route::resource('stracture_types', \App\Http\Controllers\Admin\StractureTypeController::class);

    Route::resource('employs', \App\Http\Controllers\Admin\EmployController::class);

    Route::resource('departaments', \App\Http\Controllers\Admin\DepartamentController::class);


    Route::get('employ_meta', [\App\Http\Controllers\Admin\EmployMetaController::class, 'index'])->name('employ_meta.index');

    Route::get('employ_meta/create', [\App\Http\Controllers\Admin\EmployMetaController::class, 'create'])->name('employ_meta.create');

    Route::post('employ_meta', [\App\Http\Controllers\Admin\EmployMetaController::class, 'store'])->name('employ_meta.store');

    Route::get('employ_meta/{id}', [\App\Http\Controllers\Admin\EmployMetaController::class, 'edit'])->name('employ_meta.edit');

    Route::put('employ_meta/{id}', [\App\Http\Controllers\Admin\EmployMetaController::class, 'update'])->name('employ_meta.update');

    Route::delete('employ_meta/{id}', [\App\Http\Controllers\Admin\EmployMetaController::class, 'destroy'])->name('employ_meta.destroy');

    Route::resource('products_categories', ProductsCategoryController::class);

    Route::get('banners', [BrandController::class, 'index'])->name('banners.index');

    Route::get('banners/create', [BrandController::class, 'create'])->name('banners.create');

    Route::post('banners', [BrandController::class, 'store'])->name('banners.store');

    Route::get('banners/{id}/edit', [BrandController::class, 'edit'])->name('banners.edit');

    Route::put('banners/{id}', [BrandController::class, 'update'])->name('banners.update');

    Route::delete('banners/{id}', [BrandController::class, 'destroy'])->name('banners.destroy');

    Route::resource('certificates', CertificateController::class);
    Route::resource('educational-programs', \App\Http\Controllers\Admin\EducationalProgramsController::class);

    Route::resource('documents', DocumentController::class);

    Route::resource('document_categories', DocumentCategoryController::class);

    Route::resource('feedbacks', FeedbackController::class);


    Route::get('students', [MemberController::class, 'index'])->name('students.index');

    Route::get('students/create', [MemberController::class, 'create'])->name('students.create');

    Route::post('students', [MemberController::class, 'store'])->name('students.store');

    Route::get('students/{id}/edit', [MemberController::class, 'edit'])->name('students.edit');

    Route::put('students/{id}', [MemberController::class, 'update'])->name('students.update');

    Route::delete('students/{id}', [MemberController::class, 'destroy'])->name('students.destroy');

//    Route::resource('students', MemberController::class);

    Route::resource('partners', PartnerController::class);

    Route::resource('questions', QuestionController::class);

    Route::resource('services', ServiceController::class);

    Route::resource('works', WorkController::class);

    Route::resource('users', UserController::class);

    Route::resource('applications', ApplicationsController::class);

    // Murojaatlar (rektorga / tyutorga / komplayensga)
    Route::resource('appeals', \App\Http\Controllers\Admin\AppealController::class)
        ->only(['index', 'show', 'update', 'destroy']);

    Route::resource('vacancies', VacancyController::class);

    Route::resource('logs', LogController::class);

    Route::resource('translations', TranslationsController::class);

    Route::resource('translation_groups', TranslationGroupController::class);

    // dropzone upload files
    Route::post('/upload_from_dropzone', [HomeController::class, 'upload_from_dropzone']);

    // upload image for CKEditor
    Route::post('upload-image', [HomeController::class, 'uploadImage'])->name('upload-image');

    // site info
    Route::post('facts_figures', [SiteInfoController::class, 'facts_figures_create'])->name('facts_figures.create');
    Route::get('facts_figures', [SiteInfoController::class, 'facts_figures'])->name('facts_figures');

    Route::resource('advertisements', AdvertisementController::class)->except(['create']);

    // Sahifa yozuvlari — kartochkalar va fayllar.
    Route::get('dynamic-menus/{page}/items', [\App\Http\Controllers\Admin\PageItemController::class, 'index'])->name('page-items.index');
    Route::get('dynamic-menus/{page}/items/create', [\App\Http\Controllers\Admin\PageItemController::class, 'create'])->name('page-items.create');
    Route::post('dynamic-menus/{page}/items', [\App\Http\Controllers\Admin\PageItemController::class, 'store'])->name('page-items.store');
    Route::get('dynamic-menus/{page}/items/{item}/edit', [\App\Http\Controllers\Admin\PageItemController::class, 'edit'])->name('page-items.edit');
    Route::put('dynamic-menus/{page}/items/{item}', [\App\Http\Controllers\Admin\PageItemController::class, 'update'])->name('page-items.update');
    Route::delete('dynamic-menus/{page}/items/{item}', [\App\Http\Controllers\Admin\PageItemController::class, 'destroy'])->name('page-items.destroy');

    // Tadbirlar kalendari (saytdagi /events sahifasi).
    Route::resource('events', \App\Http\Controllers\Admin\EventController::class)->except(['show']);

    // Saytga kirilganda ochiladigan modal xabarlar (bayram tabriklari).
    Route::resource('popups', \App\Http\Controllers\Admin\PopupController::class)->except(['show']);
    Route::resource('student_counts', \App\Http\Controllers\Admin\StudentConutController::class);
    Route::resource('site_infos', SiteInfoController::class)->except(['create']);

    // additional functions
    Route::resource('additional_functions', AdditionalFunctionController::class);

    // config page
    Route::get('config/Mmzf9N7QuCXDSk32', [HomeController::class, 'config'])->name('config');
    Route::post('config/update', [HomeController::class, 'config_update'])->name('config.update');

    // advantages
    Route::resource('advantages', AdvantageController::class);

    // advantage categories
    Route::resource('advantage_categories', AdvantageCategoryController::class);

    // developments
    Route::resource('developments', DevelopmentController::class);
});
