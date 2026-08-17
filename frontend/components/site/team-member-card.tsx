import Image from "next/image";
import { ArrowUpRight } from "lucide-react";
import { Link } from "@/i18n/navigation";
import type { TeamMember } from "@/lib/api/types";
import { cn } from "@/lib/utils";
import { Badge } from "@/components/ui/badge";

export function TeamMemberCard({
  member,
  href,
  ctaLabel,
  founderLabel,
  className,
}: {
  member: TeamMember;
  href: string;
  ctaLabel: string;
  founderLabel: string;
  className?: string;
}) {
  const expertise = member.expertise?.filter(Boolean).slice(0, 3) ?? [];

  return (
    <article className={cn("h-full", className)}>
      <Link
        href={href}
        className="focus-ring group flex h-full flex-col overflow-hidden rounded-[var(--radius-xl)] border border-border/80 bg-background transition-colors hover:border-primary/40"
      >
        <div className="relative aspect-[5/4] overflow-hidden border-b border-border/70 bg-muted">
          {member.photo ? (
            <Image
              src={member.photo}
              alt={member.photo_alt ?? member.full_name}
              fill
              className="object-cover transition-transform duration-300 group-hover:scale-[1.02]"
              sizes="(min-width: 1280px) 30vw, (min-width: 768px) 50vw, 100vw"
            />
          ) : (
            <div className="flex size-full items-end justify-start bg-linear-to-br from-primary/10 via-background to-secondary/10 p-5">
              <span className="text-6xl font-black leading-none text-foreground/20">
                {member.first_name.charAt(0)}
              </span>
            </div>
          )}
        </div>

        <div className="flex flex-1 flex-col gap-4 p-5">
          <div className="flex flex-wrap items-center gap-2">
            {member.is_founder ? <Badge variant="secondary">{founderLabel}</Badge> : null}
            {(member.github_url || member.linkedin_url) ? (
              <div className="flex flex-wrap items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.16em] text-muted-foreground">
                {member.github_url ? <span>GitHub</span> : null}
                {member.linkedin_url ? <span>LinkedIn</span> : null}
              </div>
            ) : null}
          </div>

          <div className="space-y-2">
            <h2 className="text-xl font-bold tracking-tight text-foreground">{member.full_name}</h2>
            {member.position ? (
              <p className="text-sm font-medium text-muted-foreground">{member.position}</p>
            ) : null}
            {member.specialization ? (
              <p className="text-pretty text-sm leading-relaxed text-foreground/80">
                {member.specialization}
              </p>
            ) : null}
          </div>

          {expertise.length > 0 ? (
            <ul className="flex flex-wrap gap-2">
              {expertise.map((item) => (
                <li
                  key={item}
                  className="rounded-full border border-border/80 bg-surface px-3 py-1 text-xs font-medium text-muted-foreground"
                >
                  {item}
                </li>
              ))}
            </ul>
          ) : null}

          <div className="mt-auto flex items-center justify-between border-t border-border/70 pt-4 text-sm font-semibold text-foreground">
            <span>{ctaLabel}</span>
            <ArrowUpRight
              className="size-4 transition-transform duration-200 group-hover:-translate-y-0.5 group-hover:translate-x-0.5 rtl:group-hover:-translate-x-0.5 rtl:group-hover:translate-y-0.5"
              aria-hidden="true"
            />
          </div>
        </div>
      </Link>
    </article>
  );
}
