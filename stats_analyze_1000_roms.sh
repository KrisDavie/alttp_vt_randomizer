php artisan alttp:randomize --bulk 1000 --no-music --heartbeep=off --no-rom --item_placement=advanced --accessibility=items --state=open --glitches=none --weapons=randomized --hints=off --write-db-seed-data lttp.sfc myout

php artisan alttp:entrandomize --entrances crossed --dungeon_items full --accessibility items --goal fast_ganon --weapons randomized --state open --no-rom --no-music --heartbeep=off --write-db-seed-data --skip-md5 /data/alttp_jp1.0_noheader.sfc /data/seeds/ --bulk 12247
php artisan alttp:entrandomize --entrances crossed --dungeon_items full --accessibility items --goal fast_ganon --weapons randomized --state open --no-rom --no-music --heartbeep=off --write-db-seed-data --skip-md5 /data/alttp_jp1.0_noheader.sfc /data/seeds/ --bulk 50000

php artisan alttp:randomize --no-music --heartbeep=off --item_placement=advanced --accessibility=items --state=open --glitches=hybrid_major_glitches --weapons=randomized --hints=off --write-db-seed-data /data/alttp_jp1.0_noheader.sfc /data/seeds/ --bulk 1000 
