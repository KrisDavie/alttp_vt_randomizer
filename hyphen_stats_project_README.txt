step 1: install and run postgresql somewhere. i do it in a linux vm.

step 2: Uncomment the following line in php.ini, located inside your php install folder:

;extension=pdo_pgsql

(remove the leading semicolon)

step 3: rename the file in this repository named .env.example to just .env

then edit it to point to your postgres

mine looks like this:

DB_CONNECTION=pgsql
DB_HOST=192.168.xxx.xxx
DB_PORT=5432
DB_DATABASE=rando
DB_USERNAME=rando
DB_PASSWORD=rando

step 4: generate a bunch of seeds with the --write-db-seed-data option enabled.

it will populate your db with delicious juicy data.
