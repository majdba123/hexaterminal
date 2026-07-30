"use client";

import { Link } from "@/i18n/navigation";
import { Button } from "@/components/ui/button";
import { trackEvent } from "@/components/site/analytics-script";

export function CtaLink({ href, children }: { href: string; children: React.ReactNode }) {
  return (
    <Button asChild size="lg" onClick={() => trackEvent("cta_click", { href })}>
      <Link href={href}>{children}</Link>
    </Button>
  );
}
