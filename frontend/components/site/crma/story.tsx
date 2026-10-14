/**
 * CRMA, told as a HexaTerminal engineering capability showcase:
 *
 *   Opening → 01 Operating system → 02 How work moves → 03 Deterministic logic
 *   → 04 Workflow automation → 05 Governed AI → 06 Knowledge → 07 Architecture
 *   → 08 Security & tenancy → 09 Integrations & MCP → 10 Responsive
 *   → 11 Arabic & English → 12 Engineering evidence → Gallery → Services → CTA
 *
 * Evidence rule: every statement lives in `messages.*.crma` and was checked
 * against the CRMA repository (code, config, tests) before it was written --
 * see docs/design/crma-design-qa.md. This file adds no claims of its own: no
 * client, no users, no business result. Screens are the running app on seeded
 * demonstration data. Motion is scroll-reveal only (components/site/crma/motion.tsx), off for reduced motion.
 */
import { ArrowRight, ShieldCheck } from "lucide-react";
import { getTranslations } from "next-intl/server";
import { Link } from "@/i18n/navigation";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Breadcrumb } from "@/components/site/breadcrumb";
import { Container } from "@/components/site/container";
import { Section } from "@/components/site/section";
import { SectionHeading } from "@/components/site/section-heading";
import { cn } from "@/lib/utils";
import { CrmaGallery, type GalleryItem } from "./gallery";
import { ModuleViewer, type ModuleTab } from "./module-viewer";
import { Parallax, Reveal, Stagger } from "./motion";
import { Shot } from "./shot";
import type { CrmaShotKey } from "./shots";

type Item = { name: string; desc: string };
type Tier = { title: string; nodes: (Item & { key?: boolean })[] };
type IntegrationTier = { title: string; note: string; items: Item[] };

const pad = (n: number) => String(n).padStart(2, "0");

function Kicker({ index, label }: { index: number; label: string }) {
  return `${pad(index)} · ${label}`;
}

function Dot({ tone = "secondary" }: { tone?: "secondary" | "warning" }) {
  return (
    <span
      aria-hidden="true"
      className={cn("mt-2 size-2 shrink-0 rounded-full", tone === "warning" ? "bg-warning" : "bg-secondary")}
    />
  );
}

function Points({ items, columns = 1 }: { items: Item[]; columns?: 1 | 2 }) {
  return (
    <ul className={cn("grid gap-3", columns === 2 && "md:grid-cols-2")}>
      {items.map((p) => (
        <li key={p.name} className="flex gap-3 rounded-[var(--radius-md)] border border-border/70 bg-background px-5 py-4">
          <Dot />
          <div>
            <h3 className="text-base font-bold text-foreground">{p.name}</h3>
            <p className="mt-1 text-pretty text-sm leading-relaxed text-muted-foreground">{p.desc}</p>
          </div>
        </li>
      ))}
    </ul>
  );
}

function Label({ children, className }: { children: React.ReactNode; className?: string }) {
  return <p className={cn("text-xs font-semibold uppercase tracking-[0.18em] text-secondary", className)}>{children}</p>;
}

