import { Container } from "@/components/site/container";
import { CtaLink } from "@/components/site/cta-link";

export function CTA({
  title,
  subtitle,
  buttonLabel,
  href = "/start-a-project",
}: {
  title: string;
  subtitle?: string;
  buttonLabel: string;
  href?: string;
}) {
  return (
    <section className="border-y border-border bg-linear-to-br from-primary/10 via-transparent to-accent/10 py-20">
      <Container className="flex flex-col items-center gap-6 text-center">
        <h2 className="max-w-2xl text-balance text-3xl font-extrabold tracking-tight text-foreground sm:text-4xl">
          {title}
        </h2>
        {subtitle ? (
          <p className="max-w-xl text-pretty text-base leading-relaxed text-muted-foreground">{subtitle}</p>
        ) : null}
        <CtaLink href={href}>{buttonLabel}</CtaLink>
      </Container>
    </section>
  );
}
