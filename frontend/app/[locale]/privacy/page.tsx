import type { Metadata } from "next";
import { getTranslations, setRequestLocale } from "next-intl/server";
import { pageMetadata } from "@/lib/seo/page-metadata";
import { getCompanySettings } from "@/lib/api/client";
import { Container } from "@/components/site/container";
import { Section } from "@/components/site/section";
import { Breadcrumb } from "@/components/site/breadcrumb";
import { LegalProse } from "@/components/site/legal-prose";

export async function generateMetadata({
  params,
}: {
  params: Promise<{ locale: string }>;
}): Promise<Metadata> {
  const { locale } = await params;
  const t = await getTranslations({ locale, namespace: "legal" });
  return pageMetadata({
    locale,
    path: "/privacy",
    title: t("privacyTitle"),
  });
}

/**
 * Generic, structurally complete privacy policy boilerplate. NOT a
 * substitute for legal review -- see docs/content/founder-content-guide.md
 * §Legal. Contact details pull from Company Settings; everything else is
 * standard-form language, not a claim about a specific data practice we
 * haven't implemented.
 */
export default async function PrivacyPage({
  params,
}: {
  params: Promise<{ locale: string }>;
}) {
  const { locale } = await params;
  setRequestLocale(locale);
  const [t, settings] = await Promise.all([
    getTranslations("legal"),
    getCompanySettings(locale),
  ]);
  const email = settings?.email ?? "hello@hexaterminal.com";
  const company = settings?.company_name ?? "Hexa Terminal";
  const isAr = locale === "ar";

  return (
    <Section as="div">
      <Container narrow>
        <Breadcrumb items={[{ label: t("privacyTitle") }]} />
        <h1 className="text-balance text-3xl font-extrabold tracking-tight text-foreground sm:text-4xl">
          {t("privacyTitle")}
        </h1>
        <p className="mt-2 text-sm text-muted-foreground">{t("lastUpdated")}</p>

        {isAr ? (
          <LegalProse dir="rtl">
            <p>
              تصف سياسة الخصوصية هذه كيفية جمع {company} للبيانات واستخدامها وحمايتها عند زيارتك
              لموقعنا الإلكتروني أو تواصلك معنا عبر نماذج الموقع.
            </p>
            <h2>المعلومات التي نجمعها</h2>
            <p>
              نجمع المعلومات التي تقدمها طوعًا عبر نماذج التواصل (مثل الاسم والبريد الإلكتروني
              ورقم الهاتف واسم الشركة وتفاصيل المشروع)، بالإضافة إلى بيانات تقنية محدودة (مثل
              الصفحة المصدر ومعلمات الحملة التسويقية) لفهم كيفية وصول الزوار إلى الموقع.
            </p>
            <h2>كيفية استخدام المعلومات</h2>
            <p>
              نستخدم المعلومات المقدمة للرد على استفساراتك، وتقييم طلبات المشاريع، وتحسين محتوى
              الموقع. لا نبيع بياناتك الشخصية لأطراف ثالثة.
            </p>
            <h2>ملفات تعريف الارتباط والتحليلات</h2>
            <p>
              قد يستخدم الموقع أدوات تحليلات تراعي الخصوصية لفهم استخدام الموقع بشكل عام، دون جمع
              بيانات تعريف شخصية أو تتبع عبر مواقع أخرى.
            </p>
            <h2>حقوقك</h2>
            <p>
              يمكنك طلب الوصول إلى بياناتك الشخصية أو تصحيحها أو حذفها في أي وقت عبر التواصل معنا
              على البريد الإلكتروني أدناه.
            </p>
            <h2>التواصل</h2>
            <p>
              لأي استفسار بخصوص هذه السياسة، يرجى مراسلتنا على{" "}
              <a href={`mailto:${email}`}>{email}</a>.
            </p>
          </LegalProse>
        ) : (
          <LegalProse>
            <p>
              This Privacy Policy describes how {company} collects, uses, and protects information
              when you visit our website or contact us through our forms.
            </p>
            <h2>Information we collect</h2>
            <p>
              We collect information you voluntarily provide through contact and project-inquiry
              forms (such as name, email, phone, company, and project details), along with limited
              technical data (such as the source page and marketing campaign parameters) to
              understand how visitors reach the site.
            </p>
            <h2>How we use information</h2>
            <p>
              We use submitted information to respond to your inquiry, evaluate project requests,
              and improve our site content. We do not sell your personal data to third parties.
            </p>
            <h2>Cookies and analytics</h2>
            <p>
              The site may use privacy-conscious analytics tooling to understand aggregate site
              usage, without collecting personally identifying data or tracking you across other
              sites.
            </p>
            <h2>Your rights</h2>
            <p>
              You may request access to, correction of, or deletion of your personal data at any
              time by contacting us at the email below.
            </p>
            <h2>Contact</h2>
            <p>
              For any questions about this policy, please email us at{" "}
              <a href={`mailto:${email}`}>{email}</a>.
            </p>
          </LegalProse>
        )}
      </Container>
    </Section>
  );
}
