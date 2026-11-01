# Filament Pages Blueprint

## Overview
Houses standalone administrative pages, custom dashboards, and management reporting interfaces within the `/console` panel.

## Pages & Components
- `DoctorPortfolio`: Commercial Performance & ROI Tracker at `/console/doctor-portfolio`.
  - Group: `Field Operations`
  - Features: Multi-horizon tab switching (`current_fy`, `last_3_months`, `last_month`, `all_time`), sortable doctor ledger by `net_pnl`, performance tier badges (`incubation`, `high_yield`, `balanced`, `under_yielding`), single-record revenue logging modal, bulk CSV prescription sales import, and header overview metric cards (`DoctorPortfolioStatsWidget`).

## Subdirectories & Boundaries
- [Auth](./Auth/EGM.md): User authentication and profile editing pages.
