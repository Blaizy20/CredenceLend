# Subscription Plan Schema Changes

This document summarizes the database-only implementation for the subscription plan feature.

## 1. SQL migration

Created:
- `setup/20260403_subscription_plan_schema.sql`

What it does:
- creates a new `plans` table
- adds `tenants.plan_id`
- adds `tenants.subscription_status`
- adds `tenants.subscription_started_at`
- adds `tenants.subscription_expires_at`
- adds tenant indexes for plan and subscription lookups
- adds `fk_tenants_plan`

## 2. Seed SQL

Created:
- `setup/20260403_subscription_plan_seed.sql`

What it does:
- seeds `BASIC`
- seeds `PROFESSIONAL`
- seeds `ENTERPRISE`
- backfills `tenants.plan_id` from existing `tenants.plan_code`
- backfills `subscription_started_at` from `tenants.created_at` when possible

## 3. Updated schema files

Updated:
- `schema.sql`
- `setup/loan_management_complete.sql`

Changes:
- added full `plans` table definition
- updated `tenants` table definition to include subscription columns
- kept `tenants.plan_code`
- added plan seed data to `setup/loan_management_complete.sql`

## 4. Backward compatibility notes

- `tenants.plan_code` was intentionally kept because the current PHP codebase still reads and writes plan code directly.
- `tenants.plan_id` is nullable for now so existing tenant creation code does not fail before PHP changes are added.
- `subscription_status` defaults to `ACTIVE` to avoid breaking current tenant creation and login flows before backend enforcement is implemented.
- Existing tenants can be mapped to the normalized `plans` table by running the seed SQL after the schema migration.
- The new schema does not yet enforce billing or feature access in PHP. It only prepares the database layer.

## 5. Recommended run order

1. Run `setup/20260403_subscription_plan_schema.sql`
2. Run `setup/20260403_subscription_plan_seed.sql`

For new installs:
- use `setup/loan_management_complete.sql`
