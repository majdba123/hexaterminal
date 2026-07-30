import type { Metadata } from "next";
import { notFound } from "next/navigation";
import { setRequestLocale } from "next-intl/server";
import { getPreview } from "@/lib/api/client";
import type { TrustPage } from "@/lib/api/types";
import { Container } from "@/components/site/container";
import { Section } from "@/components/site/section";
import { TrustPageBody } from "@/components/site/trust-page-view";

export const dynamic = "force-dynamic";

/**
 * Secure CMS preview: renders whatever a governed record (published or
 * not, approved or not) will look like from a one-time signed token minted
 * in Filament. Always noindex/no-store -- see
 * App\Http\Controllers\Api\V1\Public\PreviewController for the backend
 * contract. An invalid/expired/revoked token 404s exactly like this route
 * would for a nonexistent one.
 */
export async function generateMetadata(): Promise<Metadata> {
  return { robots: { index: false, follow: false }, title: "Preview" };
}

export default async function PreviewPage({
  params,
}: {
  params: Promise<{ locale: string; token: string }>;
}) {
  const { locale, token } = await params;
  setRequestLocale(locale);

  const preview = await getPreview(token);
  if (!preview) notFound();

  const banner = (
    <Container narrow className="pt-8">
      <div className="mb-8 rounded-[var(--radius-lg)] border border-amber-500/40 bg-amber-500/10 px-4 py-3 text-sm text-amber-700 dark:text-amber-300">
        Preview only ({preview.type}) -- expires{" "}
        {new Date(preview.preview.expires_at).toLocaleString(locale)}. Not indexed, not cached,
        not publicly linked.
      </div>
    </Container>
  );

  if (preview.type === "trust_page") {
    return (
      <>
        {banner}
        <TrustPageBody locale={preview.preview.locale} page={preview.record as TrustPage} />
      </>
    );
  }

  return (
    <Section as="div">
      {banner}
      <Container narrow>
        <pre className="overflow-x-auto rounded-[var(--radius-lg)] border border-border bg-muted/40 p-4 text-xs">
          {JSON.stringify(preview.record, null, 2)}
        </pre>
      </Container>
    </Section>
  );
}
