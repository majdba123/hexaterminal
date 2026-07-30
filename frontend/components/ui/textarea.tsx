import * as React from "react";
import { cn } from "@/lib/utils";

export interface TextareaProps extends React.TextareaHTMLAttributes<HTMLTextAreaElement> {
  /** Renders the invalid state -- see components/ui/input.tsx. */
  error?: boolean;
}

export const Textarea = React.forwardRef<HTMLTextAreaElement, TextareaProps>(
  ({ className, error, ...props }, ref) => {
    return (
      <textarea
        ref={ref}
        className={cn(
          "focus-ring flex min-h-32 w-full rounded-[var(--radius-md)] border border-border bg-background px-3.5 py-3 text-sm text-foreground placeholder:text-muted-foreground disabled:pointer-events-none disabled:opacity-50",
          error && "border-destructive",
          className,
        )}
        {...props}
      />
    );
  },
);
Textarea.displayName = "Textarea";
