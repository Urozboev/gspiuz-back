<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appeal;
use Illuminate\Http\Request;

/**
 * Murojaatlar admin paneli — ko'rish, holatni o'zgartirish, javob yozish.
 */
class AppealController extends Controller
{
    public $title = 'Murojaatlar';
    public $route_name = 'appeals';
    public $route_parameter = 'appeal';

    public function index(Request $request)
    {
        $query = Appeal::latest();

        if ($type = $request->query('type')) {
            $query->where('type', $type);
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('ticket', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        return view('app.appeals.index', [
            'title'           => $this->title,
            'route_name'      => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'appeals'         => $query->paginate(20)->withQueryString(),
            'types'           => Appeal::types(),
            'statuses'        => Appeal::statuses(),
            'filters'         => $request->only(['type', 'status', 'search']),
        ]);
    }

    public function show(Appeal $appeal)
    {
        return view('app.appeals.show', [
            'title'           => $this->title,
            'route_name'      => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'appeal'          => $appeal,
            'types'           => Appeal::types(),
            'statuses'        => Appeal::statuses(),
        ]);
    }

    public function update(Request $request, Appeal $appeal)
    {
        $data = $request->validate([
            'status' => 'required|string|in:' . implode(',', array_keys(Appeal::statuses())),
            'answer' => 'nullable|string|max:10000',
        ]);

        $appeal->status = $data['status'];
        $appeal->answer = $data['answer'] ?? null;

        if ($data['status'] === Appeal::STATUS_ANSWERED) {
            $appeal->answered_at = now();
            $appeal->answered_by = auth()->id();
        }

        $appeal->save();

        return back()->with([
            'success' => true,
            'message' => 'Murojaat yangilandi',
        ]);
    }

    public function destroy(Appeal $appeal)
    {
        $appeal->delete();

        return redirect()->route('appeals.index')->with([
            'success' => true,
            'message' => "Muvaffaqiyatli o'chirib tashlandi",
        ]);
    }
}
