<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\MessageTemplate;
use Illuminate\Http\Request;

class MessageTemplateController extends Controller
{
    public function index(Request $request)
    {
        $global = MessageTemplate::whereNull('user_id')->get();
        $userTemplates = MessageTemplate::where('user_id', $request->user()->id)->get();
        
        return response()->json([
            'global' => $global,
            'custom' => $userTemplates,
            'can_create' => $request->user()->is_premium || $userTemplates->count() < 5
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        
        if (!$user->is_premium && MessageTemplate::where('user_id', $user->id)->count() >= 5) {
            abort(403, 'Batas maksimal 5 template khusus. Upgrade ke Pro untuk slot tak terbatas.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required|string'
        ]);

        $template = MessageTemplate::create([
            'user_id' => $user->id,
            'name' => $validated['name'],
            'content' => $validated['content']
        ]);

        return response()->json($template);
    }
    
    public function destroy(Request $request, MessageTemplate $messageTemplate)
    {
        if ($messageTemplate->user_id !== $request->user()->id) abort(403);
        $messageTemplate->delete();
        return response()->json(['success' => true]);
    }
}
