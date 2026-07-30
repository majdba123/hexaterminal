import { getTranslations } from "next-intl/server";
import { Link } from "@/i18n/navigation";
import { Logo } from "@/components/site/logo";
import { footerRoutes } from "@/lib/routes/registry";

// Footer columns are derived from the single-source-of-truth route registry
// (lib/routes/registry.ts). The nav-labelled columns use each route's
// breadcrumb key against the `nav` namespace; the legal column uses the
// `legal` namespace. Rendered as `[navKey, href]` tuples to keep the existing
// markup unchanged.
const linkGroups = [
  {
    titleKey: "quickLinks",
    items: footerRoutes("quickLinks").map(
      (r) => [r.breadcrumbKey as string, r.path] as const,
    ),
  },
  {
    titleKey: "company",
    items: footerRoutes("company").map(
      (r) => [r.breadcrumbKey as string, r.path] as const,
    ),
  },
] as const;

const legalLinks = footerRoutes("legal").map(
  (r) => [r.breadcrumbKey as string, r.path] as const,
);

export async function Footer() {
  const t = await getTranslations("footer");
  const tNav = await getTranslations("nav");
  const tLegal = await getTranslations("legal");
  const year = new Date().getFullYear();

  return (
    <footer className="border-t border-border bg-surface">
      <div className="mx-auto max-w-(--container-content) px-5 py-16 sm:px-8">
        <div className="grid gap-12 sm:grid-cols-2 lg:grid-cols-4">
          <div className="flex flex-col gap-4 lg:col-span-2">
            <Logo className="h-7 w-auto" />
            <p className="max-w-xs text-pretty text-sm leading-relaxed text-muted-foreground">
              {t("tagline")}
            </p>
          </div>
          {linkGroups.map((group) => (
            <div key={group.titleKey} className="flex flex-col gap-3">
              {/* h2, not h3: footer column headings are top-level groups within
                  the footer landmark, not subsections of the page content. As h3
                  they skipped a level on any page whose main content has no h2
                  (e.g. an empty listing page), which axe flags as
                  `heading-order`. */}
              <h2 className="text-sm font-semibold text-foreground">{t(group.titleKey)}</h2>
              <ul className="flex flex-col gap-2">
                {group.items.map(([key, href]) => (
                  <li key={key}>
                    <Link
                      href={href}
                      className="focus-ring rounded text-sm text-muted-foreground hover:text-foreground"
                    >
                      {tNav(key)}
                    </Link>
                  </li>
                ))}
              </ul>
            </div>
          ))}
        </div>
        <div className="mt-12 flex flex-col items-center justify-between gap-4 border-t border-border pt-8 text-xs text-muted-foreground sm:flex-row">
          <p>© {year} Hexa Terminal. {t("rights")}</p>
          <div className="flex items-center gap-4">
            {legalLinks.map(([key, href]) => (
              <Link
                key={key}
                href={href}
                className="focus-ring rounded hover:text-foreground"
              >
                {tLegal(key)}
              </Link>
            ))}
          </div>
        </div>
      </div>
    </footer>
  );
}
