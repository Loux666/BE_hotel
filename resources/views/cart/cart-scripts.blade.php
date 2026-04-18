<script>console.log('CSRF Token:', document.querySelector('meta[name="csrf-token"]').content);</script>
<!-- Nếu dùng Bootstrap 5 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

<script> //auto focus
  window.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('cartErrorModal');
    if (modal) {
      modal.focus();
    }
  });
</script>

<script> // feedback
  document.addEventListener('DOMContentLoaded', () => {
      document.querySelectorAll('.feedback-form').forEach(form => {
          form.addEventListener('submit', async (e) => {
              e.preventDefault();

              const formData = new FormData(form);
              const url = form.getAttribute('action');
              const btn = form.querySelector('button[type="submit"]');
              btn.disabled = true;

              try {
                  const response = await fetch(url, {
                      method: 'POST',
                      headers: {
                          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                      },
                      body: formData,
                  });

                  const result = await response.json();

                  if (response.ok) {
                      // 1. Đóng modal
                      const modalEl = form.closest('.modal');
                      const bsModal = bootstrap.Modal.getInstance(modalEl);
                      bsModal.hide();

                      // 2. Sau khi modal đóng hoàn toàn → cập nhật DOM
                      modalEl.addEventListener('hidden.bs.modal', () => {
                          const detailId = form.querySelector('[name="booking_detail_id"]').value;
                          const wrapper = document.querySelector(`#feedback-wrapper-${detailId}`);

                          // 2.1. Cập nhật chỉ nút trong wrapper
                          wrapper.innerHTML = `
                              <button class="btn btn-outline-secondary btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#viewFeedbackModal${detailId}">
                                  Xem đánh giá của bạn
                              </button>
                          `;

                          // 2.2. Tạo modal mới và gắn vào body (không nằm trong vùng aria-hidden)
                          const modalHTML = `
                              <div class="modal fade" id="viewFeedbackModal${detailId}" tabindex="-1" aria-hidden="true">
                                  <div class="modal-dialog"><div class="modal-content">
                                      <div class="modal-header"><h5 class="modal-title">Đánh giá của bạn</h5>
                                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                                      </div>
                                      <div class="modal-body">
                                          <p><strong>Số sao:</strong> ${result.feedback.rating} / 5</p>
                                          <p><strong>Nội dung:</strong></p>
                                          <div class="border p-2 rounded">${result.feedback.content}</div>
                                          <p class="text-muted">Gửi lúc: ${result.feedback.created_at}</p>
                                      </div>
                                  </div></div>
                              </div>
                          `;

                          document.body.insertAdjacentHTML('beforeend', modalHTML);
                      }, { once: true }); // Đảm bảo chỉ chạy 1 lần

                      alert('Đánh giá đã được gửi!');
                  } else {
                      alert(result.error || 'Có lỗi xảy ra!');
                  }
              } catch (err) {
                  alert('Lỗi kết nối!');
              } finally {
                  btn.disabled = false;
              }
          });
      });
  });
</script>

<script> //auto tinh toán tổng tiền và cập nhật nút
    document.addEventListener('DOMContentLoaded', function () {
      // Tab switch
      const tabs = document.querySelectorAll('.cart-tab');
      const contents = document.querySelectorAll('.cart-tab-content');

      tabs.forEach(tab => {
        tab.addEventListener('click', () => {
          tabs.forEach(t => t.classList.remove('active'));
          contents.forEach(c => c.classList.remove('active'));
          tab.classList.add('active');
          const tabContent = document.getElementById('tab-' + tab.dataset.tab);
          if (tabContent) tabContent.classList.add('active');
        });
      });

      // Format tiền
      function formatPrice(num) {
        return num.toLocaleString('vi-VN') + ' ₫';
      }

      // Cập nhật tổng tiền
      function updateTotal() {
        const checkboxes = document.querySelectorAll('.cart-checkbox');
        const totalAmount = document.getElementById('totalAmount');
        const totalNote = document.getElementById('totalNote');
        let total = 0;
        let count = 0;

        checkboxes.forEach(cb => {
          if (cb.checked && !cb.disabled) {
            const price = parseInt(cb.dataset.price);
            if (!isNaN(price)) {
              total += price;
              count++;
            }
          }
        });

        if (totalAmount) totalAmount.textContent = formatPrice(total);
        if (totalNote) totalNote.textContent = `${count} món hàng, bao gồm thuế và phí`;
      }

      // Disable nút nếu không có checkbox hợp lệ
      function updateButtonState() {
        const button = document.querySelector('.cart-summary-btn');
        const validCheckboxes = Array.from(document.querySelectorAll('.cart-checkbox'))
          .filter(cb => !cb.disabled && cb.checked);

        if (validCheckboxes.length === 0) {
          button.disabled = true;
          button.classList.add('disabled');
        } else {
          button.disabled = false;
          button.classList.remove('disabled');
        }
      }

      document.addEventListener('change', function (e) {
        if (e.target.classList.contains('cart-checkbox')) {
          updateTotal();
          updateButtonState();
        }
      });

      updateTotal();
      updateButtonState();

      // Submit form
      const form = document.getElementById('verify-cart-form');
      form.addEventListener('submit', function (e) {
        const selected = Array.from(document.querySelectorAll('.cart-checkbox:checked'))
          .filter(cb => !cb.disabled)
          .map(cb => cb.value);

        if (selected.length === 0) {
          e.preventDefault();
          alert('Vui lòng chọn ít nhất 1 phòng để tiếp tục.');
          return;
        }

        document.getElementById('selected_ids').value = JSON.stringify(selected);
      });
    });
</script>

<script> //switch tab 
    document.addEventListener('DOMContentLoaded', function () {
        const urlParams = new URLSearchParams(window.location.search);
        const requestedTab = urlParams.get('tab');

        if (requestedTab === 'history') {
            // Tự động active tab
            const tab = document.querySelector(`.cart-tab[data-tab="history"]`);
            const tabContent = document.getElementById(`tab-history`);
            if (tab && tabContent) {
                // Gỡ active hiện tại
                document.querySelectorAll('.cart-tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.cart-tab-content').forEach(c => c.classList.remove('active'));

                // Kích hoạt tab "history"
                tab.classList.add('active');
                tabContent.classList.add('active');

                // Scroll đến tab nếu muốn
                tab.scrollIntoView({ behavior: 'smooth' });
            }
        }
    });
</script>