<!-- Nội dung giỏ hàng -->
<div id="tab-cart" class="cart-tab-content active">
  @if ($cartItems->isEmpty())
    <div class="cart-empty-message">
      🛒 Giỏ hàng của bạn đang trống.
    </div>
    <div class="cart-empty-button">
      <a href="{{ route('home') }}" class="btn btn-primary">🔍 Tìm khách sạn</a>
    </div>
  @endif

  @foreach ($cartItems as $item)
  <div class="cart-item">
    <div class="cart-header">
      <div class="cart-tag">🏢 {{ $item->room->hotel->hotel_name ?? 'Tên khách sạn' }}</div>
      <div class="cart-remove">
        <form method="POST" action="{{ route('cart.remove', $item->id) }}">
          @csrf
          @method('DELETE')
          <button type="submit">🗑️ Xóa</button>
       </form>
      </div>
    </div>

    <div class="cart-body">
      <input
        type="checkbox"
        class="cart-checkbox"
        name="selected_rooms[]"
        value="{{ $item->id }}"
        data-price="{{ $item->price }}"
        {{ (!$item->is_available_now || $item->is_expired) ? 'disabled' : '' }}
        checked
      >

      <img src="{{ asset('hotelImg/' . ($item->room->hotel->hotel_image ?? 'default.jpg')) }}" alt="Hotel" class="cart-image">

      <div class="cart-info">
        <div class="hotel-name">
          <a href="{{ route('rooms.by.hotel', $item->room->hotel->id) }}" class="hotel-link">
            {{ $item->room->hotel->hotel_name }}
          </a>
        </div>
        <div class="hotel-sub">
          <span class="stars">⭐⭐</span>
          <span class="location">📍 {{ $item->room->hotel->hotel_name }}</span>
        </div>
        <div class="rating">💬 <span class="score">7.5</span> Rất tốt · 280 nhận xét</div>
      </div>

      <div class="cart-details">
        <div class="room-name">1 x {{ $item->room->room_name }} cho {{ $item->room->capacity }} người </div>
        <div class="date">
          📅 {{ \Carbon\Carbon::parse($item->checkin)->translatedFormat('d \\t\\há\\n\\g m \\nă\\m Y') }}
          —
          {{ \Carbon\Carbon::parse($item->checkout)->translatedFormat('d \\t\\há\\n\\g m \\nă\\m Y') }}
        </div>
      </div>

      <div class="cart-price">
        <div class="amount">{{ number_format($item->price, 0, ',', '.') }}  ₫</div>
        <div class="note" style="font-size:10px;font-style:italic">Bao gồm thuế và phí</div>

        @if ($item->is_expired)
          <div class="unavailable-note" style="color: red; font-weight: bold;">
            ⚠ Phòng không thể đặt vì ngày nhận phòng đã qua
          </div>
        @elseif(!$item->is_available_now)
          <div class="unavailable-note" style="color: red; font-weight: bold;">
            ⚠ Phòng đã hết cho thời gian này
          </div>
        @endif
      </div>
    </div>
  </div>
  @endforeach
</div>