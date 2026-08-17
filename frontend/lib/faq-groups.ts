import type { Faq } from "./api/types";

export function categoryId(label: string, index: number) {
  const slug = label
    .toLowerCase()
    .trim()
    .replace(/[^\p{L}\p{N}]+/gu, "-")
    .replace(/^-+|-+$/g, "");

  return slug ? `faq-${slug}` : `faq-group-${index}`;
}

export function groupFaqs(faqs: Faq[], generalLabel: string) {
  const groups: { label: string; id: string; items: Faq[] }[] = [];
  const byLabel = new Map<string, { label: string; id: string; items: Faq[] }>();

  for (const faq of faqs) {
    const label = faq.category?.trim() || generalLabel;
    const existing = byLabel.get(label);

    if (existing) {
      existing.items.push(faq);
      continue;
    }

    const group = {
      label,
      id: categoryId(label, groups.length),
      items: [faq],
    };

    byLabel.set(label, group);
    groups.push(group);
  }

  return groups;
}
