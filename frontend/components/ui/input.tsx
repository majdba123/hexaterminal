import * as React from "react";
import { cn } from "@/lib/utils";

export interface InputProps extends React.InputHTMLAttributes<HTMLInputElement> {
  /**
   * Renders the invalid state. Pair it with `aria-invalid` and an
   * `aria-describedby` pointing at the field's error text -- a border colour
   * alone is not an accessible error signal. components/ui/field.tsx wires
   * all three together, so prefer that over setting this by hand.
   */
  error?: boolean;
}

export const Input = React.forwardRef<HTMLInputElement, InputProps>(
  ({ className, error, ...props }, ref) => {
    return (
      <input
        ref={ref}
        className={cn(
          "focus-ring flex h-11 w-full rounded-[var(--radius-md)] border border-border bg-background px-3.5 text-sm text-foreground placeholder:text-muted-foreground disabled:pointer-events-none disabled:opacity-50",
          error && "border-destructive",
          className,
        )}
        {...props}
      />
    );
  },
);
Input.displayName = "Input";
