import { cn } from "@/lib/utils";

export function Section({
  className,
  as: Comp = "section",
  ...props
}: React.HTMLAttributes<HTMLElement> & { as?: React.ElementType }) {
  return <Comp className={cn("py-16 sm:py-24", className)} {...props} />;
}
