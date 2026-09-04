<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EntranceRequirement;
use App\Models\EducationalProgram;
use App\Models\EmployMeta;
use Illuminate\Support\Facades\Storage;


class EducationalProgramsController extends Controller
{


    public function get_educational_programs(Request $request)
    {
        $locale = $request->get('locale', app()->getLocale()); // Get the locale for translations

        // Daraja boʻyicha filtr: `?level=bachelor` yoki `?level=master`.
        // Menyudagi "Bakalavriat" va "Magistratura" bandlari shu orqali
        // ajratiladi — usiz ikkalasi bir xil roʻyxatni ochardi.
        $level = $request->query('level');

        $byLevel = fn ($query) => $level ? $query->where('level', $level) : $query;

        // Retrieve all parent educational programs with their relationships
        $programs = EducationalProgram::whereNull('parent_id')
            ->tap($byLevel)
            ->with([
                'children' => $byLevel,
                'employs',
                'activity',
                'faq',
                'EntranceRequirement',
            ])
            ->get();

        // Localize and structure the response
        $translatedPrograms = $programs->map(function ($program) use ($locale) {
            return [
                'id' => $program->id,
                'name' => data_get($program->name, $locale) ?? data_get($program->name, $this->main_lang->code),
                'slug' => $program->slug,
                'active' => $program->active,
                'education_years' => $program->education_years,
                'level' => $program->level,
                'yt_link' => $program->yt_link,
                'order' => $program->order,
                'children' => $program->children->map(function ($child) use ($locale) {
                    return [
                        'id' => $child->id,
                        'name' => data_get($child->name, $locale) ?? data_get($child->name, $this->main_lang->code),
                        'first_name' => data_get($child->first_name, $locale) ?? data_get($child->first_name, $this->main_lang->code),
                        'second_name'=> data_get($child->second_name, $locale) ?? data_get($child->second_name, $this->main_lang->code),
                        'third_name'=> data_get($child->third_name, $locale) ?? data_get($child->third_name, $this->main_lang->code),
                        'parent_id'=> $child->parent_id ?? null,
                        'slug'=> $child->slug ?? null,
                        'map'=> data_get($child->map, $locale) ?? data_get($child->map, $this->main_lang->code) ?? null,
                        'active'=> $child->active ?? null,
                        'education_years' => $child->education_years,
                        'order' => $child->order,

                        'form_education'=> data_get($child->form_education, $locale) ?? data_get($child->form_education, $this->main_lang->code) ?? null,
                        'daytime'=> $child->daytime ?? null,
                        'part_time' => $child->part_time ?? null,
                        'first_description' => data_get($child->first_description, $locale) ?? data_get($child->first_description, $this->main_lang->code) ?? null,
                        'second_description'=> data_get($child->second_description, $locale) ?? data_get($child->second_description, $this->main_lang->code) ?? null,
                        'third_description' => data_get($child->third_description, $locale) ?? data_get($child->third_description, $this->main_lang->code) ?? null,
                        'icon' => $child->icon ? url($child->icon) : null,
                        'file' => $child->file ? url($child->file) : null,
                        'code' => $child->code,
                        'level' => $child->level,
                        'lang' => data_get($child->lang, $locale) ?? null,
                        'kundizgi_date' => $child->date,
                        'sirtqi_date' => $child->sirtqi_date,
                        'photo' => [
                            'lg' => $child->photo ? url('/upload/images/' . $child->photo) : null, // Katta o'lchamdagi rasm
                            'md' => $child->photo ? url('/upload/images/600/' . $child->photo) : null, // O'rtacha o'lchamdagi rasm
                            'sm' => $child->photo ? url('/upload/images/200/' . $child->photo) : null, // Kichik o'lchamdagi rasm
                        ],

                        'employs' => $child->employs->map(function ($employ) use ($locale) {
                            return [
                                'id' => $employ->id,
//
                                'name' => trim(implode(' ', array_filter([
                                    data_get($employ->first_name, $locale) ?? data_get($employ->first_name, $this->main_lang->code)
                                        ?? null,
                                    data_get($employ->last_name, $locale) ?? data_get($employ->last_name, $this->main_lang->code)
                                        ?? null,
                                ]))),

                                'dec' => data_get($employ->dec, $locale) ?? $employ->dec, // Add localization if applicable
                                'photo' => [
                                    'lg' => $employ->photo ? url('/upload/images/' . $employ->photo) : null, // Katta o'lchamdagi rasm
                                    'md' => $employ->photo ? url('/upload/images/600/' . $employ->photo) : null, // O'rtacha o'lchamdagi rasm
                                    'sm' => $employ->photo ? url('/upload/images/200/' . $employ->photo) : null, // Kichik o'lchamdagi rasm
                                ],
                            ];
                        }),
                        'activity' => $child->activity ? [
                            'id' => $child->activity->id,
                            'title' => data_get($child->activity->title, $locale) ?? data_get($child->activity->title, $this->main_lang->code),
                            'dec' => data_get($child->activity->dec, $locale) ?? data_get($child->activity->dec, $this->main_lang->code),
                            'dec' => data_get($child->activity->dec, $locale) ?? data_get($child->activity->dec, $this->main_lang->code),
                            'photo' => [
                                'lg' => $child->activity->photo ? url('/upload/images/' . $child->activity->photo) : null, // Katta o'lchamdagi rasm
                                'md' => $child->activity->photo ? url('/upload/images/600/' . $child->activity->photo) : null, // O'rtacha o'lchamdagi rasm
                                'sm' => $child->activity->photo ? url('/upload/images/200/' . $child->activity->photo) : null, // Kichik o'lchamdagi rasm
                            ],
                        ] : null,
                        'entrance_requirement' => $child->EntranceRequirement ? [
                            'id' => $child->EntranceRequirement->id,
                            'name' => is_array($child->EntranceRequirement->name) ? (data_get($child->EntranceRequirement->name, $locale) ?? data_get($child->EntranceRequirement->name, $this->main_lang->code) ?? null) : $child->EntranceRequirement->name,
                            'dec' => is_array($child->EntranceRequirement->dec) ? (data_get($child->EntranceRequirement->dec, $locale) ?? data_get($child->EntranceRequirement->dec, $this->main_lang->code) ?? null) : $child->EntranceRequirement->dec,

                            // Rasm URL'lari
                            'photo' => [
                                'lg' => $child->EntranceRequirement->lg_img,
                                'md' => $child->EntranceRequirement->md_img,
                                'sm' => $child->EntranceRequirement->sm_img,
                            ],

                            // Skills bo‘limi
                            'skills' => $child->EntranceRequirement->skills()->get()->filter(function ($skill) {
                                return optional($skill->created_at)->greaterThanOrEqualTo(now()->subDays(7));
                            })->map(function ($skill) use ($locale) {
                                return [
                                    'id' => $skill->id,
                                    'name' => is_array($skill->name) ? (data_get($skill->name, $locale) ?? data_get($skill->name, $this->main_lang->code) ?? null) : $skill->name,
                                ];
                            })->toArray()
                        ] : null,



                    'faq' => $child->faq ? $child->faq->map(function ($faq) use ($locale) {
                            return [
                                'id' => $faq->id,
                                'action' => $faq->action,
                                'parent' => $faq->parent_id,
                                'skills' => $faq->skill ? [
                                    'id' => $faq->skill->id,
                                    'name' => data_get($faq->skill->name, $locale) ?? data_get($faq->skill->name, $this->main_lang->code),
                                ] : null,
                                'question' => data_get($faq->question, $locale) ?? data_get($faq->question, $this->main_lang->code) ?? null,
                                'answer' => data_get($faq->answer, $locale) ?? data_get($faq->answer, $this->main_lang->code) ?? null,
                            ];
                        }) : null,
                    ];
                }),
            ];
        });

        return response()->json($translatedPrograms);
    }

