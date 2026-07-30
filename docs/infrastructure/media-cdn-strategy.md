# Media & CDN Strategy

Production media strategy. **No production media is moved by this document** —
moving media requires deployment approval. This is the plan and the env surface.

## Current media origins (audited)

| Origin | Where | Concern |
|--------|-------|---------|
| `public/` static assets | Laravel `public/`, frontend `public/` | fine for logos/icons; not for user/CMS media |
| Laravel storage | `storage/app` via CMS uploads | not CDN-fronted; not signed |
| Legacy placeholder hosts | `placehold.co` (seed data) | must not appear in production output |
| Legacy Drive links | `drive.google.com` team photos | temporary; replace with CMS media |
| Remote icons | `cdn-icons-png.flaticon.com` | third-party dependency; review licensing |

Source: `frontend/next.config.ts` `images.remotePatterns`.

## Target strategy

1. **Object storage** (e.g. S3-compatible) as the canonical store for all
   CMS-uploaded media. Filament uploads write here, not to local disk.
2. **CDN** in front of object storage with a long public cache TTL for
   immutable, content-hashed asset URLs.
3. **Image transformations** via `next/image` (already used) plus origin-side
   resizing; add the CDN host to `remotePatterns` and remove
   `placehold.co` / `drive.google.com` before production.
4. **Video** (hero/showreel): served from CDN with a poster image; respect
   reduced-motion and avoid full download on mobile (see performance docs).
5. **Signed/private access** for any non-public media (draft assets, internal
   documents).
6. **Lifecycle:** deletion policy for unpublished/removed media; backups;
   rights/licensing metadata retained per asset.

## Environment surface (to add when infra is provisioned — placeholders only)

```
MEDIA_DISK=s3
MEDIA_S3_BUCKET=
MEDIA_S3_REGION=
MEDIA_CDN_URL=            # e.g. https://cdn.hexaterminal.com
```

Secrets stay environment-only; none are committed.

## Pre-production checklist

- [ ] No `placehold.co` / `drive.google.com` URLs in built output.
- [ ] CMS uploads land in object storage, not local disk.
- [ ] CDN host added to `next.config.ts` `remotePatterns`; legacy hosts removed.
- [ ] Poster + reduced-motion fallback verified for hero/showreel video.
- [ ] Cache-Control policy verified for immutable assets.
