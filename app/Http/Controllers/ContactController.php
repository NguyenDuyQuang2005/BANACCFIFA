<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function create(Request $request): View
    {
        return view('pages.contact', [
            'user' => $request->user(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (filled($request->input('website'))) {
            return redirect()->route('contact')->with('status', 'Đã gửi liên hệ. Chúng tôi sẽ phản hồi sớm.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'required_without:phone', 'email', 'max:255'],
            'phone' => ['nullable', 'required_without:email', 'string', 'max:30', 'regex:/^[0-9+\s.\-]{8,30}$/'],
            'subject' => ['required', 'string', 'max:180'],
            'message' => ['required', 'string', 'min:10', 'max:3000'],
        ], [
            'email.required_without' => 'Vui lòng nhập email hoặc số điện thoại.',
            'phone.required_without' => 'Vui lòng nhập email hoặc số điện thoại.',
        ]);

        ContactMessage::query()->create([
            ...$validated,
            'user_id' => $request->user()?->id,
        ]);

        return redirect()->route('contact')->with('status', 'Đã gửi liên hệ. Chúng tôi sẽ phản hồi sớm.');
    }
}
