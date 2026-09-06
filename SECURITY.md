# Security Policy

## Reporting a vulnerability

Please report suspected security issues privately to **majdbayer77@gmail.com**.

Do not publish credentials, access tokens, private keys, personal data, exploit payloads, or live-environment details in a public GitHub issue before remediation is available.

A useful report includes the affected component, reproducible steps, impact, authentication/authorization context, and sanitized supporting evidence.

## Priority areas

High-priority reports include authentication or authorization bypass, CMS/admin privilege escalation, API data exposure, unsafe uploads, injection, CORS/security-header weaknesses, replay/abuse paths, secret leakage, and production deployment vulnerabilities.

## Secrets and client configuration

Production secrets belong in deployment environments, GitHub Secrets, or provider-managed secret stores and must not be committed to the repository.

Any credential that has ever entered Git history must be treated as exposed and rotated/revoked at its provider. Deleting it from the current branch is not sufficient.

Public/client keys must be provider-restricted by origin/application/API scope as appropriate.

## Supported code

Security remediation targets the current default branch and maintained production deployment path. Historical revisions are not supported production baselines.
