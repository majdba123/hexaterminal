import type { Metadata } from "next";
import Image from "next/image";
import { MapPin } from "lucide-react";
import { notFound } from "next/navigation";
import { getTranslations, setRequestLocale } from "next-intl/server";
import { getTeamMember } from "@/lib/api/client";
import { Container } from "@/components/site/container";
import { Section } from "@/components/site/section";
import { Breadcrumb } from "@/components/site/breadcrumb";
import { JsonLd } from "@/components/site/json-ld";
import { Badge } from "@/components/ui/badge";
import { breadcrumbJsonLd, personJsonLd } from "@/lib/seo/jsonld";
import { pageMetadata } from "@/lib/seo/page-metadata";
import { resolveRobots } from "@/lib/seo/indexing";
import type { PublicClaim } from "@/lib/api/types";

function safeSummary(text: string | null) {
  if (!text) return undefined;

  const normalized = text.replace(/\s+/g, " ").trim();
  if (!normalized) return undefined;

  return normalized.length > 170 ? `${normalized.slice(0, 167).trimEnd()}...` : normalized;
}

function categoryLabel(category: string) {
  return category
    .split(/[_-]+/)
    .filter(Boolean)
    .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
    .join(" ");
}

function groupClaims(claims: PublicClaim[]) {
  const grouped = new Map<string, string[]>();

  for (const claim of claims) {
    const list = grouped.get(claim.category) ?? [];
    list.push(claim.claim_text);
    grouped.set(claim.category, list);
  }

  return Array.from(grouped.entries()).map(([category, items]) => ({
    category,
    label: categoryLabel(category),
    items,
  }));
}

export async function generateMetadata({
  params,
}: {
  params: Promise<{ locale: string; slug: string }>;
}): Promise<Metadata> {
  const { locale, slug } = await params;
  const member = await getTeamMember(locale, slug);

  if (!member) return {};

  const title = member.position
    ? `${member.full_name} - ${member.position}`
    : member.full_name;

  return pageMetadata({
    locale,
    path: `/about/team/${slug}`,
    title,
    description: safeSummary(member.bio) ?? member.specialization ?? undefined,
    image: member.photo,
    robots: resolveRobots(false),
  });
}

