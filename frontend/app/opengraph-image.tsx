import { ImageResponse } from "next/og";

/**
 * Default social share image (Open Graph + Twitter). Branded wordmark and
 * tagline in the official Hexa Terminal palette (see app/globals.css). Pages
 * that set their own openGraph.images in generateMetadata override this.
 * Deterministic and text-only — no fabricated screenshots or UI.
 */
export const alt = "Hexa Terminal — software systems that run real businesses";
export const size = { width: 1200, height: 630 };
export const contentType = "image/png";

export default function OpengraphImage() {
  return new ImageResponse(
    (
      <div
        style={{
          width: "100%",
          height: "100%",
          display: "flex",
          flexDirection: "column",
          justifyContent: "center",
          padding: "80px",
          background: "linear-gradient(135deg, #10162a 0%, #0a0e1a 70%)",
        }}
      >
        <div style={{ display: "flex", alignItems: "center", gap: 24 }}>
          <div
            style={{
              width: 96,
              height: 96,
              display: "flex",
              alignItems: "center",
              justifyContent: "center",
              borderRadius: 20,
              background: "linear-gradient(135deg, #3663d8 0%, #00d1ff 100%)",
              color: "#071022",
              fontSize: 64,
              fontWeight: 800,
            }}
          >
            H
          </div>
          <span style={{ color: "#f4f6fb", fontSize: 64, fontWeight: 800, letterSpacing: -1 }}>
            Hexa Terminal
          </span>
        </div>
        <span
          style={{
            marginTop: 40,
            color: "#9aa4c4",
            fontSize: 36,
            fontWeight: 500,
            maxWidth: 900,
            lineHeight: 1.3,
          }}
        >
          Software systems that run real businesses.
        </span>
        <div
          style={{
            marginTop: 48,
            display: "flex",
            height: 8,
            width: 240,
            borderRadius: 4,
            background: "linear-gradient(90deg, #3663d8 0%, #00d1ff 100%)",
          }}
        />
      </div>
    ),
    { ...size },
  );
}
