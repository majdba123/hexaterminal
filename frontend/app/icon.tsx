import { ImageResponse } from "next/og";

/**
 * Programmatic browser/tab icon. Replaces the generic default favicon with a
 * Hexa Terminal "H" monogram in the official charcoal/blue/cyan palette
 * (see app/globals.css). Deterministic — rendered once at build time.
 */
export const size = { width: 32, height: 32 };
export const contentType = "image/png";

export default function Icon() {
  return new ImageResponse(
    (
      <div
        style={{
          width: "100%",
          height: "100%",
          display: "flex",
          alignItems: "center",
          justifyContent: "center",
          background: "linear-gradient(135deg, #10162a 0%, #0a0e1a 100%)",
          color: "#00d1ff",
          fontSize: 22,
          fontWeight: 800,
          borderRadius: 6,
        }}
      >
        H
      </div>
    ),
    { ...size },
  );
}
