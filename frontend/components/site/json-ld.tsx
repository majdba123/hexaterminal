import { serializeJsonLd } from "@/lib/seo/jsonld";

/** Renders a schema.org JSON-LD payload built by lib/seo/jsonld.ts. */
export function JsonLd({ data }: { data: object | object[] }) {
  return (
    <script
      type="application/ld+json"
      // MUST go through serializeJsonLd, which escapes `<`. A bare
      // JSON.stringify here is stored XSS -- see that function for why.
      dangerouslySetInnerHTML={{ __html: serializeJsonLd(data) }}
    />
  );
}
