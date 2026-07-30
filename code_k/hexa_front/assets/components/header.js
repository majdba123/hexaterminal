// إدارة الوضع الفاتح والداكن
function initTheme() {

    const themeSwitch = document.getElementById("theme-checkbox");
    if (!themeSwitch) return;

    const currentTheme = localStorage.getItem("theme") || "dark-mode";

    // تطبيق الوضع المحفوظ
    document.body.className = currentTheme;
    themeSwitch.checked = currentTheme === "white-mode";

    // إضافة event listener للتبديل
    themeSwitch.addEventListener("change", function () {
        if (this.checked) {
            document.body.className = "white-mode";
            localStorage.setItem("theme", "white-mode");
        } else {
            document.body.className = "dark-mode";
            localStorage.setItem("theme", "dark-mode");
        }

        // تحديث الأيقونات بناءً على الوضع
        updateThemeIcons(this.checked);
    });

    // تهيئة الأيقونات
    updateThemeIcons(themeSwitch.checked);
}

// تحديث أيقونات الوضع
function updateThemeIcons(isWhiteMode) {
    const themeIcons = document.querySelectorAll(".theme-icon");
    themeIcons.forEach((icon) => {
        if (isWhiteMode) {
            icon.textContent = "☀️";
        } else {
            icon.textContent = "🌙";
        }
    });
}

// تهيئة الوضع عند تحميل الصفحة
document.addEventListener("DOMContentLoaded", function () {
    initTheme();
});

// أيضًا تهيئة عند تغيير اللغة إذا كان هناك تحديث للصفحة
document.addEventListener("languageChanged", function () {
    setTimeout(initTheme, 100);
});
