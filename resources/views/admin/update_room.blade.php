<!DOCTYPE html>
<html>
<head> 
    <base href="/public">
    @include('admin.css')

<style type="text/css">
    label{
        display: inline-block;
        width: 200px;
    }
    .div_des{
        padding-top: 10px;
        margin: 5px;
    }
    .div_center{
        
        padding-top: 40px;
    }
    
    #hotel-suggestions {
    position: absolute;
    background: white;
    width: 50%;
    border: 1px solid #ccc;
    z-index: 10;
    max-height: 300px;
    overflow-y: auto;
    }

    .suggestion-item {
        display: flex;
        gap: 10px;
        padding: 10px;
        cursor: pointer;
    }

    .suggestion-item:hover {
        background-color: #f0f0f0;
    }
</style>
</head>
  <body>
    @include('admin.header')   
    
    @include('admin.sidebar')
    <div class="page-content">
        <div class="page-header">
          <div class="container-fluid">

            <div class="div_center">
                <h1 style="font-size:30px; font-weight:bold; ">Update phòng</h1>
                <form action="{{url('edit_room',$room->id)}}" method="Post" enctype="multipart/form-data" id="room-form">

                    @csrf
                    <input type="text" id="hotel-search" value="{{$room->hotel->hotel_name}}" style="width: 620px" readonly>
                    <div id="hotel-error" style="color: red; margin-top: 5px;"></div>
                    <input type="hidden" id="hotel_id" name="hotel_id" value="{{ $room->hotel_id }}">
                    <div id="hotel-suggestions" class="suggestion-box"></div>
                    <div class="div_des">
                        <label>Room Name</label>
                        <input type="text" name="room_name" id="room_name" value="{{$room->room_name}}" >
                        <div id="error-room_name" style="color: red; margin-top: 5px;"></div>
                    </div>
                    <div class="div_des">
                        <label>Current Image</label>
                        <td>
                            @if($room->images->isNotEmpty())
                                @foreach($room->images as $image)
                                    <img src="/roomImg/{{ $image->image_path }}" alt="" height="50" width="50">
                                @endforeach
                            @else
                                No Image
                            @endif
                        </td>
                    </div>
                    <div class="div_des">
                        <label>Upload Image</label>
                        <input type="file" name="room_image[]" multiple >
                    </div>
                    <div class="div_des">
                        <label>Price</label>
                        <input type="number" name="price" id="price" min="0" step="0.01" value="{{$room->price}}" >
                        <div id="error-price" style="color: red; margin-top: 5px;"></div>
                    </div>
                    <div class="div_des">
                        <label>Description</label>
                        <textarea name="description">
                            {{$room->description}}
                        </textarea>
                    </div>
                    
                    <div class="div_des">
                        <label for="">Wifi</label>
                        <select name="wifi">
                            <option value="{{$room->wifi}}">{{$room->wifi}}</option>
                            <option value="yes">Có</option>
                            <option value="no">Không</option>                          
                        </select>
                        
                    </div>
                    <div class="div_des">
                        <label>Capacity</label>
                        <input type="number" name="capacity" id="capacity" min="1" max="10" style="width: 100px " value="{{$room->capacity}}">
                        <div id="error-capacity" style="color: red; margin-top: 5px;"></div>
                        
                    </div>
                    <div class="div_des">
                        <label>Room Type</label>
                        <select name="room_type" style="width: 150px ">
                            <option value="{{$room->type}}">{{$room->type}}</option>
                            <option value="standard">Standard</option>
                            <option value="family">Family</option>
                            <option value="deluxe">Deluxe</option>
                        </select>
    
                    </div>
                    <div class="div_des">
                        <label>Number of Room</label>
                        <input type="number" name="total_rooms" id="total_rooms" min="1" step="1" style="width: 100px"  value="{{$room->total_rooms}}">
                        <div id="error-total_room" style="color: red; margin-top: 5px;"></div>
                        
                    </div>
                    <div class="div_des">
                        <label>Status</label>
                        <select name="status" style="width: 150px ">
                            <option value="{{$room->status}}">{{$room->status}}</option>
                            <option value="active">active</option>
                            <option value="unavailable">unavailable</option>
                        </select>
                        
                        <div id="error-total_room" style="color: red; margin-top: 5px;"></div>
                        
                    </div>
                    <div class="div_des" >
                        <input class="btn btn-primary" type="submit" value="save ">
                    </div>

                    
                </form>
            </div>

          </div>
        </div>
    </div>
      
        @include('admin.footer')
      </div>
    </div>
    <!-- JavaScript files-->
    <script src="admin/vendor/jquery/jquery.min.js"></script>
    <script src="admin/vendor/popper.js/umd/popper.min.js"> </script>
    <script src="admin/vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="admin/vendor/jquery.cookie/jquery.cookie.js"> </script>
    <script src="admin/vendor/chart.js/Chart.min.js"></script>
    <script src="admin/vendor/jquery-validation/jquery.validate.min.js"></script>
    <script src="admin/js/charts-home.js"></script>
    <script src="admin/js/front.js"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

