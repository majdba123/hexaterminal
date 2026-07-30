# HexaTerminal - IT Solutions Website

موقع ويب احترافي لشركة HexaTerminal لتقديم حلول تقنية وخدمات برمجية.

## المميزات

- ✅ تصميم متجاوب (Responsive Design)
- ✅ دعم متعدد اللغات (عربي/إنجليزي)
- ✅ تحميل ديناميكي للمكونات
- ✅ معالجة شاملة للأخطاء
- ✅ استخدام المكتبات المحلية (لا توجد استدعاءات خارجية)

## البنية

```
├── assets/
│   ├── components/      # مكونات HTML
│   ├── icons/          # الأيقونات
│   ├── images/         # الصور
│   ├── js/             # ملفات JavaScript
│   ├── libs/           # المكتبات الخارجية (محلية)
│   └── style/          # ملفات CSS
├── translations/       # ملفات الترجمة
├── index.html          # الصفحة الرئيسية
├── page2.html          # صفحة الخدمات
└── project.html        # صفحة المشاريع
```

## الإعداد

### الخطوط

الخطوط حالياً تستخدم Google Fonts. لاستخدام الملفات المحلية:

1. حمّل ملفات الخطوط من [Google Fonts](https://fonts.google.com/)
2. ضع الملفات في `assets/libs/fonts/`
3. افتح `assets/libs/css/cairo-font.css` و `assets/style/css2.css`
4. أزل التعليقات من قواعد `@font-face`
5. احذف سطور `@import` من Google Fonts

راجع `FONTS_SETUP.md` للتفاصيل الكاملة.

## الاستخدام

افتح `index.html` في المتصفح أو استخدم خادم محلي:

```bash
# باستخدام Python
python -m http.server 8000

# باستخدام Node.js
npx http-server
```

## المتطلبات

- متصفح حديث يدعم ES6+
- اتصال بالإنترنت (لتحميل Google Fonts مؤقتاً)

## الترخيص

جميع الحقوق محفوظة © HexaTerminal

