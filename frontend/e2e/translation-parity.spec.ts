import { expect, test } from "@playwright/test";
import en from "../messages/en.json";
import ar from "../messages/ar.json";

function leafKeys(value: unknown, prefix = ""): string[] {
  if (!value || typeof value !== "object" || Array.isArray(value)) return [prefix];

  return Object.entries(value).flatMap(([key, child]) =>
    leafKeys(child, prefix ? `${prefix}.${key}` : key),
  );
}

test("Arabic translations mirror the English message contract and use consistent conversion language", () => {
  expect(leafKeys(ar).sort()).toEqual(leafKeys(en).sort());

  expect(ar.nav.caseStudies).toBe("دراسات الحالة");
  expect(ar.nav.startProject).toBe("ابدأ مشروعك");
  expect(ar.home.heroCtaPrimary).toBe(ar.nav.startProject);
  expect(ar.services.heroCta).toBe(ar.nav.startProject);
  expect(ar.systems.heroCta).toBe(ar.nav.startProject);
  expect(ar.startProject.title).toBe(ar.nav.startProject);
});

test("Arabic Case Study classifications use localized labels instead of enum values", () => {
  for (const [classification, label] of Object.entries(ar.caseStudies.classification)) {
    expect(label).not.toBe(classification);
    expect(label).not.toContain("_");
  }
});
