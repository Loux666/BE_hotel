<!DOCTYPE html>
<html lang="en">
   <head>
@include('home.css')
   </head>
   <!-- body -->
   <body>
      <header>
        @include('home.header')
        
      </header>
      @include('home.banner')
      <!-- our_room -->
      @include('home.room', ['rooms' => $rooms])
      <!-- end our_room -->
      <!-- gallery -->
      <!-- end blog -->
      <!--  contact -->
      @include('home.contact')
      <!-- end contact -->
      <!--  footer -->
      <footer>
         @include('home.footer')
      </footer>
      <!-- end footer -->
      <!-- Javascript files-->
      

      <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
      <script src="js/bootstrap.bundle.min.js"></script>
      
      <!-- sidebar -->
      <script src="js/jquery.mCustomScrollbar.concat.min.js"></script>
      <script src="js/custom.js"></script>
      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>



   </body>
</html>