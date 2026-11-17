# Bugfix Requirements Document

## Introduction

This document addresses a critical type safety bug in the `BillingService::generateInvoice()` method. The method declares a return type of `Invoice|array|null` but when called with `dryRun=true`, it returns a plain PHP array instead of matching the declared return type signature. This causes a TypeError when the method is called from the `GenerateMonthlyInvoices` console command.

## Bug Analysis

### Current Behavior (Defect)

1.1 WHEN `generateInvoice()` is called with `dryRun=true` THEN the system returns a plain PHP array with keys: 'amount', 'tax', 'total_amount', 'description', 'days', 'unit_price'

1.2 WHEN `GenerateMonthlyInvoices` command calls `generateAllPendingInvoices()` with `dryRun=true` THEN the system returns stdClass objects instead of the expected array structure due to type mismatch

1.3 WHEN the `GenerateMonthlyInvoices` command processes the return value from `generateInvoice()` in dry-run mode THEN the system throws TypeError: "Return value must be of type ?App\Models\Invoice, array returned"

### Expected Behavior (Correct)

2.1 WHEN `generateInvoice()` is called with `dryRun=true` THEN the system SHALL return an array containing billing calculation data without attempting to create a database record

2.2 WHEN `generateInvoice()` is called with `dryRun=false` (default) THEN the system SHALL return an Invoice model instance or null

2.3 WHEN `GenerateMonthlyInvoices` command processes the return value from `generateInvoice()` in dry-run mode THEN the system SHALL return the calculation data as a structured array without throwing type errors

### Unchanged Behavior (Regression Prevention)

3.1 WHEN `generateInvoice()` is called with `dryRun=false` and a valid customer THEN the system SHALL CONTINUE TO create a new Invoice record in the database with calculated amounts

3.2 WHEN `generateInvoice()` is called with a customer that has no active package THEN the system SHALL CONTINUE TO return null without creating an invoice

3.3 WHEN `generateInvoice()` is called for a customer that already has an invoice for the billing period THEN the system SHALL CONTINUE TO return null without creating duplicate invoices

3.4 WHEN `generateAllPendingInvoices()` is called with `dryRun=true` THEN the system SHALL CONTINUE TO skip actual invoice creation and only return calculation results