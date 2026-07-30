# دليل إعداد الخطوط (Fonts Setup Guide)

## نظرة عامة
تم تحديث المشروع لاستخدام الخطوط محلياً بدلاً من الاستدعاءات الخارجية. يجب تحميل ملفات الخطوط وتنسيقها بشكل صحيح.

## الخطوط المطلوبة

### 1. Cairo Font (للغة العربية)
- **المسار**: `assets/libs/fonts/`
- **الملفات المطلوبة**:
  - `cairo-regular.woff2` أو `cairo-regular.woff` أو `cairo-regular.ttf`
  - `cairo-medium.woff2` أو `cairo-medium.woff` أو `cairo-medium.ttf`
  - `cairo-semibold.woff2` أو `cairo-semibold.woff` أو `cairo-semibold.ttf`
  - `cairo-bold.woff2` أو `cairo-bold.woff` أو `cairo-bold.ttf`
  - `cairo-variable.woff2` (للوزن المتغير)

### 2. Jost Font (للغة الإنجليزية)
- **المسار**: `assets/libs/fonts/`
- **الملفات المطلوبة**:
  - `jost-italic-cyrillic.woff2`
  - `jost-italic-latin-ext.woff2`
  - `jost-italic-latin.woff2`
  - `jost-normal-cyrillic.woff2`
  - `jost-normal-latin-ext.woff2`
  - `jost-normal-latin.woff2`

### 3. Russo One Font (للعناوين)
- **المسار**: `assets/libs/fonts/`
- **الملفات المطلوبة**:
  - `russo-one-cyrillic.woff2`
  - `russo-one-latin-ext.woff2`
  - `russo-one-latin.woff2`

## كيفية تحميل الخطوط

### الطريقة 1: استخدام Google Fonts Helper
1. افتح [Google Fonts Helper](https://google-webfonts-helper.herokuapp.com/)
2. ابحث عن الخط المطلوب (Cairo, Jost, Russo One)
3. اختر الأوزان المطلوبة
4. اختر "woff2" كصيغة
5. حمّل الملفات وضعها في `assets/libs/fonts/`

### الطريقة 2: استخدام Google Fonts API
1. افتح [Google Fonts](https://fonts.google.com/)
2. ابحث عن الخط المطلوب
3. اختر "Download family"
4. استخرج الملفات وضعها في `assets/libs/fonts/`
5. أعد تسمية الملفات حسب الأسماء المطلوبة أعلاه

### الطريقة 3: استخدام npm/yarn
```bash
npm install @fontsource/cairo @fontsource/jost @fontsource/russo-one
# ثم انسخ الملفات من node_modules إلى assets/libs/fonts/
```

## ملاحظات مهمة

1. **الخطوط الاحتياطية**: تم إضافة خطوط احتياطية في CSS لضمان عرض النصوص حتى لو فشل تحميل الخطوط المخصصة.

2. **font-display: swap**: تم استخدام `font-display: swap` لضمان عرض النصوص فوراً حتى لو كان الخط لا يزال قيد التحميل.

3. **Font Loader**: تم إضافة `font-loader.js` لضمان تحميل الخطوط قبل عرض المحتوى، مما يحل مشكلة "الصور بدون نصوص".

4. **التحقق من الملفات**: تأكد من أن جميع ملفات الخطوط موجودة في المسار الصحيح قبل نشر الموقع.

## هيكل المجلدات النهائي

```
assets/
├── libs/
│   ├── css/
│   │   ├── cairo-font.css
│   │   ├── font-awesome.min.css
│   │   └── sweetalert2.min.css
│   ├── fonts/
│   │   ├── cairo-*.woff2
│   │   ├── jost-*.woff2
│   │   └── russo-one-*.woff2
│   └── js/
│       ├── alpinejs.min.js
│       ├── i18next.min.js
│       └── ...
└── style/
    └── css2.css
```

## اختبار الخطوط

بعد تحميل الخطوط، افتح الموقع في المتصفح وتحقق من:
1. عرض النصوص بشكل صحيح
2. عدم وجود تأخير في عرض النصوص
3. عمل الخطوط العربية والإنجليزية بشكل صحيح

## الدعم

إذا واجهت مشاكل في تحميل الخطوط:
1. تحقق من مسارات الملفات في CSS
2. تأكد من أن الملفات موجودة في المسار الصحيح
3. تحقق من console المتصفح للأخطاء
4. تأكد من أن الخادم يخدم ملفات `.woff2` بشكل صحيح

