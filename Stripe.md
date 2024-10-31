
``` 
stripe listen --forward-to http://127.0.0.1:8000/api/v2/stripe/webhook
```

stripe trigger invoice.payment_succeeded

stripe trigger invoice.payment_failed

stripe trigger customer.subscription.created

stripe trigger payment_intent.succeeded

stripe trigger customer.subscription.trial_will_end

stripe trigger invoice.upcoming

stripe trigger customer.subscription.deleted

stripe trigger customer.source.expiring