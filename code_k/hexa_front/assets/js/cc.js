CookieConsent.setOutOfRegion('EG', 1);
document.getElementById('joinTeamForm').addEventListener('submit', async function (e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    const messageDiv = document.getElementById('formMessage');

    // تعطيل الزر أثناء الإرسال
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="me-2">جاري الإرسال...</span><i class="fas fa-spinner fa-spin"></i>';

    try {
        const response = await fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json'
            }
        });

        if (response.ok) {
            messageDiv.style.display = 'block';
            messageDiv.innerHTML = `
          <div class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i>
            تم إرسال طلبك بنجاح! سنتواصل معك قريباً.
          </div>
        `;
            form.reset();
        } else {
            throw new Error('فشل في الإرسال');
        }
    } catch (error) {
        messageDiv.style.display = 'block';
        messageDiv.innerHTML = `
        <div class="alert alert-danger">
          <i class="fas fa-exclamation-circle me-2"></i>
          حدث خطأ أثناء إرسال الطلب. يرجى المحاولة مرة أخرى.
        </div>
      `;
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<span class="me-2">إرسال الطلب</span><i class="fas fa-arrow-right"></i>';
    }
});