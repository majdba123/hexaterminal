import { Skeleton } from "@/components/ui/skeleton";
import { Container } from "@/components/site/container";
import { Section } from "@/components/site/section";

/**
 * Structural skeletons for the route-level loading.tsx files.
 *
 * Structural, not spinners: each block sits where the real content will land
 * (breadcrumb, heading, then the grid or article body), so the layout does not
 * jump when the server data arrives. Every page here does its data fetching
 * with `await` in a Server Component, so without these a navigation left the
 * previous screen frozen with no acknowledgement that anything was happening.
 *
 * aria-hidden + a polite status line: a screen reader should hear "Loading"
 * once, not read out a dozen meaningless placeholder boxes.
 *
 * DELIBERATELY NOT APPLIED to the [slug] detail routes, even though those are
 * the slowest ones. A loading.tsx turns its segment into a streamed response,
 * and streaming flushes the HTTP headers before the page's data fetch
 * resolves -- so a later notFound() can no longer set the status code, and an
 * unknown slug answers 200 with not-found content instead of a real 404
 * (caught by e2e/not-found.spec.ts). Soft-404s are worse for this site than a
 * missing skeleton, so the detail routes stay non-streamed. DetailSkeleton is
 * still used by /search, which never calls notFound().
 */
function LoadingAnnouncement({ label }: { label: string }) {
  return (
    <p role="status" aria-live="polite" className="sr-only">
      {label}
    </p>
  );
}

/** Listing pages: breadcrumb + heading + a grid of cards. */
export function ListingSkeleton({
  label,
  cards = 6,
  columns = 3,
}: {
  label: string;
  cards?: number;
  columns?: 3 | 4;
}) {
  return (
    <Section as="div">
      <Container>
        <LoadingAnnouncement label={label} />
        <div aria-hidden="true">
          <Skeleton className="h-5 w-32" />
          <div className="mt-6 flex flex-col gap-4">
            <Skeleton className="h-10 w-72 max-w-full" />
            <Skeleton className="h-5 w-96 max-w-full" />
          </div>
          <div
            className={
              columns === 4
                ? "mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4"
                : "mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3"
            }
          >
            {Array.from({ length: cards }).map((_, i) => (
              <div key={i} className="flex flex-col gap-3">
                <Skeleton className="aspect-16/9 w-full" />
                <Skeleton className="h-6 w-3/4" />
                <Skeleton className="h-4 w-full" />
                <Skeleton className="h-4 w-2/3" />
              </div>
            ))}
          </div>
        </div>
      </Container>
    </Section>
  );
}

/** Detail pages: breadcrumb + title + cover + body copy. */
export function DetailSkeleton({ label }: { label: string }) {
  return (
    <Section as="div">
      <Container narrow>
        <LoadingAnnouncement label={label} />
        <div aria-hidden="true">
          <Skeleton className="h-5 w-48" />
          <Skeleton className="mt-6 h-12 w-full" />
          <Skeleton className="mt-3 h-5 w-1/2" />
          <Skeleton className="mt-8 aspect-16/9 w-full" />
          <div className="mt-8 flex flex-col gap-3">
            {Array.from({ length: 6 }).map((_, i) => (
              <Skeleton key={i} className={i % 3 === 2 ? "h-4 w-2/3" : "h-4 w-full"} />
            ))}
          </div>
        </div>
      </Container>
    </Section>
  );
}
