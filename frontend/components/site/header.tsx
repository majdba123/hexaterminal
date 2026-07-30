import { getTranslations } from "next-intl/server";
import { Search } from "lucide-react";
import { Link } from "@/i18n/navigation";
import { Logo } from "@/components/site/logo";
import { Button } from "@/components/ui/button";
import { ThemeToggle } from "@/components/site/theme-toggle";
import { LocaleSwitcher } from "@/components/site/locale-switcher";
import { MobileNav } from "@/components/site/mobile-nav";
import { HeaderShell } from "@/components/site/header-shell";
import { NavLink } from "@/components/site/nav-link";
import { primaryNavRoutes } from "@/lib/routes/registry";

// Primary navigation is derived from the single-source-of-truth route registry
// (lib/routes/registry.ts) so nav, footer, breadcrumbs, and the sitemap can
// never drift apart. `path: ""` (home) renders as href "/".
const navItems = primaryNavRoutes().map(
  (r) => [r.navKey as string, r.path || "/"] as const,
);

/**
 * Site header.
 *
 * Three groups on one row: brand, navigation, actions. Navigation is centred
 * rather than pushed up against the brand so the row stays balanced at wide
 * widths, and the utility controls (search / locale / theme) are fenced off
 * from the CTA by a hairline -- they previously sat in one undifferentiated
 * run of controls, which made the CTA compete with three icon buttons for the
 * same attention.
 *
 * Sticky/glass behaviour lives in HeaderShell and the active state in NavLink;
 * both need client state and are the only client components here. This stays a
 * server component so nav labels are translated on the server.
 */
export async function Header() {
  const t = await getTranslations("nav");

  return (
    <HeaderShell>
      <div className="relative mx-auto flex h-20 max-w-(--container-content) items-center gap-4 px-5 sm:px-8">
        {/* Brand. The negative margin keeps the enlarged focus/hit area from
            adding visual padding around the mark. */}
        <Link
          href="/"
          className="focus-ring -m-2 shrink-0 rounded-[var(--radius-md)] p-2"
          aria-label="Hexa Terminal"
        >
          {/* The mark is a wide lockup (~5.9:1), so height drives width fast:
              h-10 renders ~234px, which overflows a 375px header once the
              utility cluster and menu button are accounted for. Large on
              desktop where the brief wants presence, restrained on mobile. */}
          {/* Mark alone on the narrowest screens; the full lockup from sm up.
              Two elements rather than one, because a viewBox cannot be changed
              responsively from CSS. */}
          <Logo variant="mark" className="h-7 w-auto sm:hidden" />
          <Logo className="hidden h-9 w-auto sm:block xl:h-10" />
        </Link>

        {/*
          Desktop navigation appears at xl, not lg.

          At 1024 it cannot fit: the logo is ~260px, the utility cluster plus
          CTA ~285px, which leaves ~465px for seven items -- about 66px each,
          well below a readable label. The result was a real 201px horizontal
          scrollbar on EVERY page at 1024x1366, not just the hero. Below xl the
          existing drawer takes over, which is what the brief asks for ("do not
          squeeze desktop navigation into the viewport").
        */}
        {/* Centred BETWEEN the brand and the actions, not absolutely centred
            on the row. An earlier `absolute left-1/2 -translate-x-1/2` version
            looked identical but silently overlapped the action cluster at some
            widths and swallowed clicks on the search button -- an absolutely
            positioned element still receives pointer events over whatever it
            covers. As a flex child it can never overlap its siblings. */}
        <nav
          aria-label="Main"
          className="hidden xl:flex xl:flex-1 xl:items-center xl:justify-center xl:gap-0.5"
        >
          {navItems.map(([key, href]) => (
            <NavLink key={key} href={href} label={t(key)} />
          ))}
        </nav>

        <div className="ms-auto flex items-center gap-1 xl:ms-0">
          {/* Utility cluster -- deliberately low-contrast and grouped.
              Search drops out below sm: the logo lockup is wide (~5.9:1), and
              logo + three icon buttons + menu overflowed a 375px header. It
              moves into the mobile drawer rather than disappearing. */}
          <div className="flex items-center">
            <Button
              asChild
              variant="ghost"
              size="icon"
              aria-label={t("search")}
              className="hidden sm:inline-flex"
            >
              <Link href="/search">
                <Search className="size-5" />
              </Link>
            </Button>
            <LocaleSwitcher />
            <ThemeToggle />
          </div>

          {/* Separates "tools" from "the thing we want you to do". Hidden on
              mobile, where the CTA is not rendered anyway. */}
          <span className="mx-2 hidden h-6 w-px bg-border sm:block" aria-hidden="true" />

          <Button
            asChild
            size="sm"
            className="hidden shadow-sm shadow-primary/20 sm:inline-flex"
          >
            <Link href="/start-a-project">{t("startProject")}</Link>
          </Button>

          <MobileNav />
        </div>
      </div>
    </HeaderShell>
  );
}
