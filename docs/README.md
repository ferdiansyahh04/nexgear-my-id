# NexGear Documentation

Engineering docs for the NexGear Storefront — written so future-you, future-collaborators, and (most immediately) the project examiner can audit the design without reading the entire codebase.

## Contents

- [`adr/`](./adr/) — **Architecture Decision Records.** One file per pivotal decision, in chronological order. Each entry captures the context, the choice we made, the alternatives we considered, and the consequences we accepted.
- [`SETUP.md`](./SETUP.md) — End-to-end local setup guide that goes deeper than the project README.
- [`PROJECT_REPORT.md`](./PROJECT_REPORT.md) — Academic-style project report covering problem, scope, design, and reflection.

## How to read these docs

Start with `PROJECT_REPORT.md` if you want the **why**. Drop into `adr/` if you want to drill into a specific decision. Use `SETUP.md` if you just want to run it locally.

## Doc conventions

- Every ADR uses the **MADR** template variant: title, status, context, decision, consequences.
- Decisions are immutable. If we change our mind, we add a new ADR that supersedes the old one (we don't edit history).
- Code references in docs link to specific files using project-relative paths so they stay valid when the repo moves.
