# Initial Migration, Seeders and Create authentication clients
To run all initial migrations, seeders and create authentication clients, just run following commands

php artisan migrate_in_order

NOTE : in this project has two databases;
1.	recruited_pro_v2_primary
2.	recruited_pro_v2_secondary

to applying these migrations you must need to switch primary db connection to both and run above command twice.
