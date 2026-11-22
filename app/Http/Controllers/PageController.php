<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PageController extends Controller
{
    public function guides(): View
    {
        return view('pages.placeholder', [
            'title' => 'Hướng dẫn',
            'heading' => 'Hướng dẫn sử dụng',
            'message' => 'Module hướng dẫn và bài viết sẽ được hoàn thiện ở Giai đoạn 4. Nội dung sẽ do Admin quản lý, không tự sinh bài SEO.',
        ]);
    }

    public function posts(): View
    {
        return view('pages.placeholder', [
            'title' => 'Bài viết',
            'heading' => 'Bài viết',
            'message' => 'Danh sách bài viết, danh mục và SEO sẽ được triển khai ở Giai đoạn 4 khi có nội dung thật từ Admin.',
        ]);
    }

    public function terms(): View
    {
        return view('pages.placeholder', [
            'title' => 'Điều khoản',
            'heading' => 'Điều khoản sử dụng',
            'message' => 'Nội dung điều khoản sẽ do bạn cung cấp và quản lý trong Admin. Trang này chưa chứa điều khoản pháp lý chính thức.',
        ]);
    }

    public function privacy(): View
    {
        return view('pages.placeholder', [
            'title' => 'Bảo mật',
            'heading' => 'Chính sách bảo mật',
            'message' => 'Chính sách bảo mật sẽ được bổ sung khi có nội dung chính thức. Trang quản trị và trang cá nhân sẽ được đánh dấu noindex.',
        ]);
    }

    public function policies(): View
    {
        return view('pages.placeholder', [
            'title' => 'Chính sách',
            'heading' => 'Chính sách giao dịch',
            'message' => 'Chính sách mua bán, bảo hành và hoàn tiền sẽ được soạn trong Admin ở giai đoạn nội dung.',
        ]);
    }
}
