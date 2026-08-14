import { readFileSync } from "node:fs";
import { join } from "node:path";
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

test("Homepage translations contain no unsupported proof metric claims", () => {
  for (const messages of [en.home, ar.home]) {
    expect(messages).not.toHaveProperty("proofSystemsValue");
    expect(messages).not.toHaveProperty("proofApisValue");
    expect(messages).not.toHaveProperty("proofSecurityValue");
  }

  const terminalSequence = readFileSync(
    join(process.cwd(), "components/site/terminal-sequence.tsx"),
    "utf8",
  );
  expect(terminalSequence).not.toContain("150+ endpoints");
  expect(terminalSequence).not.toContain("Zero incidents");
});