    public function show_educational_program(Request $request, $id)
    {
        $locale = $request->get('locale', app()->getLocale()); // Get the locale for translations

        // Retrieve the specific educational program with its relationships
        $program = EducationalProgram::where('id', $id)
            ->orWhere('slug', $id)
            ->with(['children', 'employs', 'activity', 'faq', 'EntranceRequirement'])
            ->first();

        if (!$program) {
            return response()->json(['message' => 'Program not found'], 404);
        }

        // Localize and structure the response
        $translatedProgram = [
            'id' => $program->id,
            'name' => data_get($program->name, $locale) ?? null,
            'first_name' => data_get($program->first_name, $locale) ?? null,
            'second_name'=> data_get($program->second_name, $locale) ?? null,
            'third_name'=> data_get($program->third_name, $locale) ?? null,
            'parent_id'=> $program->parent_id ?? null,
            'slug'=> $program->slug ?? null,
            'map'=> data_get($program->map, $locale) ?? null,
            'active'=> $program->active ?? null,
            'form_education'=> data_get($program->form_education, $locale) ?? null,
            'daytime'=> $program->daytime ?? null,
            'part_time' => $program->part_time ?? null,
            'first_descriptionv' => data_get($program->first_description, $locale) ?? null,
            'second_description'=> data_get($program->second_description, $locale) ?? null,
            'third_description' => data_get($program->third_description, $locale) ?? null,

            'icon' => $program->icon ? url( $program->icon) : null,
            'file' => $program->file ? url( $program->file) : null,
            'code' => $program->code,
            'order' => $program->order,
            'lang' => data_get($program->lang, $locale) ?? null,
            'kundizgi_date' => $program->date,
            'sirtqi_date' => $program->sirtqi_date,
            'education_years' => $program->education_years,
            'yt_link' => $program->yt_link,
            'photo' => [
                'lg' => $program->photo ? url('/upload/images/' . $program->photo) : null, // Katta o'lchamdagi rasm
                'md' => $program->photo ? url('/upload/images/600/' . $program->photo) : null, // O'rtacha o'lchamdagi rasm
                'sm' => $program->photo ? url('/upload/images/200/' . $program->photo) : null, // Kichik o'lchamdagi rasm
            ],
            'employs' => $program->employs->map(function ($employ) use ($locale) {
                return [
                    'id' => $employ->id,
                    'name' => data_get($employ->first_name, $locale),
                    'position' => data_get($employ->position, $locale),
                    'photo' => [
                        'lg' => $employ->photo ? url('/upload/images/' . $employ->photo) : null, // Katta o'lchamdagi rasm
                        'md' => $employ->photo ? url('/upload/images/600/' . $employ->photo) : null, // O'rtacha o'lchamdagi rasm
                        'sm' => $employ->photo ? url('/upload/images/200/' . $employ->photo) : null, // Kichik o'lchamdagi rasm
                    ],
                ];
            }),
            'activity' => $program->activity ? [
                'id' => $program->activity->id,
                'title' => data_get($program->activity->title, $locale),
                'dec' => data_get($program->activity->dec, $locale),
                'photo' => [
                    'lg' => $program->activity->photo ? url('/upload/images/' . $program->activity->photo) : null,
                    'md' => $program->activity->photo ? url('/upload/images/600/' . $program->activity->photo) : null,
                    'sm' => $program->activity->photo ? url('/upload/images/200/' . $program->activity->photo) : null,
                ],
            ] : null,
            'entrance_requirement' => $program->EntranceRequirement ? [
                'id' => $program->EntranceRequirement->id,
                'name' => data_get($program->EntranceRequirement->name, $locale),
                'dec' => data_get($program->EntranceRequirement->dec, $locale),
                'photo' => [
                    'lg' => $program->EntranceRequirement->photo ? url('/upload/images/' . $program->EntranceRequirement->photo) : null,
                    'md' => $program->EntranceRequirement->photo ? url('/upload/images/600/' . $program->EntranceRequirement->photo) : null,
                    'sm' => $program->EntranceRequirement->photo ? url('/upload/images/200/' . $program->EntranceRequirement->photo) : null,
                ],
                'skills' => $program->EntranceRequirement->skills ? $program->EntranceRequirement->skills->filter(function ($skill) {
                    return $skill->created_at >= now()->subDays(7);
                })->map(function ($skill) use ($locale) {
                    return [
                        'id' => $skill->id,
                        'name' => data_get($skill->name, $locale),
                    ];
                }) : [],
            ] : null,
            'faq' => $program->faq ? $program->faq->map(function ($faq) use ($locale) {
                return [
                    'id' => $faq->id,
                    'parent' => $faq->parent_id,
                    'action' => $faq->action,
                    'skills' => $faq->skill ? [
                        'id' => $faq->skill->id,
                        'name' => data_get($faq->skill->name, $locale),
                    ] : null,
                    'question' => data_get($faq->question, $locale) ?? null,
                    'answer' => data_get($faq->answer, $locale) ?? null,
                ];
            }) : null,
            'children' => $program->children->map(function ($child) use ($locale) {
                return [
                    'id' => $child->id,
                    'name' => data_get($child->name, $locale) ?? null,
                    'slug' => $child->slug,
                    'code' => $child->code,
                    'order' => $child->order,
                    'lang' => data_get($child->lang, $locale) ?? null,
                    'active' => $child->active,
                    'education_years' => $child->education_years,

                    'date' => $child->date,
                    'lg_img' => $child->lg_img,
                    'md_img' => $child->md_img,
                    'sm_img' => $child->sm_img,
                    'employs' => $child->employs->map(function ($employ) use ($locale) {
                        return [
                            'id' => $employ->id,
                            'name' => data_get($employ->first_name, $locale),
                            'dec' => data_get($employ->dec, $locale) ?? data_get($employ->first_name, $locale),
                            'photo' => [
                                'lg' => $employ->activity->photo ? url('/upload/images/' . $employ->activity->photo) : null,
                                'md' => $employ->activity->photo ? url('/upload/images/600/' . $employ->activity->photo) : null,
                                'sm' => $employ->activity->photo ? url('/upload/images/200/' . $employ->activity->photo) : null,
                            ],
                        ];
                    }),
                    'activity' => $child->activity ? [
                        'id' => $child->activity->id,
                        'title' => data_get($child->activity->title, $locale),
                        'dec' => data_get($child->activity->dec, $locale),
                        'photo' => [
                            'lg' => $child->activity->photo ? url('/upload/images/' . $child->activity->photo) : null,
                            'md' => $child->activity->photo ? url('/upload/images/600/' . $child->activity->photo) : null,
                            'sm' => $child->activity->photo ? url('/upload/images/200/' . $child->activity->photo) : null,
                        ],
                    ] : null,
                    'entrance_requirement' => $child->EntranceRequirement ? [
                        'id' => $child->EntranceRequirement->id,
                        'name' => data_get($child->EntranceRequirement->name, $locale),
                        'dec' => data_get($child->EntranceRequirement->dec, $locale),
                        'photo' => [
                            'lg' => $child->EntranceRequirement->photo ? url('/upload/images/' . $child->EntranceRequirement->photo) : null,
                            'md' => $child->EntranceRequirement->photo ? url('/upload/images/600/' . $child->EntranceRequirement->photo) : null,
                            'sm' => $child->EntranceRequirement->photo ? url('/upload/images/200/' . $child->EntranceRequirement->photo) : null,
                        ],
                        'skills' => $child->EntranceRequirement->skills ? $child->EntranceRequirement->skills->filter(function ($skill) {
                            return $skill->created_at >= now()->subDays(7);
                        })->map(function ($skill) use ($locale) {
                            return [
                                'id' => $skill->id,
                                'name' => data_get($skill->name, $locale),
                            ];
                        }) : [],
                    ] : null,
                    'faq' => $child->faq ? $child->faq->map(function ($faq) use ($locale) {
                        return [
                            'id' => $faq->id,
                            'parent' => $faq->parent_id,
                            'skills' => $faq->skill ? [
                                'id' => $faq->skill->id,
                                'name' => data_get($faq->skill->name, $locale),
                            ] : null,
                            'question' => data_get($faq->question, $locale) ?? null,
                            'answer' => data_get($faq->answer, $locale) ?? null,
                        ];
                    }) : null,
                ];
            }),
        ];

        return response()->json($translatedProgram);
    }
}
