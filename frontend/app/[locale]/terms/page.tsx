import type { Metadata } from "next";
import { getTranslations, setRequestLocale } from "next-intl/server";
import { pageMetadata } from "@/lib/seo/page-metadata";
import { resolveRobots } from "@/lib/seo/indexing";
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
    path: "/terms",
    title: t("termsTitle"),
    robots: resolveRobots(true),
  });
}

/**
 * Generic, structurally complete terms-of-service boilerplate. NOT a
 * substitute for legal review -- see docs/content/founder-content-guide.md
 * §Legal.
 */
export default async function TermsPage({
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
        <Breadcrumb items={[{ label: t("termsTitle") }]} />
        <h1 className="text-balance text-3xl font-extrabold tracking-tight text-foreground sm:text-4xl">
          {t("termsTitle")}
        </h1>
        <p className="mt-2 text-sm text-muted-foreground">{t("lastUpdated")}</p>

        {isAr ? (
          <LegalProse dir="rtl">
            <p>
              تحكم شروط الخدمة هذه استخدامك لموقع {company} الإلكتروني وتواصلك معنا عبر نماذج
              الموقع. باستخدامك للموقع، فإنك توافق على هذه الشروط.
            </p>
            <h2>استخدام الموقع</h2>
            <p>
              يجوز لك تصفح الموقع والاطلاع على محتواه للأغراض المشروعة فقط. يُحظر استخدام الموقع
              بأي طريقة قد تضر بالخدمة أو تعطل توفرها للآخرين.
            </p>
            <h2>الملكية الفكرية</h2>
            <p>
              جميع المحتويات المنشورة على هذا الموقع، بما في ذلك النصوص والشعارات والصور، مملوكة
              لـ {company} أو مرخصة لها، ولا يجوز إعادة استخدامها دون إذن كتابي مسبق.
            </p>
            <h2>طلبات المشاريع والاستفسارات</h2>
            <p>
              تقديم نموذج طلب مشروع أو استفسار عبر الموقع لا يشكل التزامًا تعاقديًا؛ أي اتفاقية
              عمل فعلية تُوثّق بشكل منفصل ورسمي بين الطرفين.
            </p>
            <h2>حدود المسؤولية</h2>
            <p>
              يُقدَّم محتوى الموقع &quot;كما هو&quot; دون أي ضمانات صريحة أو ضمنية. لا نتحمل مسؤولية أي أضرار
              ناتجة عن استخدام الموقع أو الاعتماد على محتواه.
            </p>
            <h2>التواصل</h2>
            <p>
              لأي استفسار بخصوص هذه الشروط، يرجى مراسلتنا على{" "}
              <a href={`mailto:${email}`}>{email}</a>.
            </p>
          </LegalProse>
        ) : (
          <LegalProse>
            <p>
              These Terms of Service govern your use of the {company} website and any
              communication you send us through our site forms. By using this site, you agree to
              these terms.
            </p>
            <h2>Use of the site</h2>
            <p>
              You may browse and read the site&apos;s content for lawful purposes only. You may
              not use the site in any way that could harm the service or disrupt its availability
              to others.
            </p>
            <h2>Intellectual property</h2>
            <p>
              All content published on this site, including text, logos, and images, is owned by
              or licensed to {company} and may not be reused without prior written permission.
            </p>
            <h2>Project inquiries and requests</h2>
            <p>
              Submitting a project inquiry or contact form through the site does not create a
              contractual obligation; any actual engagement is documented separately in a formal
              agreement between the parties.
            </p>
            <h2>Limitation of liability</h2>
            <p>
              Site content is provided &quot;as is&quot; without warranties of any kind, express or
              implied. We are not liable for any damages arising from use of, or reliance on, the
              site&apos;s content.
            </p>
            <h2>Contact</h2>
            <p>
              For any questions about these terms, please email us at{" "}
              <a href={`mailto:${email}`}>{email}</a>.
            </p>
          </LegalProse>
        )}
      </Container>
    </Section>
  );
}
