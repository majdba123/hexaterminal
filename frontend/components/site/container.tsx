import { cn } from "@/lib/utils";

export function Container({
  className,
  narrow,
  ...props
}: React.HTMLAttributes<HTMLDivElement> & { narrow?: boolean }) {
  return (
    <div
      className={cn(
        "mx-auto w-full px-5 sm:px-8",
        narrow ? "max-w-(--container-narrow)" : "max-w-(--container-content)",
        className,
      )}
      {...props}
    />
  );
}
