<div class="page-content">
    <div class="page-header">
      <div class="container-fluid">
        <h2 class="h5 no-margin-bottom">Welcome Back, Quang</h2>
      </div>
    </div>
    <section class="no-padding-top no-padding-bottom">
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-3 col-sm-6">
            <div class="statistic-block block">
              <div class="progress-details d-flex align-items-end justify-content-between">
                <div class="title">
                  <div class="icon"><i class="icon-user-1"></i></div><strong>Khách hàng mới</strong>
                </div>
                <div class="number dashtext-1">{{$totalUsers}}</div>
              </div>
              <div class="progress progress-template">
                <div role="progressbar" style="width: 30%" aria-valuenow="30" aria-valuemin="0" aria-valuemax="100" class="progress-bar progress-bar-template dashbg-1"></div>
              </div>
            </div>
          </div>
          <div class="col-md-3 col-sm-6">
            <div class="statistic-block block">
              <div class="progress-details d-flex align-items-end justify-content-between">
                <div class="title">
                  <div class="icon"><i class="icon-contract"></i></div><strong>Số lượng đơn đặt hàng</strong>
                </div>
                <div class="number dashtext-2">{{$confirmedBookings}}</div>
              </div>
              <div class="progress progress-template">
                <div role="progressbar" style="width: 70%" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100" class="progress-bar progress-bar-template dashbg-2"></div>
              </div>
            </div>
          </div>
          <div class="col-md-3 col-sm-6">
            <div class="statistic-block block">
              <div class="progress-details d-flex align-items-end justify-content-between">
                <div class="title">
                  <div class="icon"><i class="icon-paper-and-pencil"></i></div><strong>Phòng  hoạt động</strong>
                </div>
                <div class="number dashtext-3">{{$totalRooms}}</div>
              </div>
              <div class="progress progress-template">
                <div role="progressbar" style="width: 55%" aria-valuenow="55" aria-valuemin="0" aria-valuemax="100" class="progress-bar progress-bar-template dashbg-3"></div>
              </div>
            </div>
          </div>
          <div class="col-md-3 col-sm-6">
            <div class="statistic-block block">
              <div class="progress-details d-flex align-items-end justify-content-between">
                <div class="title">
                  <div class="icon"><i class="icon-writing-whiteboard"></i></div><strong>Khách sạn hoạt động</strong>
                </div>
                <div class="number dashtext-4">{{$totalHotels}}</div>
              </div>
              <div class="progress progress-template">
                <div role="progressbar" style="width: 35%" aria-valuenow="35" aria-valuemin="0" aria-valuemax="100" class="progress-bar progress-bar-template dashbg-4"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    
<div class="container-fluid mt-4 mb-4">
  <!-- Hàng 1: Doanh thu & Lượt booking -->
  <div class="row mb-5">
    <div class="col-md-6">
      <canvas id="revenueChart"></canvas>
    </div>
    <div class="col-md-6">
      <canvas id="bookingChart"></canvas>
    </div>
  </div>

  <!-- Hàng 2: Hotel chart và 2 biểu đồ tròn -->
  <div class="row">
    <!-- Hotel chart -->
    <div class="col-md-6">
      <canvas id="hotelChart"></canvas>
    </div>

    <!-- 2 biểu đồ tròn -->
    <div class="col-md-6 d-flex flex-column align-items-center gap-5">

      <!-- Status Pie Chart -->
      <div class="d-flex flex-column align-items-center">
        <div style="width: 280px; height: 280px;">
          <canvas id="statusChart"></canvas>
        </div>
        <div class="mt-2 font-weight-bold">Tỉ lệ đặt phòng thành công / tỉ lên hủy đơn</div>
      </div>

      <!-- Room Rate Doughnut Chart -->
      

    </div>
  </div>
</div>


  </div>
</div>





<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
<script>
  // Revenue Chart
  new Chart(document.getElementById("revenueChart"), {
    type: 'bar',
    data: {
      labels: Object.keys(@json($monthlyRevenue)).map(m => 'Tháng ' + m),
      datasets: [{
        label: 'Doanh thu theo tháng',
        data: Object.values(@json($monthlyRevenue)),
        backgroundColor: 'rgba(54, 162, 235, 0.6)'
      }]
    }
  });

  // Booking Chart
  new Chart(document.getElementById("bookingChart"), {
    type: 'line',
    data: {
      labels: Object.keys(@json($monthlyBookings)).map(m => 'Tháng ' + m),
      datasets: [{
        label: 'Số lượt booking',
        data: Object.values(@json($monthlyBookings)),
        backgroundColor: 'rgba(75, 192, 192, 0.6)',
        fill: false,
        borderColor: 'rgba(75, 192, 192, 1)',
      }]
    }
  });

  // Status Chart
  new Chart(document.getElementById("statusChart"), {
  type: 'pie',
  data: {
    labels: ['Đã xác nhận', 'Đã huỷ'],
    datasets: [{
      data: [{{ $confirmed }}, {{ $cancelled }}],
      backgroundColor: ['#28a745', '#dc3545'],
    }]
  },
  options: {
    plugins: {
      datalabels: {
        color: '#fff',
        formatter: (value, ctx) => {
          const sum = ctx.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
          const percentage = (value / sum * 100).toFixed(1) + "%";
          return percentage;
        }
      }
    }
  },
  plugins: [ChartDataLabels]
});

  // Hotel Chart
  new Chart(document.getElementById("hotelChart"), {
    type: 'bar',
    data: {
      labels: @json($bookingsByHotel->pluck('hotel_name')),
      datasets: [{
        label: 'Top khách sạn theo lượt booking',
        data: @json($bookingsByHotel->pluck('total')),
        backgroundColor: 'rgba(255, 159, 64, 0.6)'
      }]
    }
  });

  // Room rate chart
  new Chart(document.getElementById("roomRateChart"), {
    type: 'doughnut',
    data: {
      labels: ['Đã đặt', 'Còn trống'],
      datasets: [{
        data: [{{ $bookedRooms }}, {{ $totalRooms - $bookedRooms }}],
        backgroundColor: ['#007bff', '#6c757d']
      }]
    }
  });
</script>

<style>
  .gap-5 > * + * {
    margin-top: 2.5rem !important;
  }
</style>

