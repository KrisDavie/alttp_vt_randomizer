This is a fork of the zelda randomizer for the purpose of dumping data to a postgres
database for statistical analysis. It's more convenient to do this directly inside the randomizer
code.

How to set it up:

step 1: install and run postgresql somewhere. i do it in a linux vm.

step 2: create a user and database in postgres. (i suggest naming them both rando)

step 3: run the sql in stats_db_schema.sql to get the db schema and item/location IDs

step 4: Uncomment the following line in php.ini, located inside your php install folder:

;extension=pdo_pgsql

(remove the leading semicolon)

step 5: rename the file in this repository named .env.example to just .env

then edit it to point to your postgres

mine looks like this:

DB_CONNECTION=pgsql
DB_HOST=192.168.xxx.xxx
DB_PORT=5432
DB_DATABASE=rando
DB_USERNAME=rando
DB_PASSWORD=rando

step 6: generate a bunch of seeds with the --write-db-seed-data option enabled.

it will populate your db with delicious juicy data.
