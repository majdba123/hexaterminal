/** Converts a public `?page=` value into a safe 1-based page number. */
export function parsePageParam(value: string | undefined): number {
  if (!value || !/^\d+$/.test(value)) return 1;

  const page = Number(value);
  return Number.isSafeInteger(page) && page >= 1 ? page : 1;
}
