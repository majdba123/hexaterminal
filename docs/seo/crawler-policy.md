# Crawler Policy

This document is the source of truth for how Hexa Terminal treats web crawlers
and AI bots. The policy is implemented in
[`frontend/app/robots.ts`](../../frontend/app/robots.ts); this file explains
*why* each decision was made and how to verify it.

## Environment gate: production vs. everything else

`robots.ts` reads a single environment variable, `NEXT_PUBLIC_ALLOW_INDEXING`.

| Environment | `NEXT_PUBLIC_ALLOW_INDEXING` | robots.txt result |
|---|---|---|
| Production | `"true"` (exact string) | Allow crawling per the policy below |
| Staging / preview / local dev | anything else (or unset) | `Disallow: /` for **all** user agents |

The default is **fail-safe**: unless a deployment explicitly sets
`NEXT_PUBLIC_ALLOW_INDEXING=true`, it serves a disallow-all robots.txt. This
means a staging box, a Vercel/preview deploy, or a developer's laptop can never
be accidentally indexed. Indexing is opt-in, not opt-out.

`frontend/.env.example` ships with `NEXT_PUBLIC_ALLOW_INDEXING=false` and a
comment stating it must only be `true` on the real production deployment.

Because the variable is read at build/render time, flipping it requires a
redeploy (or a rebuild) of the frontend — there is no way to toggle indexing
without an intentional deploy.

## Standard crawler policy (production only)

When indexing is enabled:

- `User-agent: *` → `Allow: /`, `Disallow: /api/`
  - Everything public is crawlable. `/api/` is disallowed because those paths
    are the JSON backend, not user-facing HTML, and have no SEO value.
- `Sitemap:` points at `${NEXT_PUBLIC_SITE_URL}/sitemap.xml`.

## AI crawler decisions

AI crawlers are treated **individually and deliberately**, not lumped under the
wildcard, so each decision is explicit and easy to reverse.

### OAI-SearchBot — ALLOWED

`OAI-SearchBot` is OpenAI's crawler for **ChatGPT Search** (the answer/citation
surface), distinct from model training. Allowing it means Hexa Terminal can
appear as a cited source when users ask ChatGPT about the systems, services, and
industries we build for.

- **Business implication:** ChatGPT Search is a discovery channel comparable to
  Google/Bing. Being findable there is upside with no material downside — it
  surfaces our public marketing content, which is exactly what it is for.

### GPTBot — ALLOWED (tracked separately)

`GPTBot` is OpenAI's crawler that **trains** OpenAI's models on crawled content.
It is a genuinely different decision from OAI-SearchBot and is called out
explicitly in `robots.ts` (rather than left to the wildcard) so it is a
conscious, one-line-reversible choice.

- **Current decision:** Allowed. Our public content is marketing material we
  *want* propagated; having it represented in model knowledge is a mild
  brand-awareness benefit.
- **Business implication:** If the company later decides it does not want its
  content used for model training (e.g. for competitive or IP reasons), flip
  this single rule to `disallow: "/"`. This does **not** affect OAI-SearchBot
  visibility — the two are independent.

### Other AI crawlers

Any AI crawler not named explicitly falls under `User-agent: *` and is therefore
allowed to crawl public pages in production. To block a specific bot, add a
dedicated rule with `disallow: "/"`; to block all AI training crawlers as a
class, add their individual user agents — there is no reliable wildcard for
"AI bots" so they must be enumerated.

## How to verify the generated robots.txt

`robots.ts` is a Next.js Metadata Route; it is emitted at `/robots.txt`.

**Staging / local (indexing disabled):**

```bash
# From a running frontend with NEXT_PUBLIC_ALLOW_INDEXING unset or != "true"
curl -s http://localhost:3000/robots.txt
# Expect:
#   User-Agent: *
#   Disallow: /
```

**Production (indexing enabled):**

```bash
# Build/run with NEXT_PUBLIC_ALLOW_INDEXING=true
NEXT_PUBLIC_ALLOW_INDEXING=true npm run build && npm run start
curl -s http://localhost:3000/robots.txt
# Expect: Allow: / and Disallow: /api/ for *, explicit OAI-SearchBot and
# GPTBot Allow rules, and a Sitemap: line.
```

To confirm the fail-safe, build **without** the flag and check that the output
is disallow-all even though the code contains an allow policy.

## Change log discipline

Any change to a crawler's allow/disallow status should:

1. Edit the rule in `frontend/app/robots.ts`.
2. Update the corresponding section here with the new decision and its business
   rationale.
3. Redeploy production for the change to take effect.
