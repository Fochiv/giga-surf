---
name: Giga Surf payment networks
description: Product rule for separating the bundle operator from the payment network.
---

Customers may pay for a bundle from any telecom operator using either Orange Money or MTN Mobile Money; the bundle network and payer's payment network are independent.

Fapshi Live Direct Pay activation is separate from webhook setup; a configured webhook does not grant permission to initiate Live direct payments.

As of 2026-11-11, the user confirmed Fapshi Live Direct Pay works with MTN Mobile Money, while Fapshi has a problem with Orange Money payments. Treat Orange failures as provider-side until Fapshi confirms resolution.

**Why:** the user wants a choice of payment network independent of bundle operator. Fapshi's documentation states that Live Direct Pay is disabled by default and needs provider approval; the user later confirmed MTN works while Orange is failing on Fapshi's side.

**How to apply:** Keep the bundle operator for fulfillment and send the selected payment network to the payment provider. If Live Direct Pay returns HTTP 403, verify Live authorization and credentials. For Orange failures, check Fapshi's service status before changing the app.
