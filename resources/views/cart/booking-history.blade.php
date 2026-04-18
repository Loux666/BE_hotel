<!-- Nội dung lịch sử -->
<div id="tab-history" class="cart-tab-content">
  @if ($bookings->isEmpty())
      <p>Bạn chưa đặt phòng nào.</p>
  @else
      @foreach ($bookings as $booking)
        <div class="card mb-4">
            <div class="card-header">
                Mã đặt phòng: #{{ $booking->id }}<br>
                Trạng thái: <strong>{{ $booking->payment_status == 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán' }}</strong><br>
                @if ($booking->status === 'cancelled')
                    <button type="button" class="btn btn-secondary mt-3" disabled>Đã hủy</button>

                @elseif ($booking->refundRequest && $booking->refundRequest->status === 'pending')
                    <button type="button" class="btn btn-warning mt-3" disabled>Đang đợi duyệt hủy</button>

                @elseif ($booking->refundRequest && $booking->refundRequest->status === 'rejected')
                    <button type="button" class="btn btn-outline-danger mt-3" disabled>Yêu cầu hủy bị từ chối</button>

                @else
                    <form method="POST" action="{{ route('booking.cancel', $booking->id) }}">
                        @csrf
                        <button type="submit" class="btn btn-danger mt-3">Hủy đặt phòng</button>
                    </form>
                @endif

            </div>
              
            <div class="card-body">
                <p><strong>Khách:</strong> {{ $booking->guest_name }} | Email: {{ $booking->guest_email }} | SĐT: {{ $booking->guest_phone }}</p>

                @foreach ($booking->booking_details as $detail)
                    @php
                        $today = \Carbon\Carbon::now();
                        $canFeedback = \Carbon\Carbon::parse($detail->checkout)->lt($today);

                        $feedback = \App\Models\Feedback::where('booking_detail_id', $detail->id)
                            ->where('user_id', Auth::id())
                            ->first();
                    @endphp

                    <div class="border p-2 mb-2">
                        <p><strong>Khách sạn ID:</strong> {{ $detail->hotel_id ?? 'N/A' }}</p>
                        <p><strong>Phòng:</strong> {{ $detail->room_name ?? 'N/A' }} (x{{ $detail->quantity }})</p>
                        <p><strong>Ngày nhận phòng:</strong> {{ \Carbon\Carbon::parse($detail->checkin)->translatedFormat('d \\t\\há\\n\\g m \\nă\\m Y') }}</p>
                        <p><strong>Ngày trả phòng:</strong> {{ \Carbon\Carbon::parse($detail->checkout)->translatedFormat('d \\t\\há\\n\\g m \\nă\\m Y') }}</p>
                        <p><strong>Giá mỗi đêm:</strong> {{ number_format($detail->price_per_night, 0, ',', '.') }} VND</p>
                        <p><strong>Ưu đãi:</strong> -{{number_format($detail->discount, 0, ',' , '.')}}</p>
                        <p><strong>Phải trả:</strong> {{number_format($detail->subtotal - $detail->discount , 0, ',' , '.')}} VND</p>

                        {{-- Feedback button --}}
                        @if ($canFeedback && $feedback)
                            <button class="btn btn-outline-secondary btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#viewFeedbackModal{{ $detail->id }}">
                                Xem đánh giá của bạn
                            </button>

                        @elseif ($canFeedback && !$feedback)
                        <div id="feedback-wrapper-{{ $detail->id }}">
                            <button class="btn btn-outline-primary btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#feedbackModal{{ $detail->id }}">
                                Đánh giá khách sạn
                            </button>
                        </div>
                        @endif
                            
                    </div>

                    {{-- Modal: Xem feedback --}}
                    @if ($canFeedback && $feedback)
                        <div class="modal fade" id="viewFeedbackModal{{ $detail->id }}" tabindex="-1" aria-hidden="true">
                          <div class="modal-dialog">
                            <div class="modal-content">
                              <div class="modal-header">
                                <h5 class="modal-title">Đánh giá của bạn</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                              </div>
                              <div class="modal-body">
                                <p><strong>Số sao:</strong> {{ $feedback->rating }} / 5</p>
                                <p><strong>Nội dung:</strong></p>
                                <div class="border p-2 rounded">{{ $feedback->content }}</div>
                                <p class="text-muted">Gửi lúc: {{ $feedback->created_at->format('d/m/Y H:i') }}</p>
                              </div>
                            </div>
                          </div>
                        </div>
                    @endif

                    {{-- Modal: Gửi feedback --}}
                    @if ($canFeedback && !$feedback)
                        <div class="modal fade" id="feedbackModal{{ $detail->id }}" tabindex="-1" aria-hidden="true">
                          <div class="modal-dialog">
                            <form class="modal-content feedback-form" method="POST" action="{{ route('feedback.store') }}">
                              @csrf
                              <input type="hidden" name="booking_detail_id" value="{{ $detail->id }}">
                              <input type="hidden" name="hotel_id" value="{{ $detail->hotel_id }}">

                              <div class="modal-header">
                                <h5 class="modal-title">Đánh giá khách sạn</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                              </div>
                              <div class="modal-body">
                                <div class="form-group">
                                  <label>Số sao</label>
                                  <select name="rating" class="form-control" required>
                                    @for ($i = 5; $i >= 1; $i--)
                                      <option value="{{ $i }}">{{ $i }} sao</option>
                                    @endfor
                                  </select>
                                </div>
                                <div class="form-group">
                                  <label>Nội dung</label>
                                  <textarea name="content" class="form-control" rows="4" required></textarea>
                                </div>
                              </div>
                              <div class="modal-footer">
                                <button type="submit" class="btn btn-primary">Gửi đánh giá</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                              </div>
                            </form>
                          </div>
                        </div>
                    @endif

                @endforeach
            </div>
        </div>
      @endforeach
  @endif
</div>
@if (session('show_refund_modal'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = new bootstrap.Modal(document.getElementById('refundModal'));
            modal.show();
        });
    </script>
@endif

<!-- Modal nhập lý do hủy -->
<div class="modal fade" id="refundModal" tabindex="-1" aria-labelledby="refundModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('refund.submit') }}">
            @csrf
            <input type="hidden" name="booking_id" value="{{ session('booking_id') }}">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="refundModalLabel">Lý do hủy đặt phòng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <textarea name="reason" class="form-control" rows="4" placeholder="Bạn có thể ghi lý do hủy (không bắt buộc)"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger">Xác nhận hủy</button>
                </div>
            </div>
        </form>
    </div>
</div>