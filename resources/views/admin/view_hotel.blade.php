<!DOCTYPE html>
<html>
<head>
  <base href="/public">
  @include('admin.css')
  <style type="text/css">
    label {
      display: inline-block;
      width: 200px;
    }
    .div_des {
      padding-top: 10px;
      margin: 5px;
    }
    .div_center {
      padding-top: 40px;
    }

    #hotel-suggestions {
      position: absolute;
      background: white;
      width: 620px;
      border: 1px solid #ccc;
      z-index: 10;
      max-height: 300px;
      overflow-y: auto;
      box-shadow: 0 2px 6px rgba(0,0,0,0.2);
    }

    .suggestion-item {
      display: flex;
      gap: 10px;
      padding: 10px;
      cursor: pointer;
      border-bottom: 1px solid #eee;
    }

    .suggestion-item:hover {
      background-color: #f0f0f0;
    }

    .hotel-info {
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .suggestion-item img {
      border-radius: 6px;
      object-fit: cover;
    }
  </style>
</head>
<body>
  @include('admin.header')
  @include('admin.sidebar')

  <div class="page-content">
    <div class="page-header">
      <div class="container-fluid">

        
        <form id="room-form" action="{{ route('hotel.search') }}"  method="GET" class="mb-4" autocomplete="off">
          <input type="text" id="hotel-search" placeholder="Nhập tên khách sạn" style="width: 620px;">
          <div id="hotel-error" style="color: red; margin-top: 5px;"></div>
          <input type="hidden" id="hotel_id" name="hotel_id">
          <div id="hotel-suggestions"></div>
          <button type="submit">Tìm kiếm</button>
        </form>

        
        <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
          <tr>
            <th style="padding: 8px"> Hotel ID</th>
            <th style="padding: 8px"> Name</th>
            <th style="padding: 8px"> Image</th>
            <th style="padding: 8px"> City</th>
            <th style="padding: 8px"> Address</th>
            <th style="padding: 8px"> Description</th>
            <th style="padding: 8px"> Rating</th>
            <th style="padding: 8px"> Phone</th>
            <th style="padding: 8px"> Email</th>
            <th colspan="3" style="padding: 8px">Actions</th>
          </tr>

          @foreach($data as $hotel)
          <tr style="background-color: white;" align="center">
            <td>{{ $hotel->id }}</td>
            <td>{{ $hotel->hotel_name }}</td>
            <td>
              <img src="/hotelImg/{{ $hotel->hotel_image }}" alt="" height="50" width="50">
            </td>
            <td>{{ $hotel->hotel_city }}</td>
            
            <td>{!! Str::limit($hotel->hotel_address, 30, '...') !!}</td>
            <td>{!! Str::limit($hotel->hotel_description, 20, '...') !!}</td>
            <td>{{ $hotel->hotel_rating }}</td>
            <td>{{ $hotel->hotel_phone }}</td>
            <td>{{ $hotel->hotel_email }}</td>
            
            <td><a class="btn btn-warning" href="{{ url('update_hotel', $hotel->id) }}">Update</a></td>
            <td><a class="btn btn-danger" href="{{ url('delete_hotel', $hotel->id) }}">Delete</a></td>
            <td><a class="btn btn-info" href="{{ route('hotel.rooms', $hotel->id) }}">All Room</a></td>
          </tr>
          @endforeach
        </table>

      </div>
    </div>
  </div>

  {{-- SCRIPT --}}
  <script>
    const input = document.getElementById('hotel-search');
    const suggestions = document.getElementById('hotel-suggestions');
    const hotelIdInput = document.getElementById('hotel_id');
    const errorDiv = document.getElementById('hotel-error');
    const form = document.getElementById('room-form');

    // Gợi ý khách sạn khi nhập
    input.addEventListener('input', function () {
      const query = this.value;
      hotelIdInput.value = ''; // reset ID
      errorDiv.innerText = '';

      if (query.length < 2) {
        suggestions.innerHTML = '';
        return;
      }

      fetch(`/search-hotels?query=${query}`)
        .then(res => res.json())
        .then(data => {
          let html = data.map(hotel => `
            <div class="suggestion-item" data-id="${hotel.id}" data-name="${hotel.hotel_name}">
              <img src="/hotelImg/${hotel.hotel_image}" alt="hotel" width="50" height="50">
              <div class="hotel-info">
                <div class="name-line"><strong>${hotel.hotel_name}</strong></div>
                <div class="address-line">${hotel.hotel_address ?? ''}</div>
                <div class="type-line">${hotel.hotel_city ?? ''}</div>
              </div>
            </div>
          `).join('');

          suggestions.innerHTML = html;
          suggestions.style.display = 'block';
        });
    });

    // Click chọn khách sạn
    suggestions.addEventListener('click', function (e) {
      const item = e.target.closest('.suggestion-item');
      if (item) {
        input.value = item.dataset.name;
        hotelIdInput.value = item.dataset.id;
        suggestions.innerHTML = '';
        suggestions.style.display = 'none';
      }
    });

    // Click ra ngoài thì ẩn suggestions
    document.addEventListener('click', function (e) {
      if (!input.contains(e.target) && !suggestions.contains(e.target)) {
        suggestions.innerHTML = '';
        suggestions.style.display = 'none';
      }
    });

    // Validate khi submit
    form.addEventListener('submit', function (e) {
      if (hotelIdInput.value === '') {
        e.preventDefault();
        errorDiv.innerText = 'Vui lòng chọn một khách sạn hợp lệ từ gợi ý.';
      }
    });
  </script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

<!-- Toastr JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    
    
    @if (Session::has('message'))
        <script>
            toastr.options = {
                progressBar: true, 
                closeButton: true
            };
            toastr.success("{{ Session::get('message') }}", 'Success', {
                timeOut: 12000,
                positionClass: 'toast-top-right'
            });
        </script>
    @elseif (Session::has('error'))
        <script>
            toastr.options = {
                progressBar: true, 
                closeButton: true
            };
            toastr.error("{{ Session::get('error') }}", 'Error', {
                timeOut: 12000,
                positionClass: 'toast-top-right'
            });
        </script>
    @endif

  {{-- Các script khác --}}
  <script src="admin/vendor/jquery/jquery.min.js"></script>
  <script src="admin/vendor/popper.js/umd/popper.min.js"></script>
  <script src="admin/vendor/bootstrap/js/bootstrap.min.js"></script>
  <script src="admin/vendor/jquery.cookie/jquery.cookie.js"></script>
  <script src="admin/vendor/chart.js/Chart.min.js"></script>
  <script src="admin/vendor/jquery-validation/jquery.validate.min.js"></script>
  <script src="admin/js/charts-home.js"></script>
  <script src="admin/js/front.js"></script>

  
</body>
</html>
