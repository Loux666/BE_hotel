
<!DOCTYPE html>
<html>
<head>
    <base href="/public">
  @include('admin.css')
  <style>
        .rf-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: transparent 100%;
            border-radius: 10px;
            
        }

        .rf-title {
            color: #fdfbfb;
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 20px;
            text-align: center;
        }

        .rf-table {
            width: 100%;
            border-collapse: collapse;
            background: transparent 100%;
            border-radius: 8px;
            overflow: hidden;
            
        }

        .rf-table th {
            background: transparent 100%;
            color: rgb(254, 247, 247);
            padding: 12px 10px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
        }

        .rf-table td {
            padding: 12px 10px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
            background: white
        }

        

        .rf-table tr:last-child td {
            border-bottom: none;
        }

        .rf-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .rf-btn-success {
            background: #28a745;
            color: white;
        }

        .rf-btn-success:hover {
            background: #218838;
            transform: translateY(-1px);
        }

        .rf-btn-secondary {
            background: #6c757d;
            color: white;
        }

        .rf-btn-secondary:hover {
            background: #5a6268;
        }

        .rf-empty-row {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 30px;
        }

        /* Modal styles */
        .rf-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            animation: rf-fadeIn 0.3s ease;
        }

        .rf-modal.show {
            display: block;
        }

        @keyframes rf-fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .rf-modal-content {
            background-color: white;
            margin: 15% auto;
            padding: 0;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            animation: rf-slideIn 0.3s ease;
        }

        @keyframes rf-slideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .rf-modal-header {
            padding: 20px;
            background: #007bff;
            color: white;
            border-radius: 8px 8px 0 0;
            position: relative;
        }

        .rf-modal-title {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
        }

        .rf-modal-close {
            position: absolute;
            right: 15px;
            top: 15px;
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            padding: 0;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.3s ease;
        }

        .rf-modal-close:hover {
            background: rgba(255,255,255,0.2);
        }

        .rf-modal-body {
            padding: 20px;
            color: #333;
            line-height: 1.5;
        }

        .rf-modal-footer {
            padding: 20px;
            text-align: right;
            border-top: 1px solid #eee;
        }

        .rf-modal-footer .rf-btn {
            margin-left: 10px;
            padding: 8px 16px;
            font-size: 14px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .rf-container {
                margin: 10px;
                padding: 15px;
            }

            .rf-table {
                font-size: 12px;
            }

            .rf-table th,
            .rf-table td {
                padding: 8px 5px;
            }

            .rf-title {
                font-size: 20px;
            }

            .rf-modal-content {
                width: 95%;
                margin: 10% auto;
            }
        }

        /* Status badges */
        .rf-status {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
        }

        .rf-status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .rf-amount {
            font-weight: 600;
            color: #007bff;
        }

        .rf-user-info {
            font-weight: 500;
        }

        .rf-booking-id {
            font-weight: 600;
            color: #28a745;
        }
    </style>
 
