import { ImageResponse } from "next/og";

/**
 * Apple touch icon (home-screen). Hexa Terminal "H" monogram in the official
 * palette (see app/globals.css). Deterministic — rendered at build time.
 */
export const size = { width: 180, height: 180 };
export const contentType = "image/png";

export default function AppleIcon() {
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
          fontSize: 120,
          fontWeight: 800,
        }}
      >
        H
      </div>
    ),
    { ...size },
  );
}
