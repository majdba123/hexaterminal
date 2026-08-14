import { readFileSync } from "node:fs";
import { join } from "node:path";
import { expect, test } from "@playwright/test";
import en from "../messages/en.json";
import ar from "../messages/ar.json";

function source(path: string) {
  return readFileSync(join(process.cwd(), path), "utf8");
}

test("LeadForm fields expose associated, announced errors and submission state", () => {
  const field = source("components/ui/field.tsx");
  const form = source("components/site/lead-form.tsx");

  expect(field).toContain('role="alert"');
  expect(field).toContain('"aria-invalid"');
  expect(field).toContain('"aria-describedby"');
  expect(form).toContain('aria-busy={status === "submitting"}');
  expect(form).toContain('role="status"');
  expect(form).toContain('role="alert"');
});

test("pagination has localized names and does not leave disabled links keyboard-activatable", () => {
  const pagination = source("components/site/pagination.tsx");

  expect(pagination).toContain('aria-label={previousLabel}');
  expect(pagination).toContain('aria-label={nextLabel}');
  expect(pagination).toContain('aria-current="page"');
  expect(pagination).not.toContain("pointer-events-none");
  expect(en.common.pagination).toBeTruthy();
  expect(ar.common.pagination).toBeTruthy();
  expect(en.common.previousPage).toBeTruthy();
  expect(ar.common.nextPage).toBeTruthy();
});

test("public card fallbacks are decorative and CMS images have record-name fallbacks", () => {
  const systemCard = source("components/site/system-card.tsx");
  const caseStudyCard = source("components/site/case-study-card.tsx");
  const articleCard = source("components/site/article-card.tsx");

  expect(systemCard).toContain('alt={system.cover_image_alt ?? system.name}');
  expect(caseStudyCard).toContain('alt={caseStudy.cover_image_alt ?? caseStudy.title}');
  expect(articleCard).toContain('alt={article.cover_image_alt ?? article.title}');
  expect(systemCard).toContain('aria-hidden="true"');
  expect(caseStudyCard).toContain('aria-hidden="true"');
  expect(articleCard).toContain('aria-hidden="true"');
});

test("mobile navigation exposes its current destination", () => {
  const mobileNav = source("components/site/mobile-nav.tsx");

  expect(mobileNav).toContain('aria-current={isActive(href) ? "page" : undefined}');
});