<!-- Toastr JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    
    
    @if (Session::has('message'))
    <script>
        const navType = performance.getEntriesByType("navigation")[0]?.type;
        if (navType !== "back_forward") {
            toastr.options = {
                progressBar: true,
                closeButton: true
            };
            toastr.success("{{ Session::get('message') }}", 'Success', {
                timeOut: 12000,
                positionClass: 'toast-top-right'
            });
        }
    </script>
    @php Session::forget('message'); @endphp
    @elseif (Session::has('error'))
    <script>
        const navType = performance.getEntriesByType("navigation")[0]?.type;
        if (navType !== "back_forward") {
            toastr.options = {
                progressBar: true,
                closeButton: true
            };
            toastr.error("{{ Session::get('error') }}", 'Error', {
                timeOut: 12000,
                positionClass: 'toast-top-right'
            });
        }
    </script>
    @php Session::forget('error'); @endphp
    @endif

    

    <script>
        
        const input = document.getElementById('hotel-search');
        const suggestions = document.getElementById('hotel-suggestions');
        const hotelIdInput = document.getElementById('hotel_id');
        const errorDiv = document.getElementById('hotel-error');
        const form = document.getElementById('room-form');
    
        
        // Validate khi submit
        form.addEventListener('submit', function (e) {
            let hasError = false;

            // reset lỗi cũ
            document.getElementById('hotel-error').innerText = '';
            document.getElementById('error-room_name').innerText = '';
            document.getElementById('error-price').innerText = '';
            document.getElementById('error-capacity').innerText = '';

            // Validate hotel_id
            if (hotelIdInput.value === '') {
                document.getElementById('hotel-error').innerText = 'Vui lòng chọn một khách sạn hợp lệ từ gợi ý.';
                hasError = true;
            }

            // Validate room_name
            const roomName = document.getElementById('room_name').value.trim();
            if (!roomName) {
                document.getElementById('error-room_name').innerText = 'Tên phòng là bắt buộc.';
                hasError = true;
            } else if (roomName.length > 255) {
                document.getElementById('error-room_name').innerText = 'Tên phòng không được vượt quá 255 ký tự.';
                hasError = true;
            }

            // Validate price
            const price = document.getElementById('price').value;
            if (!price) {
                document.getElementById('error-price').innerText = 'Giá phòng là bắt buộc.';
                hasError = true;
            } else if (isNaN(price) || Number(price) < 0) {
                document.getElementById('error-price').innerText = 'Giá phòng phải là số lớn hơn hoặc bằng 0.';
                hasError = true;
            }

            // Validate capacity
            const capacity = document.getElementById('capacity').value.trim();
            if (!capacity) {
                document.getElementById('error-capacity').innerText = 'Sức chứa là bắt buộc.';
                hasError = true;
            } else if (!Number.isInteger(Number(capacity)) || Number(capacity) < 1 || Number(capacity) > 10) {
                document.getElementById('error-capacity').innerText = 'Sức chứa phải là số nguyên từ 1 đến 10.';
                hasError = true;
            }

            if (hasError) {
                e.preventDefault();
            }
        });

            
            

    </script>
</body>
</html>