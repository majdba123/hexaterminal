import { Container } from "@/components/site/container";
import { CtaLink } from "@/components/site/cta-link";
import { Button } from "@/components/ui/button";
import { Link } from "@/i18n/navigation";

export function CTA({
  eyebrow,
  title,
  subtitle,
  buttonLabel,
  secondaryButtonLabel,
  href = "/start-a-project",
  secondaryHref = "/contact",
}: {
  eyebrow?: string;
  title: string;
  subtitle?: string;
  buttonLabel: string;
  secondaryButtonLabel?: string;
  href?: string;
  secondaryHref?: string;
}) {
  return (
    <section className="border-y border-border bg-linear-to-br from-primary/10 via-transparent to-accent/10 py-20">
      <Container className="flex flex-col items-center gap-6 text-center">
        {eyebrow ? (
          <span className="text-sm font-semibold text-secondary">{eyebrow}</span>
        ) : null}
        <h2 className="max-w-2xl text-balance text-3xl font-extrabold tracking-tight text-foreground sm:text-4xl">
          {title}
        </h2>
        {subtitle ? (
          <p className="max-w-xl text-pretty text-base leading-relaxed text-muted-foreground">{subtitle}</p>
        ) : null}
        <div className="flex w-full flex-col justify-center gap-3 sm:w-auto sm:flex-row">
          <CtaLink href={href}>{buttonLabel}</CtaLink>
          {secondaryButtonLabel ? (
            <Button asChild variant="outline" size="lg">
              <Link href={secondaryHref}>{secondaryButtonLabel}</Link>
            </Button>
          ) : null}
        </div>
      </Container>
    </section>
  );
}
