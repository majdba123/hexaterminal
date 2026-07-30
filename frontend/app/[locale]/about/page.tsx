import type { Metadata } from "next";
import Image from "next/image";
import { ExternalLink } from "lucide-react";
import { getTranslations, setRequestLocale } from "next-intl/server";
import { pageMetadata } from "@/lib/seo/page-metadata";
import { getTeam } from "@/lib/api/client";
import { Container } from "@/components/site/container";
import { Section } from "@/components/site/section";
import { SectionHeading } from "@/components/site/section-heading";
import { Breadcrumb } from "@/components/site/breadcrumb";
import { EmptyState } from "@/components/site/empty-state";
import { Card, CardContent } from "@/components/ui/card";

export async function generateMetadata({
  params,
}: {
  params: Promise<{ locale: string }>;
}): Promise<Metadata> {
  const { locale } = await params;
  const t = await getTranslations({ locale, namespace: "about" });
  return pageMetadata({
    locale,
    path: "/about",
    title: t("title"),
    description: t("subtitle"),
  });
}

export default async function AboutPage({
  params,
}: {
  params: Promise<{ locale: string }>;
}) {
  const { locale } = await params;
  setRequestLocale(locale);

  const [t, tc, team] = await Promise.all([
    getTranslations("about"),
    getTranslations("common"),
    getTeam(locale),
  ]);

  return (
    <Section as="div">
      <Container>
        <Breadcrumb items={[{ label: t("title") }]} />
        <h1 className="max-w-2xl text-balance text-3xl font-extrabold tracking-tight text-foreground sm:text-4xl">
          {t("title")}
        </h1>
        <p className="mt-3 max-w-xl text-pretty text-base leading-relaxed text-muted-foreground">
          {t("subtitle")}
        </p>

        <div className="mt-16">
          <SectionHeading align="start" title={t("teamTitle")} subtitle={t("teamSubtitle")} />
          {team.length > 0 ? (
            <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
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
            <EmptyState
              title={tc("noResults")}
              description={tc("noResultsDesc")}
              action={{ href: "/start-a-project", label: tc("emptyCta") }}
            />
          )}
        </div>
      </Container>
    </Section>
  );
}
