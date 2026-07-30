/**
 * How often the hero reveal replays, in milliseconds.
 *
 * The film is a one-shot logo reveal, not a loop, so it settles on the mark
 * and then re-plays on a long cycle rather than running continuously. Ten
 * minutes is long enough that a reader is never interrupted mid-sentence, and
 * short enough that anyone lingering on the page sees the brand move again.
 *
 * Shared so the backdrop and the wordmark caption stay on the same beat
 * instead of drifting apart with two hard-coded numbers.
 */
export const HERO_REPLAY_MS = 10 * 60 * 1000;

/**
 * Whether a replay should actually happen right now.
 *
 * Replaying into a hidden tab or an off-screen hero burns decode and paint for
 * something nobody can see, so a cycle that lands at a bad moment is SKIPPED
 * rather than queued -- queueing would make several fire at once when the tab
 * comes back.
 */
export function canReplay(isOnScreen: boolean): boolean {
  if (!isOnScreen) return false;
  if (typeof document !== "undefined" && document.visibilityState === "hidden") return false;

  return true;
}
