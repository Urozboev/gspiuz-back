<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appeal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Murojaatlar API — frontenddagi /murojaat sahifasi uchun.
 */
class AppealController extends Controller
{
    /** Murojaat turlari va holatlari — forma uchun ma'lumotnoma. */
    public function meta()
    {
        $locale = App::getLocale();

        $map = function (array $items) use ($locale) {
            $out = [];
            foreach ($items as $key => $labels) {
                $out[] = [
                    'key'   => $key,
                    'label' => $labels[$locale] ?? $labels['uz'],
                ];
            }
            return $out;
        };

        return response()->json([
            'data' => [
                'types'    => $map(Appeal::types()),
                'statuses' => $map(Appeal::statuses()),
            ],
        ]);
    }

    /** Yangi murojaat yuborish. */
    public function store(Request $request)
    {
        $data = $request->validate([
            'type'         => 'required|string|in:' . implode(',', array_keys(Appeal::types())),
            'name'         => 'required|string|max:255',
            'phone_number' => 'nullable|string|max:32',
            'email'        => 'nullable|email|max:255',
            'address'      => 'nullable|string|max:255',
            'message'      => 'required|string|max:5000',
            'file'         => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ]);

        $fileName = null;
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = Str::random(24) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('upload/appeals'), $fileName);
        }

        $appeal = Appeal::create([
            'ticket'       => Appeal::generateTicket(),
            'type'         => $data['type'],
            'status'       => Appeal::STATUS_NEW,
            'name'         => $data['name'],
            'phone_number' => $data['phone_number'] ?? null,
            'email'        => $data['email'] ?? null,
            'address'      => $data['address'] ?? null,
            'message'      => $data['message'],
            'file'         => $fileName,
            'ip'           => $request->ip(),
        ]);

        $this->notifyTelegram($appeal);

        return response()->json([
            'message' => "Murojaatingiz qabul qilindi. Ariza raqami: {$appeal->ticket}",
            'data'    => $this->present($appeal),
        ], 201);
    }

    /** Ariza raqami bo'yicha holatni tekshirish. */
    public function show($ticket)
    {
        $appeal = Appeal::where('ticket', $ticket)->first();

        if (!$appeal) {
            return response()->json(['message' => 'Bunday ariza raqami topilmadi'], 404);
        }

        return response()->json(['data' => $this->present($appeal)]);
    }

    /** Murojaatni frontend kutgan shaklga keltirish. */
    private function present(Appeal $appeal): array
    {
        $locale = App::getLocale();
        $types = Appeal::types();
        $statuses = Appeal::statuses();

        return [
            'id'           => $appeal->id,
            'ticket'       => $appeal->ticket,
            'type'         => $appeal->type,
            'type_label'   => $types[$appeal->type][$locale] ?? $types[$appeal->type]['uz'] ?? null,
            'status'       => $appeal->status,
            'status_label' => $statuses[$appeal->status][$locale] ?? $statuses[$appeal->status]['uz'] ?? null,
            'name'         => $appeal->name,
            'message'      => $appeal->message,
            'file'         => $appeal->fileUrl(),
            'answer'       => $appeal->answer,
            'answered_at'  => optional($appeal->answered_at)->toDateTimeString(),
            'created_at'   => $appeal->created_at->toDateTimeString(),
        ];
    }

    private function notifyTelegram(Appeal $appeal): void
    {
        // `env()` emas: `config:cache` dan keyin u null qaytaradi.
        $token = config('services.telegram.token');
        $chatId = config('services.telegram.chat_id');

        if (!$token || !$chatId) {
            return;
        }

        $text = "📨 Yangi murojaat ({$appeal->ticket})\n\n"
            . "📂 Turi: {$appeal->type}\n"
            . "👤 Ism: {$appeal->name}\n"
            . '📞 Telefon: ' . ($appeal->phone_number ?? 'Kiritilmagan') . "\n"
            . "💬 Xabar: {$appeal->message}";

        // Telegram javob bermasa ham murojaat saqlangan bo'ladi —
        // foydalanuvchiga xatolik ko'rsatilmasligi kerak.
        try {
            Http::timeout(5)->post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text'    => $text,
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
