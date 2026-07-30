import type { Metadata } from "next";
import Image from "next/image";
import { ExternalLink } from "lucide-react";
import { getTranslations, setRequestLocale } from "next-intl/server";
import { getTeam } from "@/lib/api/client";
import { Container } from "@/components/site/container";
import { Section } from "@/components/site/section";
import { Breadcrumb } from "@/components/site/breadcrumb";
import { EmptyState } from "@/components/site/empty-state";
import { Card, CardContent } from "@/components/ui/card";
import { JsonLd } from "@/components/site/json-ld";
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
    // Content-blocked in the route registry until founder-approved team
    // content exists -- see frontend/lib/routes/registry.ts.
    robots: resolveRobots(true),
  };
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
    <Section as="div">
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
          <div className="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            {team.map((member) => (
              <Card key={member.slug} className="overflow-hidden">
                <div className="relative aspect-square w-full overflow-hidden bg-muted">
                  {member.photo ? (
                    <Image
                      src={member.photo}
                      alt={member.photo_alt ?? ""}
                      fill
                      className="object-cover"
                      sizes="(min-width: 1024px) 25vw, 50vw"
                    />
                  ) : (
                    <div className="flex size-full items-center justify-center bg-linear-to-br from-primary/15 to-accent/15 text-3xl font-black text-primary/30">
                      {member.first_name.charAt(0)}
                    </div>
                  )}
                </div>
                <CardContent className="flex flex-col gap-1">
                  <h3 className="text-sm font-bold text-foreground">{member.full_name}</h3>
                  {member.position ? (
                    <p className="text-xs text-muted-foreground">{member.position}</p>
                  ) : null}
                  {member.bio ? (
                    <p className="mt-2 line-clamp-3 text-pretty text-xs leading-relaxed text-muted-foreground">
                      {member.bio}
                    </p>
                  ) : null}
                  {member.expertise && member.expertise.length > 0 ? (
                    <ul className="mt-2 flex flex-wrap gap-1">
                      {member.expertise.map((skill) => (
                        <li
                          key={skill}
                          className="rounded-full bg-muted px-2 py-0.5 text-xs text-muted-foreground"
                        >
                          {skill}
                        </li>
                      ))}
                    </ul>
                  ) : null}
                  {member.github_url || member.linkedin_url ? (
                    <div className="mt-3 flex gap-3">
                      {member.github_url ? (
                        <a
                          href={member.github_url}
                          target="_blank"
                          rel="noopener noreferrer"
                          className="focus-ring rounded text-muted-foreground hover:text-foreground"
                          aria-label="GitHub"
                        >
                          <ExternalLink className="size-4" />
                        </a>
                      ) : null}
                      {member.linkedin_url ? (
                        <a
                          href={member.linkedin_url}
                          target="_blank"
                          rel="noopener noreferrer"
                          className="focus-ring rounded text-muted-foreground hover:text-foreground"
                          aria-label="LinkedIn"
                        >
                          <ExternalLink className="size-4" />
                        </a>
                      ) : null}
                    </div>
                  ) : null}
                </CardContent>
              </Card>
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