</head>
<body>
  @include('admin.header')
  @include('admin.sidebar')

  <div class="page-content">
    <div class="page-header">
      <div class="container-fluid">
        <div class="container mt-4">
            <div class="rf-container">
        <h3 class="rf-title">Danh sách yêu cầu hoàn tiền đang chờ xử lý</h3>
        <form action="{{ route('admin.refund') }}" method="GET" class="mb-3">
            <div class="input-group" style="max-width: 300px;">
                <input type="text" name="phone" class="form-control" placeholder="Tìm theo số điện thoại"
                    value="{{ request('phone') }}">
                <button class="btn btn-primary" type="submit">Tìm</button>
            </div>
        </form>
        
        <table class="rf-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Người dùng</th>
                    <th>SĐT</th>
                    <th>Mã đặt phòng</th>
                    <th>Số tiền</th>
                    <th>Phương thức</th>
                    <th>Lý do</th>
                    <th>Ngày tạo</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody id="rfTableBody">
                @forelse ($refunds as $refund)
                    @if ($refund->status === 'pending')
                    <tr>
                        <td>#{{ $refund->id }}</td>
                        <td class="rf-user-info">{{ $refund->booking->guest_name ?? 'N/A' }} (ID: {{ $refund->booking->user_id }})</td>
                        <td>{{ $refund->booking->guest_phone ?? 'N/A' }}</td>
                        <td class="rf-booking-id">#{{ $refund->booking_id }}</td>
                        <td class="rf-amount">{{ number_format($refund->amount) }}₫</td>
                        <td><span class="rf-status rf-status-pending">{{ strtoupper($refund->type) }}</span></td>
                        <td>{{ $refund->reason ?? '-' }}</td>
                        <td>{{ $refund->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <button type="button" class="rf-btn rf-btn-success" onclick="openModal('{{ $refund->id }}', '{{ addslashes($refund->booking->guest_name ?? 'N/A') }}', '{{ $refund->booking_id }}')">
                                Duyệt
                            </button>
                        </td>
                    </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="9" class="rf-empty-row">Không có yêu cầu hoàn tiền nào đang chờ xử lý.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Pagination -->
        <div style="text-align: center; margin-top: 20px; color: #666;">
            {{ $refunds->links() }}
        </div>
    </div>

    <!-- Modal -->
    <div class="rf-modal" id="rfModal">
        <div class="rf-modal-content">
            <div class="rf-modal-header">
                <h5 class="rf-modal-title" id="rfModalTitle">Xác nhận duyệt hoàn tiền</h5>
                <button type="button" class="rf-modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div class="rf-modal-body">
                <p id="rfModalBody">Bạn có chắc chắn muốn duyệt hoàn tiền không?</p>
            </div>
            <div class="rf-modal-footer">
                <button type="button" class="rf-btn rf-btn-secondary" onclick="closeModal()">Huỷ</button>
                <button type="button" class="rf-btn rf-btn-success" onclick="confirmRefund()">Xác nhận</button>
            </div>
        </div>
    </div>

    <script>
        let currentRefundId = null;
        let currentBookingId = null;

        function openModal(refundId, userName, bookingId) {
            currentRefundId = refundId;
            currentBookingId = bookingId;
            
            // Update modal content
            document.getElementById('rfModalTitle').textContent = `Xác nhận duyệt hoàn tiền đơn #${bookingId}`;
            document.getElementById('rfModalBody').innerHTML = `Bạn có chắc chắn muốn duyệt hoàn tiền cho người dùng <strong>${userName}</strong> không?`;
            
            // Show modal
            const modal = document.getElementById('rfModal');
            modal.classList.add('show');
            
            // Prevent body scroll
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            const modal = document.getElementById('rfModal');
            modal.classList.remove('show');
            document.body.style.overflow = 'auto';
            currentRefundId = null;
            currentBookingId = null;
        }

        function confirmRefund() {
            if (!currentRefundId) {
                alert('Có lỗi xảy ra. Vui lòng thử lại.');
                return;
            }

            // Validation
            if (!confirm('Bạn có chắc chắn muốn duyệt yêu cầu hoàn tiền này không?')) {
                return;
            }

            // Create form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/refunds/manual/${currentRefundId}`;
            
            // Add CSRF token
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';
            form.appendChild(csrfInput);

            document.body.appendChild(form);
            form.submit();
        }

        // Close modal when clicking outside
        document.getElementById('rfModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && document.getElementById('rfModal').classList.contains('show')) {
                closeModal();
            }
        });

        // Form validation and table interactions
        document.addEventListener('DOMContentLoaded', function() {
            // Add loading state to buttons
            const buttons = document.querySelectorAll('.rf-btn-success');
            buttons.forEach(button => {
                button.addEventListener('click', function() {
                    if (this.textContent === 'Duyệt') {
                        this.style.pointerEvents = 'none';
                        this.style.opacity = '0.6';
                        setTimeout(() => {
                            this.style.pointerEvents = 'auto';
                            this.style.opacity = '1';
                        }, 2000);
                    }
                });
            });

            // Table row highlight on hover
            const rows = document.querySelectorAll('.rf-table tbody tr');
            rows.forEach(row => {
                row.addEventListener('mouseenter', function() {
                    this.style.backgroundColor = '#f8f9fa';
                });
                row.addEventListener('mouseleave', function() {
                    this.style.backgroundColor = '';
                });
            });
        });
    </script>
                
      </div>
    </div>
  </div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

<!-- Toastr JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
        
        
    @if (Session::has('success'))
    <script>
        const navType = performance.getEntriesByType("navigation")[0]?.type;
        if (navType !== "back_forward") {
            toastr.options = {
                progressBar: true,
                closeButton: true
            };
            toastr.success("{{ Session::get('success') }}", 'Success', {
                timeOut: 12000,
                positionClass: 'toast-top-right'
            });
        }
    </script>
    @php Session::forget('success'); @endphp
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

