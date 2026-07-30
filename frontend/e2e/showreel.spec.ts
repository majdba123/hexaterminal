import { test, expect } from "@playwright/test";

test("showreel modal opens, closes on Escape, and restores focus to the trigger", async ({
  page,
}) => {
  await page.goto("/en");

  const trigger = page.getByRole("button", { name: "Watch Showreel" });
  await trigger.scrollIntoViewIfNeeded();
  await trigger.click();

  const dialog = page.getByRole("dialog");
  await expect(dialog).toBeVisible();
  // The video only mounts once the modal is open.
  await expect(dialog.locator("video")).toBeVisible();

  await page.keyboard.press("Escape");
  await expect(page.getByRole("dialog")).toBeHidden();

  // Radix returns focus to the element that opened the dialog.
  await expect(trigger).toBeFocused();
});
