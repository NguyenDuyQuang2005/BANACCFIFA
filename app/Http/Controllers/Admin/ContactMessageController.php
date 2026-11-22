<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactMessageController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status', 'open');

        return view('admin.contact-messages.index', [
            'status' => $status,
            'messages' => ContactMessage::query()
                ->with('handler')
                ->when($status === 'open', fn ($query) => $query->whereNull('handled_at'))
                ->when($status === 'handled', fn ($query) => $query->whereNotNull('handled_at'))
                ->latest('id')
                ->paginate(20)
                ->withQueryString(),
        ]);
    }

    public function handle(Request $request, ContactMessage $contactMessage, ActivityLogger $activityLogger): RedirectResponse
    {
        $handled = ! $contactMessage->isHandled();

        $contactMessage->update([
            'handled_at' => $handled ? now() : null,
            'handled_by' => $handled ? $request->user()->id : null,
        ]);

        $activityLogger->log(
            $handled ? 'contact.handled' : 'contact.reopened',
            ($handled ? 'Đã xử lý' : 'Mở lại').' liên hệ: '.$contactMessage->subject,
            $contactMessage,
        );

        return back()->with('status', $handled ? 'Đã đánh dấu đã xử lý.' : 'Đã mở lại liên hệ.');
    }

    public function destroy(ContactMessage $contactMessage, ActivityLogger $activityLogger): RedirectResponse
    {
        $contactMessage->delete();
        $activityLogger->log('contact.deleted', 'Xóa liên hệ: '.$contactMessage->subject, $contactMessage);

        return back()->with('status', 'Đã xóa liên hệ.');
    }
}
