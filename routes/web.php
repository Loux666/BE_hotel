<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\HotelController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\ManagerController;

use App\Http\Controllers\BookingController;

use App\Http\Controllers\PaymentController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\RoomHoldController;
use App\Http\Controllers\FeedbackController;
use App\Models\Hotel;
use App\Models\Room;
use Illuminate\Support\Facades\Log;


Route::prefix('manager')->middleware(['auth', 'usertype:manager'])->group(function () {
    Route::get('/dashboard', [ManagerController::class, 'dashboard'])->name('manager.dashboard');
    Route::get('/booking', [ManagerController::class, 'showBooking'])->name('manager.showbooking');
    Route::get('/cancel', [ManagerController::class, 'showCancel'])->name('manager.showCancel');
    Route::post('/cancel-requests/{refund}/approve', [ManagerController::class, 'approveCancel'])->name('manager.cancel.approve');
    Route::get('/room', [ManagerController::class, 'showRoom'])->name('manager.showRoom');
    Route::get('/guest', [ManagerController::class, 'showGuest'])->name('manager.showGuest');
    Route::get('/feedback', [ManagerController::class, 'showFeedback'])->name('manager.showFeedback');
    Route::get('/maintenance', [ManagerController::class, 'showMaintenanceForm'])->name('manager.showMaintenance');
    Route::post('/maintenance/set', [ManagerController::class, 'setMaintenance'])->name('manager.setMaintenance');
    Route::get('/chart', [ManagerController::class, 'showChart'])->name('manager.showChart');
    Route::put('/bookingdetail/update/{id}', [ManagerController::class, 'updateBookingDetail'])->name('manager.booking_detail.update');
    Route::get('/room/edit/{id}', [ManagerController::class, 'editRoom'])->name('manager.rooms.edit');
    Route::post('/room/update/{id}', [ManagerController::class, 'updateRoom'])->name('manager.rooms.update');
    Route::post('/room/delete/{id}', [ManagerController::class, 'deleteRoom'])->name('manager.rooms.delete');
    Route::post('/manager/room-images/{id}', [ManagerController::class, 'deleteImage'])->name('manager.rooms.deleteImage');


    Route::post('room/add', [ManagerController::class, 'addRoom'])->name('manager.rooms.add');
    Route::get('room/create', [ManagerController::class, 'createRoom'])->name('manager.rooms.create');
    Route::get('/payment', [ManagerController::class, 'showPayment'])->name('manager.showPayment');
});

Route::middleware(['auth', 'usertype:admin'])->group(function () {
    Route::get('/manager/create', [AdminController::class, 'createManager'])->name('admin.manager.create');
    Route::post('/manager/store', [AdminController::class, 'storeManager'])->name('admin.manager.store');
    Route::get('/showmanager', [AdminController::class, 'showManager'])->name('admin.showManager');
    Route::get('/manager/edit/{id}', [AdminController::class, 'editManager'])->name('admin.manager.edit');
    Route::post('/manager/update/{id}', [AdminController::class, 'updateManager'])->name('admin.manager.update');
    Route::post('/manager/delete/{id}', [AdminController::class, 'deleteManager'])->name('admin.manager.delete');


    Route::get('/coupon/manage', [AdminController::class, 'couponManage'])->name('coupon.manage');
    Route::get('/coupon/add', [AdminController::class, 'addCoupon'])->name('coupon.add');
    Route::post('/coupon/store', [AdminController::class, 'storeCoupon'])->name('coupon.store');
    Route::get('/coupon/{id}/edit', [AdminController::class, 'editCoupon'])->name('coupon.edit');
    Route::post('/coupon/{id}/update', [AdminController::class, 'updateCoupon'])->name('coupon.update');
    Route::post('/coupon/delete/{id}', [AdminController::class, 'deleteCoupon'])->name('coupon.delete');

    route::get('/create_hotel', [HotelController::class, 'create_hotel']);
    route::post('/add_hotel', [HotelController::class, 'add_hotel']);
    Route::get('/search-hotels', [HotelController::class, 'searchHotels']);
    Route::get('/view_hotel', [HotelController::class, 'view_hotel']);
    Route::get('/delete_hotel/{id}', [HotelController::class, 'delete_hotel']);
    Route::get('/update_hotel/{id}', [HotelController::class, 'update_hotel']);
    Route::post('/edit_hotel/{id}', [HotelController::class, 'edit_hotel']);
    Route::get('/hotel/{id}/rooms', [HotelController::class, 'viewRooms'])->name('hotel.rooms');

    route::get('/create_room', [RoomController::class, 'create_room']);
    route::get('/view_room', [RoomController::class, 'view_room']);
    Route::get('/delete_room/{id}', [RoomController::class, 'delete_room']);
    route::post('/add_room', [RoomController::class, 'add_room']);
    route::get('/update_room/{id}', [RoomController::class, 'update_room']);

    Route::get('/showbooking', [AdminController::class, 'showBooking'])->name('admin.showBooking');

    Route::get('/refund', [AdminController::class, 'showRefund'])->name('admin.refund');
    Route::post('/admin/refunds/manual/{refund}', [AdminController::class, 'processManualRefund'])->name('admin.refunds.manual.process');
});

