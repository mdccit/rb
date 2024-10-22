# Configure Passport Authentication
To use passport, it requires public and private key pair. To do that just run following commands

`php artisan key:generate`

then,

`php artisan passport:keys`

# Initial Migration, Seeders and Create authentication clients
To run all initial migrations, seeders and create authentication clients, just run following commands

`php artisan migrate_in_order`

**NOTE :** in this project has two databases;
1.	recruited_pro_v2_primary
2.	recruited_pro_v2_secondary

to applying these migrations you must need to switch primary db connection to both and run above command twice.


# Azure blob storage related environment variables 

AZURE_STORAGE_ACCOUNT_NAME=recruitedv2
AZURE_STORAGE_ACCOUNT_KEY="bfO3ptC+VdjUceYM6cOGgYdkufcRZ9GoyZw7r6/Ky8ptrLnRUsmDPzoNYAJw8KmEnTTpaeAqjlmR+AStFrfz3w=="
AZURE_STORAGE_CONTAINER_NAME=recruited
AZURE_STORAGE_URL=https://recruitedv2.blob.core.windows.net/


# Stripe Keys
STRIPE_KEY=pk_test_51Q5IlqB1aCt3RRccXbVS8aYnSTynl0TufY4s4mPxlYeZKbZrX2YpKxkwMBbeitKm8iWBAyWwWzcLyYByyE9sGegG00OJSEbT2i
STRIPE_SECRET=sk_test_51Q5IlqB1aCt3RRccS2Hy7sHOuQlWghvB77dhgI969TUSUYnKkrEAwGUpQ6KsPAKASrLRM9ygsHqIUSLXIZtGazDN00HH302iWE
STRIPE_WEBHOOK_SECRET=your-stripe-webhook-secret
# Stripe Subscription Price IDs
STRIPE_MONTHLY_PRICE_ID=price_1Q5LsbB1aCt3RRcc6eRGc3wo 
STRIPE_ANNUAL_PRICE_ID=price_1Q5LsbB1aCt3RRcc6eRGc3wo