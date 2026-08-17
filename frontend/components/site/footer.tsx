import { getTranslations } from "next-intl/server";
import { Link } from "@/i18n/navigation";
import { Logo } from "@/components/site/logo";
import { footerRoutes } from "@/lib/routes/registry";

const linkGroups = [
  {
    titleKey: "quickLinks",
    items: footerRoutes("quickLinks").map((r) => [r.breadcrumbKey as string, r.path] as const),
  },
  {
    titleKey: "company",
    items: footerRoutes("company").map((r) => [r.breadcrumbKey as string, r.path] as const),
  },
] as const;

const legalLinks = footerRoutes("legal").map((r) => [r.breadcrumbKey as string, r.path] as const);

export async function Footer() {
  const t = await getTranslations("footer");
  const tNav = await getTranslations("nav");
  const tLegal = await getTranslations("legal");
  const year = new Date().getFullYear();

  return (
    <footer className="border-t border-border bg-surface">
      <div className="mx-auto max-w-(--container-content) px-5 py-10 sm:px-8 sm:py-12">
        <div className="grid gap-8 md:grid-cols-2 lg:grid-cols-[minmax(0,1.1fr)_minmax(0,0.75fr)_minmax(0,0.75fr)] lg:gap-10">
          <div className="flex flex-col gap-3">
            <Logo className="h-7 w-auto" />
            <p className="ui-copy max-w-sm text-pretty text-sm leading-relaxed text-muted-foreground">{t("tagline")}</p>
          </div>

          {linkGroups.map((group) => (
            <div key={group.titleKey} className="flex flex-col gap-2.5">
              <h2 className="text-sm font-semibold text-foreground">{t(group.titleKey)}</h2>
              <ul className="flex flex-col gap-1.5">
                {group.items.map(([key, href]) => (
                  <li key={key}>
                    <Link
                      href={href}
                      className="focus-ring rounded py-0.5 text-sm text-muted-foreground hover:text-foreground"
                    >
                      {tNav(key)}
                    </Link>
                  </li>
                ))}
              </ul>
            </div>
          ))}
        </div>

        <div className="mt-8 flex flex-col gap-3 border-t border-border pt-5 text-xs text-muted-foreground sm:flex-row sm:items-center sm:justify-between">
          <p>{`\u00A9 ${year} Hexa Terminal. ${t("rights")}`}</p>
          <div className="flex flex-wrap items-center gap-4">
            {legalLinks.map(([key, href]) => (
              <Link key={key} href={href} className="focus-ring rounded py-0.5 hover:text-foreground">
                {tLegal(key)}
              </Link>
            ))}
          </div>
        </div>
      </div>
    </footer>
  );
}