export async function CrmaStory() {
  const [t, tc] = await Promise.all([getTranslations("crma"), getTranslations("caseStudies")]);
  const s = (k: string) => t(`sections.${k}`);

  const modules = t.raw("os.modules") as ModuleTab[];
  const flow = t.raw("flow.steps") as Item[];
  const rules = t.raw("logic.items") as Item[];
  const triad = t.raw("workflow.triad") as Item[];
  const wf = t.raw("workflow.items") as Item[];
  const chain = t.raw("ai.chain") as Item[];
  const risks = t.raw("ai.risks") as Item[];
  const govPoints = t.raw("ai.points") as Item[];
  const kbFlow = t.raw("knowledge.flow") as Item[];
  const kbPoints = t.raw("knowledge.points") as Item[];
  const tiers = t.raw("arch.tiers") as Tier[];
  const security = t.raw("security.items") as Item[];
  const integrations = t.raw("integrations.tiers") as IntegrationTier[];
  const phones = t.raw("mobile.phones") as { key: CrmaShotKey; name: string; alt: string }[];
  const rtlPoints = t.raw("rtl.points") as string[];
  const metrics = t.raw("evidence.metrics") as { value: string; label: string }[];
  const gallery = t.raw("gallery.items") as GalleryItem[];
  const services = t.raw("services.items") as (Item & { href: string })[];
  const APPROVE_STEP = 5; // "Approve" in the operating flow
  const HUMAN_STEP = 6; // "Human approval" in the governed chain

  return (
    <article>
      {/* ── Opening ─────────────────────────────────────────────────────── */}
      <Section as="header" className="overflow-hidden bg-surface">
        <Container>
          <Breadcrumb items={[{ label: tc("title"), href: "/case-studies" }, { label: t("meta.breadcrumb") }]} />
          <div className="grid items-end gap-8 lg:grid-cols-[minmax(0,1.1fr)_minmax(0,0.9fr)] lg:gap-12">
            <div className="flex flex-col items-start gap-4">
              <Badge>{t("hero.eyebrow")}</Badge>
              <h1 className="text-balance text-4xl font-extrabold tracking-tight text-foreground sm:text-5xl lg:text-6xl">
                {t("hero.title")}
              </h1>
            </div>
            <p className="text-pretty text-lg leading-8 text-muted-foreground">{t("hero.lead")}</p>
          </div>
          <div className="mt-8 flex flex-col gap-3 border-t border-border/70 pt-5 lg:flex-row lg:items-center lg:justify-between">
            <p className="text-sm text-muted-foreground" dir="ltr">
              <span className="sr-only">{t("hero.stackLabel")}: </span>
              {t("hero.stack")}
            </p>
            <p className="flex shrink-0 items-center gap-2 text-sm font-medium text-foreground">
              <span aria-hidden="true" className="size-2 rounded-full bg-warning" />
              {t("hero.disclosure")}
            </p>
          </div>
          <div className="relative mt-10 lg:pe-[16%]">
            <Shot shot="dashboard" alt={t("hero.shotAlt")} label={t("hero.shotLabel")} sizes="(min-width: 1280px) 1000px, 100vw" priority />
            <div className="absolute -bottom-10 end-0 hidden w-[36%] lg:block">
              <Parallax>
                <Shot shot="actionCenter" alt={t("hero.centerAlt")} label={t("hero.centerLabel")} sizes="30vw" />
              </Parallax>
            </div>
          </div>
          <p className="mt-8 max-w-3xl text-pretty text-sm leading-relaxed text-muted-foreground lg:mt-20">
            {t("hero.positioning")}
          </p>
        </Container>
      </Section>

      {/* ── 01 Operating system ─────────────────────────────────────────── */}
      <Section id="os" aria-labelledby="os-title">
        <Container>
          <SectionHeading align="start" badge={Kicker({ index: 1, label: s("os") })} title={<span id="os-title">{t("os.title")}</span>} subtitle={t("os.lede")} />
          <ModuleViewer tabs={modules} label={t("os.tabsLabel")} frameLabel={t("os.frameLabel")} />
        </Container>
      </Section>

      {/* ── 02 How work moves ───────────────────────────────────────────── */}
      <Section id="flow" aria-labelledby="flow-title" className="border-y border-border/70 bg-surface">
        <Container>
          <SectionHeading align="start" badge={Kicker({ index: 2, label: s("flow") })} title={<span id="flow-title">{t("flow.title")}</span>} subtitle={t("flow.lede")} />
          <Stagger as="ol" aria-label={t("flow.label")} className="grid gap-3 sm:grid-cols-2 lg:grid-cols-7">
            {flow.map((step, i) => {
              const gate = i === APPROVE_STEP;
              return (
                <li
                  key={step.name}
                  className={cn(
                    "relative flex flex-col gap-2 rounded-[var(--radius-md)] border px-4 py-4",
                    gate ? "crma-gate border-warning/50 bg-warning/10" : "border-border/70 bg-background",
                  )}
                >
                  <span className={cn("text-xs font-semibold tabular-nums", gate ? "text-warning" : "text-secondary")}>{pad(i + 1)}</span>
                  <h3 className="text-sm font-bold uppercase tracking-wide text-foreground rtl:tracking-normal">{step.name}</h3>
                  <p className="text-pretty text-sm leading-relaxed text-muted-foreground">{step.desc}</p>
                </li>
              );
            })}
          </Stagger>
          <p className="mt-6 flex max-w-3xl gap-2 text-sm text-muted-foreground">
            <ShieldCheck aria-hidden="true" className="mt-0.5 size-4 shrink-0 text-warning" />
            {t("flow.note")}
          </p>
        </Container>
      </Section>

      {/* ── 03 Deterministic business logic ─────────────────────────────── */}
      <Section id="logic" aria-labelledby="logic-title">
        <Container>
          <SectionHeading
            align="start"
            badge={Kicker({ index: 3, label: s("logic") })}
            title={<span id="logic-title">{t("logic.title")}</span>}
            subtitle={t("logic.lede")}
            className="[&_h2]:max-w-3xl"
          />
          <Stagger className="grid gap-5 lg:grid-cols-2">
            <Shot shot="pipeline" alt={t("logic.pipelineAlt")} label={t("logic.pipelineLabel")} sizes="(min-width: 1024px) 50vw, 100vw" />
            <Shot shot="dealWorkspace" alt={t("logic.dealAlt")} label={t("logic.dealLabel")} sizes="(min-width: 1024px) 50vw, 100vw" />
          </Stagger>
          <Reveal className="mt-8">
            <Points items={rules} columns={2} />
          </Reveal>
        </Container>
      </Section>

      {/* ── 04 Workflow automation ──────────────────────────────────────── */}
      <Section id="workflow" aria-labelledby="workflow-title" className="border-y border-border/70 bg-surface">
        <Container>
          <SectionHeading align="start" badge={Kicker({ index: 4, label: s("workflow") })} title={<span id="workflow-title">{t("workflow.title")}</span>} subtitle={t("workflow.lede")} />
          <Stagger as="ol" aria-label={t("workflow.triadLabel")} className="grid gap-3 md:grid-cols-3">
            {triad.map((step, i) => (
              <li key={step.name} className="rounded-[var(--radius-md)] border border-border/70 bg-background px-5 py-5">
                <Label>{pad(i + 1)}</Label>
                <h3 className="mt-2 text-lg font-bold text-foreground">{step.name}</h3>
                <p className="mt-1 text-pretty text-sm leading-relaxed text-muted-foreground">{step.desc}</p>
              </li>
            ))}
          </Stagger>
          <div className="mt-8 grid gap-8 lg:grid-cols-[minmax(0,1.3fr)_minmax(0,0.7fr)]">
            <Reveal>
              <Shot shot="workflowBuilder" alt={t("workflow.shotAlt")} label={t("workflow.shotLabel")} sizes="(min-width: 1024px) 62vw, 100vw" />
            </Reveal>
            <Reveal>
              <Points items={wf} />
            </Reveal>
          </div>
        </Container>
      </Section>

      {/* ── 05 Governed AI ──────────────────────────────────────────────── */}
      <Section id="governed-ai" aria-labelledby="governed-ai-title">
        <Container>
          <div className="mb-12 flex flex-col items-start gap-4">
            <Badge>{Kicker({ index: 5, label: s("ai") })}</Badge>
            <h2 id="governed-ai-title" className="text-balance text-4xl font-extrabold tracking-tight text-foreground sm:text-5xl">
              <span className="block">{t("ai.title1")}</span>
              <span className="block text-warning">{t("ai.title2")}</span>
            </h2>
            <p className="max-w-3xl text-pretty text-base leading-relaxed text-muted-foreground">{t("ai.lede")}</p>
          </div>

          <div className="grid gap-10 lg:grid-cols-12">
            <Stagger as="ol" aria-label={t("ai.chainLabel")} className="relative flex flex-col gap-2 lg:col-span-5">
              <span aria-hidden="true" className="absolute inset-y-4 start-[0.95rem] w-px bg-border" />
              {chain.map((step, i) => {
                const human = i === HUMAN_STEP;
                return (
                  <li key={step.name} className="relative grid grid-cols-[2rem_minmax(0,1fr)] gap-3">
                    <span
                      aria-hidden="true"
                      className={cn(
                        "relative z-10 mt-3 flex size-8 items-center justify-center rounded-full border text-[11px] font-semibold tabular-nums",
                        human ? "border-warning bg-warning text-warning-foreground" : "border-border bg-surface text-secondary",
                      )}
                    >
                      {pad(i + 1)}
                    </span>
                    <div
                      className={cn(
                        "rounded-[var(--radius-md)] border px-4 py-3",
                        human ? "crma-gate border-warning/60 bg-warning/10" : "border-border/70 bg-background",
                      )}
                    >
                      <h3 className={cn("text-sm font-bold", human ? "text-warning" : "text-foreground")}>{step.name}</h3>
                      <p className="mt-0.5 text-sm leading-relaxed text-muted-foreground">{step.desc}</p>
                    </div>
                  </li>
                );
              })}
            </Stagger>

            <div className="flex min-w-0 flex-col gap-6 lg:col-span-7">
              <Shot shot="copilotApproval" alt={t("ai.shotAlt")} label={t("ai.shotLabel")} sizes="(min-width: 1024px) 58vw, 100vw" />
              <div className="rounded-[var(--radius-lg)] border border-border/70 bg-surface p-5">
                <div className="flex flex-wrap items-center gap-2">
                  <code className="me-1 text-sm font-semibold text-foreground" dir="ltr">
                    {t("ai.diff.action")}
                  </code>
                  <Badge variant="warning">{t("ai.diff.risk")}</Badge>
                  <Badge variant="outline">{t("ai.diff.status")}</Badge>
                </div>
                <div className="mt-4 overflow-x-auto">
                  <table className="w-full min-w-[20rem] border-collapse text-sm">
                    <caption className="sr-only">{t("ai.diff.caption")}</caption>
                    <thead>
                      <tr className="text-start text-xs uppercase tracking-[0.14em] text-muted-foreground rtl:tracking-normal">
                        <th scope="col" className="py-2 pe-4 text-start font-semibold">{t("ai.diff.field")}</th>
                        <th scope="col" className="py-2 pe-4 text-start font-semibold">{t("ai.diff.current")}</th>
                        <th scope="col" className="py-2 text-start font-semibold">{t("ai.diff.proposed")}</th>
                      </tr>
                    </thead>
                    <tbody dir="ltr">
                      {[
                        ["stage", "Qualification", "Proposal"],
                        ["stage_id", "1", "2"],
                      ].map(([field, from, to]) => (
                        <tr key={field} className="border-t border-border/70">
                          <th scope="row" className="py-2 pe-4 text-start font-normal">
                            <code className="text-foreground">{field}</code>
                          </th>
                          <td className="py-2 pe-4 text-muted-foreground">
                            <del className="decoration-warning">{from}</del>
                          </td>
                          <td className="py-2 font-semibold text-secondary">
                            <ins className="no-underline">{to}</ins>
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
                <div className="mt-4 flex flex-wrap gap-2" aria-hidden="true">
                  <Badge variant="success">{t("ai.diff.approve")}</Badge>
                  <Badge variant="outline">{t("ai.diff.reject")}</Badge>
                </div>
                <p className="mt-3 text-xs leading-relaxed text-muted-foreground">{t("ai.diff.note")}</p>
              </div>
            </div>
          </div>

          <div className="mt-12 grid gap-10 lg:grid-cols-12">
            <div className="lg:col-span-5">
              <Label>{t("ai.risksLabel")}</Label>
              <ol className="mt-4 overflow-hidden rounded-[var(--radius-md)] border border-border/70">
                {risks.map((r, i) => {
                  const strict = r.name === "HIGH_EXTERNAL" || r.name === "CRITICAL";
                  return (
                    <li key={r.name} className={cn("grid gap-1 px-4 py-3 sm:grid-cols-[10rem_minmax(0,1fr)] sm:gap-4", i > 0 && "border-t border-border/70")}>
                      <code dir="ltr" className={cn("text-sm font-semibold", strict ? "text-warning" : "text-secondary")}>
                        {r.name}
                      </code>
                      <span className="text-sm text-muted-foreground">{r.desc}</span>
                    </li>
                  );
                })}
              </ol>
            </div>
            <div className="lg:col-span-7">
              <Points items={govPoints} columns={2} />
              <p className="mt-5 flex gap-2 text-sm leading-relaxed text-muted-foreground">
                <span aria-hidden="true" className="mt-1.5 size-2 shrink-0 rounded-full bg-warning" />
                {t("ai.disclosure")}
              </p>
            </div>
          </div>
        </Container>
      </Section>

      {/* ── 06 Knowledge ────────────────────────────────────────────────── */}
      <Section id="knowledge" aria-labelledby="knowledge-title" className="border-y border-border/70 bg-surface">
        <Container>
          <SectionHeading align="start" badge={Kicker({ index: 6, label: s("knowledge") })} title={<span id="knowledge-title">{t("knowledge.title")}</span>} subtitle={t("knowledge.lede")} />
          <Stagger as="ol" aria-label={t("knowledge.flowLabel")} className="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
            {kbFlow.map((step, i) => (
              <li key={step.name} className="rounded-[var(--radius-md)] border border-border/70 bg-background px-4 py-4">
                <Label>{pad(i + 1)}</Label>
                <h3 className="mt-2 text-base font-bold text-foreground" dir="auto">{step.name}</h3>
                <p className="mt-1 text-sm leading-relaxed text-muted-foreground">{step.desc}</p>
              </li>
            ))}
          </Stagger>
          <div className="mt-8 grid gap-8 lg:grid-cols-2">
            <Shot shot="knowledgeBase" alt={t("knowledge.shotAlt")} label={t("knowledge.shotLabel")} sizes="(min-width: 1024px) 50vw, 100vw" />
            <Points items={kbPoints} />
          </div>
        </Container>
      </Section>

      {/* ── 07 Architecture ─────────────────────────────────────────────── */}
      <Section id="architecture" aria-labelledby="architecture-title">
        <Container>
          <SectionHeading align="start" badge={Kicker({ index: 7, label: s("arch") })} title={<span id="architecture-title">{t("arch.title")}</span>} subtitle={t("arch.lede")} />
          <Stagger className="grid gap-4 lg:grid-cols-[minmax(0,0.9fr)_minmax(0,1.6fr)_minmax(0,0.95fr)]">
            {tiers.map((tier, ti) => (
              <div key={tier.title} className="rounded-[var(--radius-lg)] border border-dashed border-border bg-surface p-4">
                <h3 className="mb-4 text-xs font-semibold uppercase tracking-[0.18em] text-secondary rtl:tracking-normal">{tier.title}</h3>
                <ul className={cn("grid gap-3", ti === 1 && "sm:grid-cols-2")}>
                  {tier.nodes.map((node, ni) => (
                    <li
                      key={node.name}
                      className={cn(
                        "rounded-[var(--radius-md)] border px-4 py-3",
                        node.key ? "border-primary/50 bg-primary/10" : "border-border/70 bg-background",
                        ti === 1 && ni === 0 && "sm:col-span-2",
                      )}
                    >
                      <p className="text-sm font-bold text-foreground" dir="auto">{node.name}</p>
                      <p className="mt-1 text-sm leading-relaxed text-muted-foreground">{node.desc}</p>
                    </li>
                  ))}
                </ul>
              </div>
            ))}
          </Stagger>
          <div className="mt-6 rounded-[var(--radius-md)] border border-border/70 bg-surface px-5 py-4">
            <Label>{t("arch.deployLabel")}</Label>
            <p className="mt-2 text-sm leading-relaxed text-muted-foreground">{t("arch.deploy")}</p>
          </div>
        </Container>
      </Section>

      {/* ── 08 Security, tenancy, governance ────────────────────────────── */}
      <Section id="security" aria-labelledby="security-title" className="border-y border-border/70 bg-surface">
        <Container>
          <SectionHeading align="start" badge={Kicker({ index: 8, label: s("security") })} title={<span id="security-title">{t("security.title")}</span>} subtitle={t("security.lede")} />
          <div className="grid gap-8 lg:grid-cols-[minmax(0,1.1fr)_minmax(0,0.9fr)]">
            <Points items={security} columns={2} />
            <Shot shot="roles" alt={t("security.shotAlt")} label={t("security.shotLabel")} sizes="(min-width: 1024px) 42vw, 100vw" className="self-start" />
          </div>
        </Container>
      </Section>

      {/* ── 09 Integrations & MCP ───────────────────────────────────────── */}
      <Section id="integrations" aria-labelledby="integrations-title">
        <Container>
          <SectionHeading align="start" badge={Kicker({ index: 9, label: s("integrations") })} title={<span id="integrations-title">{t("integrations.title")}</span>} subtitle={t("integrations.lede")} />
          <div className="grid gap-5 lg:grid-cols-2">
            {integrations.map((tier, ti) => (
              <section
                key={tier.title}
                aria-labelledby={`integrations-tier-${ti}`}
                className={cn(
                  "rounded-[var(--radius-lg)] border p-5",
                  ti === 0 ? "border-primary/40 bg-primary/5" : "border-border/70 bg-surface",
                )}
              >
                <h3 id={`integrations-tier-${ti}`} className="text-lg font-bold text-foreground">
                  {tier.title}
                </h3>
                <p className="mt-1 text-xs text-muted-foreground">{tier.note}</p>
                <ul className="mt-4 flex flex-col gap-4">
                  {tier.items.map((it) => (
                    <li key={it.name} className="border-t border-border/70 pt-4">
                      <p className="text-sm font-bold text-foreground" dir="auto">{it.name}</p>
                      <p className="mt-1 text-sm leading-relaxed text-muted-foreground">{it.desc}</p>
                    </li>
                  ))}
                </ul>
              </section>
            ))}
          </div>
          <Shot shot="mcpConnectors" alt={t("integrations.shotAlt")} label={t("integrations.shotLabel")} sizes="(min-width: 1280px) 1000px, 100vw" className="mt-8" />
        </Container>
      </Section>

      {/* ── 10 Responsive ───────────────────────────────────────────────── */}
      <Section id="mobile" aria-labelledby="mobile-title" className="border-y border-border/70 bg-surface">
        <Container>
          <SectionHeading align="start" badge={Kicker({ index: 10, label: s("mobile") })} title={<span id="mobile-title">{t("mobile.title")}</span>} subtitle={t("mobile.lede")} />
          <Stagger as="ul" className="grid grid-cols-2 gap-x-4 gap-y-8 lg:grid-cols-4 lg:gap-6">
            {phones.map((p) => (
              <li key={p.key}>
                <Shot shot={p.key} alt={p.alt} sizes="(min-width: 1024px) 22vw, 46vw" className="rounded-[1.4rem]" />
                <p className="mt-3 text-sm font-semibold text-foreground">{p.name}</p>
              </li>
            ))}
          </Stagger>
          <p className="mt-8 max-w-3xl text-sm text-muted-foreground">{t("mobile.note")}</p>
        </Container>
      </Section>

      {/* ── 11 Arabic & English ─────────────────────────────────────────── */}
      <Section id="bilingual" aria-labelledby="bilingual-title">
        <Container>
          <SectionHeading align="start" badge={Kicker({ index: 11, label: s("rtl") })} title={<span id="bilingual-title">{t("rtl.title")}</span>} subtitle={t("rtl.lede")} />
          <div className="grid gap-5 lg:grid-cols-2">
            <Shot shot="pipeline" alt={t("rtl.ltrAlt")} label={t("rtl.ltrLabel")} dir="ltr" sizes="(min-width: 1024px) 50vw, 100vw" />
            <Reveal from="end">
              <Shot shot="rtlPipeline" alt={t("rtl.rtlAlt")} label={t("rtl.rtlLabel")} dir="rtl" sizes="(min-width: 1024px) 50vw, 100vw" />
            </Reveal>
          </div>
          <ul className="mt-8 grid gap-3 md:grid-cols-2">
            {rtlPoints.map((p) => (
              <li key={p} className="flex gap-3 text-sm leading-relaxed text-muted-foreground">
                <Dot />
                <span>{p}</span>
              </li>
            ))}
          </ul>
        </Container>
      </Section>

      {/* ── 12 Engineering evidence ─────────────────────────────────────── */}
      <Section id="evidence" aria-labelledby="evidence-title" className="border-y border-border/70 bg-surface">
        <Container>
          <SectionHeading align="start" badge={Kicker({ index: 12, label: s("evidence") })} title={<span id="evidence-title">{t("evidence.title")}</span>} subtitle={t("evidence.lede")} className="mb-6" />
          <p className="mb-10 text-sm text-muted-foreground">
            <span className="font-semibold text-foreground">{t("evidence.dateLabel")}: </span>
            {t("evidence.date")}
          </p>
          <Reveal>
            <dl className="grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-4">
              {metrics.map((m) => (
                <div key={m.label} className="flex flex-col rounded-[var(--radius-md)] border border-border/70 bg-background px-4 py-4">
                  <dt className="order-2 mt-1 text-sm text-muted-foreground" dir="auto">{m.label}</dt>
                  <dd className="order-1 text-3xl font-extrabold tabular-nums tracking-tight text-foreground" dir="ltr">
                    {m.value}
                  </dd>
                </div>
              ))}
            </dl>
          </Reveal>
          <div className="mt-8 rounded-[var(--radius-md)] border border-border/70 bg-background px-5 py-4">
            <Label>{t("evidence.testsLabel")}</Label>
            <p className="mt-2 text-base font-semibold text-foreground">{t("evidence.tests")}</p>
            <p className="mt-1 text-sm text-muted-foreground">{t("evidence.testsNote")}</p>
          </div>
        </Container>
      </Section>

      {/* ── Gallery ─────────────────────────────────────────────────────── */}
      <Section id="screens" aria-labelledby="screens-title">
        <Container>
          <SectionHeading align="start" title={<span id="screens-title">{t("gallery.title")}</span>} subtitle={t("gallery.note")} />
          <CrmaGallery
            items={gallery}
            labels={{
              open: t("gallery.open"),
              close: t("gallery.close"),
              previous: t("gallery.previous"),
              next: t("gallery.next"),
              counter: t.raw("gallery.counter") as string,
            }}
          />
        </Container>
      </Section>

      {/* ── Related services ────────────────────────────────────────────── */}
      <Section id="services" aria-labelledby="services-title" className="border-t border-border/70 bg-surface">
        <Container>
          <SectionHeading align="start" badge={s("services")} title={<span id="services-title">{t("services.title")}</span>} subtitle={t("services.lede")} />
          <ul className="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
            {services.map((sv) => (
              <li key={sv.name}>
                <Link
                  href={sv.href}
                  className="focus-ring group flex h-full flex-col gap-1 rounded-[var(--radius-md)] border border-border/70 bg-background px-5 py-4 transition-colors hover:border-secondary/50"
                >
                  <span className="flex items-center justify-between gap-3 text-base font-bold text-foreground">
                    {sv.name}
                    <ArrowRight aria-hidden="true" className="size-4 shrink-0 text-secondary rtl:rotate-180" />
                  </span>
                  <span className="text-sm leading-relaxed text-muted-foreground">{sv.desc}</span>
                </Link>
              </li>
            ))}
          </ul>
        </Container>
      </Section>

      {/* ── Closing ─────────────────────────────────────────────────────── */}
      <Section aria-labelledby="crma-cta-title">
        <Container>
          <div className="flex flex-col items-start gap-5 rounded-[calc(var(--radius-lg)+0.25rem)] border border-primary/30 bg-primary/5 px-6 py-10 sm:px-10 sm:py-14">
            <h2 id="crma-cta-title" className="max-w-2xl text-balance text-3xl font-extrabold tracking-tight text-foreground sm:text-4xl">
              {t("cta.title")}
            </h2>
            <p className="max-w-2xl text-pretty text-base leading-relaxed text-muted-foreground">{t("cta.body")}</p>
            <div className="mt-2 flex flex-wrap gap-3">
              <Button asChild size="lg">
                <Link href="/start-a-project">
                  {t("cta.primary")}
                  <ArrowRight className="rtl:rotate-180" aria-hidden="true" />
                </Link>
              </Button>
              <Button asChild size="lg" variant="outline">
                <Link href="/case-studies">
                  {t("cta.secondary")}
                  <ArrowRight className="rtl:rotate-180" aria-hidden="true" />
                </Link>
              </Button>
            </div>
          </div>
        </Container>
      </Section>
    </article>
  );
}
