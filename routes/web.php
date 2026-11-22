<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AffiliateController as AdminAffiliateController;
use App\Http\Controllers\Admin\BankTransactionController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CoachSkillController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DepositReviewController;
use App\Http\Controllers\Admin\HeroBannerController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\PaymentMethodController;
use App\Http\Controllers\Admin\PlayerController as AdminPlayerController;
use App\Http\Controllers\Admin\PlayerSeasonController;
use App\Http\Controllers\Admin\PopupController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ProductImageController;
use App\Http\Controllers\Admin\RevenueController;
use App\Http\Controllers\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SkillMoveController;
use App\Http\Controllers\Admin\WalletTransactionController;
use App\Http\Controllers\AffiliateController;
use App\Http\Controllers\AffiliateLinkController;
use App\Http\Controllers\Api\PaymentWebhookController;
use App\Http\Controllers\Api\RecentTransactionController;
use App\Http\Controllers\Api\SepayWebhookController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ToolController;
use App\Http\Controllers\TransactionHistoryController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/danh-muc', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/danh-muc/{category}', [CatalogController::class, 'category'])->name('catalog.category');
Route::get('/acc/{product}', [CatalogController::class, 'show'])->name('catalog.show');

Route::get('/dich-vu', [ServiceController::class, 'index'])->name('services.index');
Route::get('/gioi-thieu/{code}', AffiliateLinkController::class)->middleware('throttle:60,1')->where('code', '[A-Za-z0-9]{4,16}')->name('affiliate.track');

Route::get('/huong-dan', [PageController::class, 'guides'])->name('guides');
Route::get('/lich-su-giao-dich', [TransactionHistoryController::class, 'index'])->name('transactions.history');
Route::get('/lien-he', [ContactController::class, 'create'])->name('contact');
Route::post('/lien-he', [ContactController::class, 'store'])->middleware('throttle:5,10')->name('contact.store');
Route::get('/dieu-khoan', [PageController::class, 'terms'])->name('terms');
Route::get('/bao-mat', [PageController::class, 'privacy'])->name('privacy');
Route::get('/chinh-sach', [PageController::class, 'policies'])->name('policies');

Route::get('/cong-cu', [ToolController::class, 'index'])->name('tools.index');
Route::get('/cong-cu/{tool}', [ToolController::class, 'show'])->name('tools.show');
Route::get('/cong-cu/cau-thu/{player}', [ToolController::class, 'player'])->name('tools.player');

Route::get('/giao-dich-gan-day', [RecentTransactionController::class, 'index'])->name('transactions.recent');
Route::post('/webhook/thanh-toan', [PaymentWebhookController::class, 'store'])->name('payments.webhook');
Route::post('/webhook/sepay', [SepayWebhookController::class, 'store'])->middleware('throttle:120,1')->name('payments.sepay');

