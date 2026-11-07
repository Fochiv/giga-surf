---
name: Giga Surf order fulfillment
description: Product rule for how telecom bundles are fulfilled and shown to customers.
---

Giga Surf bundles are delivered manually by the owner. The site collects payment through Fapshi; once that payment is verified, the owner performs the telecom recharge outside the site and clicks “Livrer” in Admin. The customer sees the delivered state in order tracking.

**Why:** the user explicitly said they manage each bundle recharge themselves after receiving payment.

**How to apply:** Do not add automatic telecom top-up calls or mark an order delivered merely because a payment request was created. Verify the Fapshi payment first.
