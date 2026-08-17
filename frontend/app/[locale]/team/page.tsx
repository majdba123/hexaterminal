import type { Metadata } from "next";
import { getTranslations, setRequestLocale } from "next-intl/server";
import { getTeam } from "@/lib/api/client";
import { Container } from "@/components/site/container";
import { Section } from "@/components/site/section";
import { Breadcrumb } from "@/components/site/breadcrumb";
import { EmptyState } from "@/components/site/empty-state";
import { JsonLd } from "@/components/site/json-ld";
import { TeamMemberCard } from "@/components/site/team-member-card";
import { breadcrumbJsonLd, personJsonLd } from "@/lib/seo/jsonld";
import { localeAlternates, absoluteUrl } from "@/lib/seo/alternates";
import { resolveRobots } from "@/lib/seo/indexing";

export async function generateMetadata({
  params,
}: {
  params: Promise<{ locale: string }>;
}): Promise<Metadata> {
  const { locale } = await params;
  const t = await getTranslations({ locale, namespace: "team" });
  return {
    title: t("title"),
    description: t("subtitle"),
    alternates: {
      canonical: absoluteUrl(locale, "/team"),
      ...localeAlternates("/team"),
    },
    robots: resolveRobots(true),
  };
}

function teamGridClass(count: number) {
  if (count <= 1) return "max-w-xl";
  if (count === 2) return "md:grid-cols-2";
  if (count === 3) return "md:grid-cols-2 xl:grid-cols-3";
  return "md:grid-cols-2 xl:grid-cols-4";
}

export default async function TeamPage({
  params,
}: {
  params: Promise<{ locale: string }>;
}) {
  const { locale } = await params;
  setRequestLocale(locale);

  const [t, tc, team] = await Promise.all([
    getTranslations("team"),
    getTranslations("common"),
    getTeam(locale),
  ]);

  const eligibleForPersonJsonLd = team.filter((member) => member.person_jsonld_eligible);

  return (
    <Section as="div" className="pb-12 pt-10 sm:pb-16 sm:pt-14">
      <JsonLd data={breadcrumbJsonLd([{ name: t("title"), path: "/team" }], locale)} />
      {eligibleForPersonJsonLd.map((member) => (
        <JsonLd
          key={member.slug}
          data={personJsonLd({
            name: member.full_name,
            jobTitle: member.position,
            image: member.photo,
            sameAs: [member.linkedin_url, member.github_url].filter(
              (url): url is string => Boolean(url),
            ),
          })}
        />
      ))}

      <Container>
        <Breadcrumb items={[{ label: t("title") }]} />
        <h1 className="max-w-2xl text-balance text-3xl font-extrabold tracking-tight text-foreground sm:text-4xl">
          {t("title")}
        </h1>
        <p className="mt-3 max-w-xl text-pretty text-base leading-relaxed text-muted-foreground">
          {t("subtitle")}
        </p>

        {team.length > 0 ? (
          <div
            className={
              team.length === 1
                ? "mt-10 max-w-xl"
                : `mt-10 grid gap-6 ${teamGridClass(team.length)}`
            }
          >
            {team.map((member) => (
              <TeamMemberCard
                key={member.slug}
                member={member}
                href={`/about/team/${member.slug}`}
                ctaLabel={t("viewProfile")}
                founderLabel={t("founderBadge")}
              />
            ))}
          </div>
        ) : (
          <div className="mt-12">
            <EmptyState
              title={tc("noResults")}
              description={tc("noResultsDesc")}
              action={{ href: "/start-a-project", label: tc("emptyCta") }}
            />
          </div>
        )}
      </Container>
    </Section>
  );
}
