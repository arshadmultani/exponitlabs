# Design Specifications & Architecture Sitemap

This directory contains technical design specifications defining the architecture, components, and implementation plans for **Exponit Labs**, managed via the **Elephant-Goldfish Model (EGM)** framework.

---

## Active & Historical Specifications

| Specification | Status | Primary Scope | Description / Links |
| :--- | :--- | :--- | :--- |
| [LOCAL_FIRST_DCR_PLAN.md](../../LOCAL_FIRST_DCR_PLAN.md) | Approved / In Progress | Field App (`/mr`) | Architecture for local-first, offline-capable Daily Call Report (DCR) entry, doctor directory, and Dexie.js IndexedDB storage. |
| [VISUAL_AID_SPEC.md](../../VISUAL_AID_SPEC.md) | Approved | Field App (`/mr`) | Offline e-detailing and visual aid presenter engine for Medical Representatives (MRs). |
| [exponit-offline-quickdraw.md](../../exponit-offline-quickdraw.md) | Reference | AR / Field App | QuickDraw canvas and offline asset synchronization protocol. |
| [specs/001-offline-mr-field-app/](../../specs/001-offline-mr-field-app/) | Implemented / Active | Mobile MR Experience | Base feature spec and checklist for the mobile-first offline field web app. |

---

## EGM Specifications Index

New features and architectural refactors designed with `/egm-draft` and reviewed with `/egm-review` are recorded here.

| Document | Target Subsystem | Status | Owner |
| :--- | :--- | :--- | :--- |
| [2026-10-27_DRIFT_REPORT.md](./2026-10-27_DRIFT_REPORT.md) | `app/` Codebase Contracts | Verified (0 drift) | EGM Bootstrap |
| [doctor_portfolio.md](./doctor_portfolio.md) | Admin Console (`/console/doctor-portfolio`) | Proposed (Draft Complete) | Exponit Labs Core Architecture |
| [concepts_brief.md](./concepts_brief.md) | Doctor Portfolio & ROI Tracker | Concept Brief Aligned | EGM Brainstorm |


---

## Spec Lifecycle Guidelines

1. **Ideation**: Run `/egm-brainstorm <topic>` to explore requirements and generate `concepts_brief.md`.
2. **Drafting**: Run `/egm-draft` to iteratively create a specification file under `docs/designs/<feature-name>.md`.
3. **Review**: Run `/egm-review docs/designs/<feature-name>.md` to run the 5-stage zero-context Goldfish review swarm.
4. **Resolution**: Run `/egm-resolve` to address review findings and update the document.
5. **Human Approval**: Open a PR or submit for human review before proceeding to implementation.
6. **Implementation**: Build according to the approved plan, validating with Pest tests (`php artisan test --compact`).
7. **Drift Detection**: Run `/egm-drift-report` weekly to reconcile checked-in specs with active code changes.
