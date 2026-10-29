<?php
require __DIR__ . '/../app/config/database.php';
echo db()->query('SELECT version()')->fetchColumn(), PHP_EOL;
echo db()->query("SELECT count(*) FROM information_schema.tables
                  WHERE table_schema='public' AND table_type='BASE TABLE'")->fetchColumn(), " tabel\n";