export default async function TeamMemberDetailPage({
  params,
}: {
  params: Promise<{ locale: string; slug: string }>;
}) {
  const { locale, slug } = await params;
  setRequestLocale(locale);

  const [member, tTeam, tNav] = await Promise.all([
    getTeamMember(locale, slug),
    getTranslations("team"),
    getTranslations("nav"),
  ]);

  if (!member) notFound();

  const claims = groupClaims(member.claims);
  const sameAs = [member.github_url, member.linkedin_url].filter(
    (value): value is string => Boolean(value),
  );

  return (
    <>
      <JsonLd
        data={[
          breadcrumbJsonLd(
            [
              { name: tNav("about"), path: "/about" },
              { name: tTeam("title"), path: "/about" },
              { name: member.full_name, path: `/about/team/${slug}` },
            ],
            locale,
          ),
          ...(member.person_jsonld_eligible
            ? [
                personJsonLd({
                  name: member.full_name,
                  jobTitle: member.position,
                  image: member.photo,
                  sameAs,
                }),
              ]
            : []),
        ]}
      />

      <Section as="div" className="bg-surface pb-10 pt-10 sm:pb-12 sm:pt-14">
        <Container>
          <Breadcrumb
            items={[
              { label: tNav("about"), href: "/about" },
              { label: tTeam("title"), href: "/about#team" },
              { label: member.full_name },
            ]}
          />

          <div className="grid gap-8 lg:grid-cols-[minmax(18rem,0.42fr)_minmax(0,0.58fr)] lg:items-center">
            <div className="mx-auto w-full max-w-sm overflow-hidden rounded-[var(--radius-2xl)] border border-border/80 bg-background lg:mx-0 lg:max-w-none">
              <div className="relative aspect-[5/6] bg-muted">
                {member.photo ? (
                  <Image
                    src={member.photo}
                    alt={member.photo_alt ?? member.full_name}
                    fill
                    className="object-cover"
                    sizes="(min-width: 1024px) 40vw, 100vw"
                    priority
                  />
                ) : (
                  <div className="flex size-full items-end justify-start bg-linear-to-br from-primary/10 via-background to-secondary/10 p-8">
                    <span className="text-8xl font-black leading-none text-foreground/20">
                      {member.first_name.charAt(0)}
                    </span>
                  </div>
                )}
              </div>
            </div>

            <div className="max-w-2xl">
              <div className="flex flex-wrap items-center gap-3">
                {member.is_founder ? <Badge variant="secondary">{tTeam("founderBadge")}</Badge> : null}
                {member.position ? (
                  <span className="ui-kicker text-muted-foreground">
                    {member.position}
                  </span>
                ) : null}
              </div>

              <h1 className="mt-4 max-w-3xl text-balance text-4xl font-bold tracking-tight text-foreground sm:text-5xl">
                {member.full_name}
              </h1>

              {member.specialization ? (
                <p className="ui-copy mt-4 text-pretty text-lg leading-relaxed text-foreground/85">
                  {member.specialization}
                </p>
              ) : null}

              <div className="mt-6 flex flex-wrap items-center gap-3 text-sm text-muted-foreground">
                {member.location ? (
                  <div className="inline-flex items-center gap-2 rounded-full border border-border bg-background px-3 py-2">
                    <MapPin className="size-4" aria-hidden="true" />
                    <span>{member.location}</span>
                  </div>
                ) : null}
                {member.github_url ? (
                  <a
                    href={member.github_url}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="focus-ring inline-flex items-center gap-2 rounded-full border border-border bg-background px-3 py-2 font-medium text-foreground hover:border-primary/30"
                  >
                    <span>{tTeam("githubLabel")}</span>
                  </a>
                ) : null}
                {member.linkedin_url ? (
                  <a
                    href={member.linkedin_url}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="focus-ring inline-flex items-center gap-2 rounded-full border border-border bg-background px-3 py-2 font-medium text-foreground hover:border-primary/30"
                  >
                    <span>{tTeam("linkedinLabel")}</span>
                  </a>
                ) : null}
              </div>
            </div>
          </div>
        </Container>
      </Section>

      {member.bio ? (
        <Section className="py-12 sm:py-14">
          <Container narrow>
            <div className="max-w-3xl">
              <h2 className="text-2xl font-bold tracking-tight text-foreground">
                {tTeam("aboutTitle")}
              </h2>
              <div className="ui-copy mt-5 whitespace-pre-line text-pretty text-base leading-relaxed text-foreground">
                {member.bio}
              </div>
            </div>
          </Container>
        </Section>
      ) : null}

      {member.expertise && member.expertise.length > 0 ? (
        <Section className="border-y border-border bg-surface py-12 sm:py-14">
          <Container narrow>
            <h2 className="text-2xl font-bold tracking-tight text-foreground">
              {tTeam("expertiseTitle")}
            </h2>
            <ul className="mt-5 flex flex-wrap gap-3">
              {member.expertise.map((item) => (
                <li
                  key={item}
                  className="rounded-full border border-border bg-background px-4 py-2 text-sm font-medium text-foreground"
                >
                  {item}
                </li>
              ))}
            </ul>
          </Container>
        </Section>
      ) : null}

      {member.languages && member.languages.length > 0 ? (
        <Section className="py-12 sm:py-14">
          <Container narrow>
            <h2 className="text-2xl font-bold tracking-tight text-foreground">
              {tTeam("languagesTitle")}
            </h2>
            <ul className="mt-5 flex flex-wrap gap-3">
              {member.languages.map((item) => (
                <li
                  key={item}
                  className="rounded-full border border-border bg-surface px-4 py-2 text-sm font-medium text-foreground"
                >
                  {item}
                </li>
              ))}
            </ul>
          </Container>
        </Section>
      ) : null}

      {claims.length > 0 ? (
        <Section className="border-t border-border py-12 sm:py-14">
          <Container narrow>
            <h2 className="text-2xl font-bold tracking-tight text-foreground">
              {tTeam("claimsTitle")}
            </h2>
            <div className="mt-6 space-y-5">
              {claims.map((group) => (
                <section
                  key={group.category}
                  className="rounded-[var(--radius-xl)] border border-border/80 bg-surface p-5"
                >
                  <h3 className="ui-kicker text-secondary">
                    {group.label}
                  </h3>
                  <ul className="mt-4 space-y-3">
                    {group.items.map((claim) => (
                      <li key={claim} className="text-pretty text-sm leading-relaxed text-foreground/85">
                        {claim}
                      </li>
                    ))}
                  </ul>
                </section>
              ))}
            </div>
          </Container>
        </Section>
      ) : null}
    </>
  );
}