route::get('/', [AdminController::class, 'home']);
route::get('/home', [AdminController::class, 'index'])->name('home');
Route::get('/about', [AdminController::class, 'about'])->name('about');
Route::get('/room', [AdminController::class, 'room'])->name('room');
Route::get('/cart', [AdminController::class, 'cart'])->name('cart');
Route::get('/hotel/{id}', [AdminController::class, 'show']);
Route::get('/locations/search', [AdminController::class, 'liveSearch'])->name('locations.search');
Route::get('/recommend_room', [RoomController::class, 'recommend_room']);
Route::post('/edit_room/{id}', [RoomController::class, 'edit_room']);
Route::get('/room-filter', [RoomController::class, 'showAvailableRoomByCity'])->name('room.filter');
Route::get('/rooms_by_hotel{id}', [RoomController::class, 'roomsByHotel'])->name('rooms.by.hotel');
Route::get('/room-unavailable', [RoomController::class, 'unavailableToBook'])->name('room.unavailableToBook');
Route::get('/search-hotel', [HotelController::class, 'searchForm'])->name('hotel.search');
Route::get('/all_hotels', [HotelController::class, 'all_hotel'])->name('hotels.list');
Route::get('/hotels/city/{city}', [HotelController::class, 'listByCity'])->name('hotels.by_city');
Route::get('/hotels/{id}', [HotelController::class, 'show'])->name('hotel.show');
Route::get('/hotels/search/{city}', [HotelController::class, 'searchHotelsByCity']);
Route::get('/coupon', [AdminController::class, 'coupon'])->name('coupon');

//Cart
Route::middleware(['auth'])->prefix('cart')->group(function () {
    Route::get('/', [CartController::class, 'viewCart'])->name('cart.view');
    Route::post('/add', [CartController::class, 'addtoCart'])->name('cart.add');
    Route::delete('/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');
    Route::post('/verify', [CartController::class, 'verifyCart'])->name('cart.verify'); //verify trc khi bam dat phong trong cart
    Route::get('/booking/form', [BookingController::class, 'bookingwithCart'])->name('cart_booking.form'); //chuyen huong sang form
    Route::post('/booking/temp', [BookingController::class, 'storeTmpCart'])->name('cart.store.temp'); //luu data tam thoi
    Route::get('/payment', [BookingController::class, 'showPaymentCart'])->name('payment.page.cart'); //chuyen huong sang trang thanh toan
    Route::post('/apply-coupon', [BookingController::class, 'applyCouponCart'])->name('cart.applyCoupon'); //apply coupon cho form booking cua cart
    Route::get('/booking-history', [BookingController::class, 'history'])->name('booking.history'); //lich su dat phong
    Route::post('/feedback', [FeedbackController::class, 'store'])->name('feedback.store'); // trang danh gia
    Route::get('/payment/method/{booking_id}', [PaymentController::class, 'showPaymentOptionsCart'])->name('cart.payment.method');
    Route::post('/payment/init', [PaymentController::class, 'initPaymentCart'])->name('payment.init.cart');
    Route::post('/payment/payLater', [PaymentController::class, 'payLaterCart'])->name('payment.payLater.cart');
    Route::post('/booking/{booking}/cancel', [BookingController::class, 'cancel'])->name('booking.cancel');
    Route::post('/refund-submit', [BookingController::class, 'submit'])->name('refund.submit');
});

Route::post('/booking/verify', [BookingController::class, 'verifyRoomBeforeBooking'])->name('booking.verify'); //verify trc khi dat phong
Route::post('/apply-coupon', [BookingController::class, 'applyCoupon'])->name('booking.applyCoupon');
Route::get('/booking_form', [BookingController::class, 'bookingForm'])->name('booking.form'); //form dat phong
Route::post('/booking/storeTmp', [BookingController::class, 'storeTmp'])->name('booking.storeTmp'); // tao ban ghi booking tam thoi
Route::get('/payment/method/{booking_id}', [PaymentController::class, 'showPaymentOptions'])->name('payment.method'); //lua chon phuong thuc thanh toan
Route::post('/payment/init', [PaymentController::class, 'initPayment'])->name('payment.init');


//pay Later
Route::post('/payment/payLater', [PaymentController::class, 'payLater'])->name('payment.payLater');
//VNPAY
Route::post('/payment/vnpay', [PaymentController::class, 'payWithVnpay'])->name('payment.vnpay');
Route::get('/payment/vnpay/callback', [PaymentController::class, 'vnpayCallback'])->name('payment.vnpay.callback');
//VNPAY - CART
Route::post('/cart/payment/vnpay', [PaymentController::class, 'payWithVnpayFromCart'])->name('payment.vnpay.cart');
Route::get('/payment/vnpay/callback/cart', [PaymentController::class, 'vnpayCallbackCart'])->name('payment.vnpay.callback.cart');
Route::get('/payment/success/existing', [PaymentController::class, 'showExisting'])->name('payment.success.existing');

Route::get('/failed', function () {
    return view('home.payment_failed');
});
