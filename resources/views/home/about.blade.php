   @include('home.css')
   <div class="header">
      <div class="container">
         <div class="row">
            <div class="col-xl-3 col-lg-3 col-md-3 col-sm-3 col logo_section">
               <div class="full">
                  <div class="center-desk">
                     <div class="logo">
                        <a href="."><img src="images/logo.png" alt="#" /></a>
                     </div>
                  </div>
               </div>
            </div>
            <div class="col-xl-9 col-lg-9 col-md-9 col-sm-9" >
               <nav class="navigation navbar navbar-expand-md navbar-dark  " style="display: flex; justify-content: center;">
                  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExample04" aria-controls="navbarsExample04" aria-expanded="false" aria-label="Toggle navigation">
                  <span class="navbar-toggler-icon"></span>
                  </button>
                  <div class="collapse navbar-collapse" id="navbarsExample04" >
                     <ul class="navbar-nav mr-auto"  >
                        <li class="nav-item " >
                           <a class="nav-link" style="font-size: 14px "href="/">Trang chủ</a>
                        </li>
                        <li class="nav-item active">
                           <a class="nav-link"style="font-size: 14px" href="{{url('coupon')}}">ưu đãi</a>
                        </li>
                        
  
  
  
                       @if (Route::has('login'))
                       
                          @auth
                          <x-app-layout>
      
                          </x-app-layout>
                          
                       @else
                          <li class="nav-item" style="padding-right: 10px; ">
                             <a class="btn btn-primary" style="font-size: 14px" href="{{url('login')}}">Đăng nhập</a>
                          </li>
  
                       @if (Route::has('register'))
                          <li class="nav-item" >
                             <a class="btn btn-success"  style="font-size: 14px" href="{{url('register')}}">Đăng kí</a>
                          </li>
                       @endif
  
                          @endauth
                       
                       @endif
                        
  
                       <li class="nav-item" style="margin-top:5px; padding-left:20px" >
                          <a href="cart.html" >
                             <img src="images/cart_logo.png" width="20px" height="20px" >
                          </a>
                       </li>
                     </ul>
                  </div>
               </nav>
            </div>
         </div>
      </div>
   </div>
   <!-- about -->
   <div class="back_re">
      <div class="container">
         <div class="row">
            <div class="col-md-12">
               <div class="title">
                  <h2>About Us</h2>
               </div>
            </div>
         </div>
      </div>
   </div>
   <div class="about">
      <div class="container-fluid">
         <div class="row">
            <div class="col-md-5">
               <div class="titlepage">

                  <p class="margin_0">The passage experienced a surge in popularity during the 1960s when Letraset used
                     it on their dry-transfer sheets, and again during the 90s as desktop publishers bundled the text
                     with their software. Today it's seen all around the web; on templates, websites, and stock designs.
                     Use our generator to get your own, or read on for the authoritative history of lorem ipsum. </p>
                  <a class="read_more" href="Javascript:void(0)"> Read More</a>
               </div>
            </div>
            <div class="col-md-7">
               <div class="about_img">
                  <figure><img src="images/about.png" alt="#" /></figure>
               </div>
            </div>
         </div>
      </div>
   </div>
   <!-- end about -->


   <!--  footer -->
   @include('home.footer')
   <!-- end footer -->
   <!-- Javascript files-->
   <script src="js/jquery.min.js"></script>
   <script src="js/bootstrap.bundle.min.js"></script>
   <script src="js/jquery-3.0.0.min.js"></script>
   <!-- sidebar -->
   <script src="js/jquery.mCustomScrollbar.concat.min.js"></script>
   <script src="js/custom.js"></script>
</body>

</html>