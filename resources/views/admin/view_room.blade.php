<!DOCTYPE html>
<html>
  <head> 
    <base href="/public">
    @include('admin.css')
  </head>
  <body>
    @include('admin.header')   
    
    @include('admin.sidebar')
      
      
    @include('admin.footer')
    
    <div class="page-content">
        <div class="page-header">
          <div class="container-fluid">
            <table style="width: 100%; border-collapse: collapse; margin-top: 20px; ">
                <tr >
                    <th style="padding: 10px;"> Hotel</th>
                    
                    <th style="padding: 10px;"> Room Name</th>
                    <th style="padding: 10px;"> Image</th>
                    <th style="padding: 10px;"> Price</th>
                    <th style="padding: 10px;"> Discription</th>
                    <th style="padding: 10px;"> Room Type</th>
                    <th style="padding: 10px;"> Capacity</th>
                    <th style="padding: 10px;"> Total rooms</th>
                    
                    
                    
                    
                    
                    <th style="padding: 10px;">Action</th>
                </tr>

                @foreach($data as $room)
                <tr align="center" style="background-color: white; ">
                    <td>{{$room->hotel->hotel_name}}</td>
                    
                    <td>{{$room->room_name}}</td>
                    <td>
                        @if($room->images->isNotEmpty())
                            <img src="/roomImg/{{ $room->images->first()->image_path }}" alt="" height="50" width="50">
                        @else
                            No Image
                        @endif
                    </td>
                    
                    <td>{{$room->price}}</td>
                    <td>{!! Str::limit($room->description, 50, '...') !!}</td>
                    <td>{{$room->type}}</td>
                    <td>{{$room->capacity}}</td>
                    <td>{{$room->total_rooms}}</td>
                    
                    
                    <td><a class="btn btn-warning" href="{{url('update_room', $room->id)}}">Update</a></td>
                    <td><a class="btn btn-danger" href="{{url('delete_room', $room->id)}}">Delete</a></td>
                    
                </tr>
                @endforeach

            </table>


          </div>
        </div>
    </div>



    
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
    <script src="admin/vendor/jquery/jquery.min.js"></script>
    <script src="admin/vendor/popper.js/umd/popper.min.js"> </script>
    <script src="admin/vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="admin/vendor/jquery.cookie/jquery.cookie.js"> </script>
    <script src="admin/vendor/chart.js/Chart.min.js"></script>
    <script src="admin/vendor/jquery-validation/jquery.validate.min.js"></script>
    <script src="admin/js/charts-home.js"></script>
    <script src="admin/js/front.js"></script>
  </body>
</html>