Route::middleware('guest')->group(function (): void {
    Route::get('/dang-ky', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/dang-ky', [RegisteredUserController::class, 'store']);
    Route::get('/dang-nhap', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/dang-nhap', [AuthenticatedSessionController::class, 'store']);
    Route::get('/quen-mat-khau', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/quen-mat-khau', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('/dat-lai-mat-khau/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/dat-lai-mat-khau', [NewPasswordController::class, 'store'])->name('password.update');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/dang-xuat', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/tai-khoan', [AccountController::class, 'show'])->name('account');
    Route::get('/tai-khoan/gioi-thieu', [AffiliateController::class, 'show'])->name('affiliate.show');
    Route::post('/tai-khoan/gioi-thieu/rut', [AffiliateController::class, 'withdraw'])->middleware('throttle:10,1')->name('affiliate.withdraw');
    Route::put('/tai-khoan', [AccountController::class, 'update'])->name('account.update');
    Route::put('/tai-khoan/mat-khau', [AccountController::class, 'updatePassword'])->name('account.password');

    Route::get('/nap-tien', [DepositController::class, 'index'])->name('deposit');
    Route::post('/nap-tien', [DepositController::class, 'store'])->name('deposits.store');
    Route::get('/nap-tien/{deposit}', [DepositController::class, 'show'])->name('deposits.show');
    Route::get('/nap-tien/{deposit}/trang-thai', [DepositController::class, 'status'])->name('deposits.status');
    Route::post('/nap-tien/{deposit}/huy', [DepositController::class, 'cancel'])->name('deposits.cancel');

    Route::get('/don-hang', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/don-hang/{order}', [OrderController::class, 'show'])->name('orders.show');

    Route::get('/mua/{product}', [PurchaseController::class, 'create'])->name('purchases.create');
    Route::post('/mua/{product}', [PurchaseController::class, 'store'])->name('purchases.store');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('/doanh-thu', RevenueController::class)->name('revenue');

    Route::resource('danh-muc', CategoryController::class)
        ->except(['show'])
        ->parameters(['danh-muc' => 'category'])
        ->names('categories');

    Route::resource('acc', AdminProductController::class)
        ->except(['show'])
        ->parameters(['acc' => 'product'])
        ->names('products');
    Route::post('/acc/{product}/anh', [ProductImageController::class, 'store'])->name('products.images.store');
    Route::put('/acc/{product}/anh/{image}/bia', [ProductImageController::class, 'makePrimary'])->name('products.images.primary');
    Route::delete('/acc/{product}/anh/{image}', [ProductImageController::class, 'destroy'])->name('products.images.destroy');

    Route::get('/nap-tien', [DepositReviewController::class, 'index'])->name('deposits.index');
    Route::post('/nap-tien/{deposit}/xac-nhan', [DepositReviewController::class, 'confirm'])->name('deposits.confirm');
    Route::post('/nap-tien/{deposit}/tu-choi', [DepositReviewController::class, 'reject'])->name('deposits.reject');

    Route::get('/don-hang', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/don-hang/{order}', [AdminOrderController::class, 'show'])->name('orders.show');

    Route::get('/thanh-vien', [MemberController::class, 'index'])->name('members.index');
    Route::get('/thanh-vien/{user}', [MemberController::class, 'show'])->name('members.show');

    Route::get('/giao-dich', [WalletTransactionController::class, 'index'])->name('wallet-transactions.index');
    Route::get('/giao-dich-ngan-hang', [BankTransactionController::class, 'index'])->name('bank-transactions.index');

    Route::get('/affiliate', [AdminAffiliateController::class, 'index'])->name('affiliates.index');
    Route::get('/affiliate/rut-tien', [AdminAffiliateController::class, 'withdrawals'])->name('affiliates.withdrawals');
    Route::post('/affiliate/rut-tien/{withdrawal}', [AdminAffiliateController::class, 'processWithdrawal'])->name('affiliates.withdrawals.process');

    Route::put('/banner-dau-trang', [HeroBannerController::class, 'update'])->name('hero.update');
    Route::delete('/banner-dau-trang', [HeroBannerController::class, 'destroy'])->name('hero.destroy');

    Route::resource('banner', BannerController::class)
        ->except(['show'])
        ->parameters(['banner' => 'banner'])
        ->names('banners');

    Route::resource('dich-vu', AdminServiceController::class)
        ->except(['show'])
        ->parameters(['dich-vu' => 'service'])
        ->names('services');

    Route::get('/cong-cu/cau-thu/mau-csv', [AdminPlayerController::class, 'template'])->name('tool-players.template');
    Route::post('/cong-cu/cau-thu/nhap-csv', [AdminPlayerController::class, 'import'])->name('tool-players.import');
    Route::resource('cong-cu/cau-thu', AdminPlayerController::class)
        ->except(['show'])
        ->parameters(['cau-thu' => 'player'])
        ->names('tool-players');
    Route::post('/cong-cu/mua-the', [PlayerSeasonController::class, 'store'])->name('tool-seasons.store');
    Route::put('/cong-cu/mua-the/{season}', [PlayerSeasonController::class, 'update'])->name('tool-seasons.update');
    Route::delete('/cong-cu/mua-the/{season}', [PlayerSeasonController::class, 'destroy'])->name('tool-seasons.destroy');
    Route::resource('cong-cu/ky-thuat', SkillMoveController::class)
        ->except(['show'])
        ->parameters(['ky-thuat' => 'skill'])
        ->names('tool-skills');
    Route::get('/cong-cu/ky-nang-hlv', [CoachSkillController::class, 'index'])->name('tool-coach-skills.index');
    Route::post('/cong-cu/ky-nang-hlv', [CoachSkillController::class, 'store'])->name('tool-coach-skills.store');
    Route::put('/cong-cu/ky-nang-hlv/{coachSkill}', [CoachSkillController::class, 'update'])->name('tool-coach-skills.update');
    Route::delete('/cong-cu/ky-nang-hlv/{coachSkill}', [CoachSkillController::class, 'destroy'])->name('tool-coach-skills.destroy');

    Route::resource('popup', PopupController::class)
        ->except(['show'])
        ->parameters(['popup' => 'popup'])
        ->names('popups');

    Route::get('/lien-he', [ContactMessageController::class, 'index'])->name('contact-messages.index');
    Route::post('/lien-he/{contactMessage}/xu-ly', [ContactMessageController::class, 'handle'])->name('contact-messages.handle');
    Route::delete('/lien-he/{contactMessage}', [ContactMessageController::class, 'destroy'])->name('contact-messages.destroy');

    /**
     * Admin dev only: system configuration, bank accounts, member balances/roles/locks and the audit log.
     */
    Route::middleware('can:manage-system')->group(function (): void {
        Route::put('/affiliate/cau-hinh', [AdminAffiliateController::class, 'updateSettings'])->name('affiliates.settings');
        Route::put('/affiliate/thanh-vien/{user}', [AdminAffiliateController::class, 'updateMember'])->name('affiliates.member');

        Route::resource('ngan-hang', PaymentMethodController::class)
            ->except(['show'])
            ->parameters(['ngan-hang' => 'payment_method'])
            ->names('payment-methods');

        Route::post('/thanh-vien/{user}/so-du', [MemberController::class, 'adjustBalance'])->name('members.balance');
        Route::put('/thanh-vien/{user}/quyen', [MemberController::class, 'updateRole'])->name('members.role');
        Route::post('/thanh-vien/{user}/khoa', [MemberController::class, 'toggleBan'])->name('members.ban');

        Route::get('/cau-hinh', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('/cau-hinh', [SettingController::class, 'update'])->name('settings.update');

        Route::get('/nhat-ky', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    });
});
