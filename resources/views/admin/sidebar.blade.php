<div class="d-flex align-items-stretch">
    <!-- Sidebar Navigation-->
    <nav id="sidebar">
      <!-- Sidebar Header-->
      <div class="sidebar-header d-flex align-items-center">
        <div class="avatar"><img src="admin/img/admin.jpg" alt="..." class="img-fluid rounded-circle"></div>
        <div class="title">
          <h1 class="h5">Quang Nguyen</h1>
          <p>Pro</p>
        </div>
      </div>
      <!-- Sidebar Navidation Menus--><span class="heading">Main</span>
      <ul class="list-unstyled">
              <li ><a href="{{url('home')}}"> <i class="icon-home"></i>Home </a></li>
              <li>
                <a href="#hotelDropdown" aria-expanded="false" data-toggle="collapse">
                  <i class="icon-windows"></i>Hotel
                </a>
                <ul id="hotelDropdown" class="collapse list-unstyled">
                  <li><a href="{{url('create_hotel')}}">Add Hotel</a></li>
                  
                  <li><a href="{{url('view_hotel')}}">View Hotel</a></li>
                </ul>
              </li>
              <li>
                <a href="#managerDropdown" aria-expanded="false" data-toggle="collapse">
                  <i class="icon-windows"></i>Manager
                </a>
                <ul id="managerDropdown" class="collapse list-unstyled">
                  <li><a href="{{url('manager/create')}}">Create Manager</a></li>
                    
                  <li><a href="{{route('admin.showManager')}}">View Managers</a></li>
                </ul>
              </li>
              
              <li>
                <a href="#roomDropdown" aria-expanded="false" data-toggle="collapse">
                  <i class="icon-windows"></i>Room
                </a>
                <ul id="roomDropdown" class="collapse list-unstyled">
                  <li><a href="{{url('create_room')}}">Add Room</a></li>
                    
                  <li><a href="{{url('view_room')}}">View Room</a></li>
                </ul>
              </li>
              <li>
                <a href="#bookingDropdown" aria-expanded="false" data-toggle="collapse">
                  <i class="icon-windows"></i>Booking
                </a>
                <ul id="bookingDropdown" class="collapse list-unstyled">
                  <li><a href="{{route('admin.showBooking')}}">Booking</a></li>
                    
                  <li><a href="{{route('admin.refund')}}">Refund</a></li>
                </ul>
              </li>
              <li>
                <a href="#couponDropdown" aria-expanded="false" data-toggle="collapse">
                  <i class="icon-windows"></i>Coupon
                </a>
                <ul id="couponDropdown" class="collapse list-unstyled">
                  <li><a href="{{route('coupon.manage')}}">Coupon</a></li>
                    
                  
                </ul>
              </li>
    </nav>