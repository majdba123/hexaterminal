import * as React from "react";
import { cn } from "@/lib/utils";

export function Card({ className, ...props }: React.HTMLAttributes<HTMLDivElement>) {
  return (
    <div
      className={cn(
        "rounded-[var(--radius-lg)] border border-border bg-surface transition-colors",
        className,
      )}
      {...props}
    />
  );
}

export function CardHeader({ className, ...props }: React.HTMLAttributes<HTMLDivElement>) {
  return <div className={cn("p-6 pb-0", className)} {...props} />;
}

export function CardContent({ className, ...props }: React.HTMLAttributes<HTMLDivElement>) {
  return <div className={cn("p-6", className)} {...props} />;
}

export function CardFooter({ className, ...props }: React.HTMLAttributes<HTMLDivElement>) {
  return <div className={cn("flex items-center p-6 pt-0", className)} {...props} />;
}

export function CardTitle({
  className,
  as: Heading = "h3",
  ...props
}: React.HTMLAttributes<HTMLHeadingElement> & {
  /**
   * Heading level. Defaults to `h3`, which is right when the card sits in a
   * section that has its own `h2` heading (the home page). On a listing page
   * where the page `h1` is the only heading above the grid, pass `as="h2"` --
   * otherwise the document skips from h1 to h3 and axe flags `heading-order`.
   */
  as?: "h2" | "h3" | "h4";
}) {
  return (
    <Heading
      className={cn("text-balance text-lg font-bold leading-snug text-foreground", className)}
      {...props}
    />
  );
}

export function CardDescription({
  className,
  ...props
}: React.HTMLAttributes<HTMLParagraphElement>) {
  return (
    <p
      className={cn("text-pretty text-sm leading-relaxed text-muted-foreground", className)}
      {...props}
    />
  );
}
