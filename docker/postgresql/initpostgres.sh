#!/bin/sh
set -e

psql -v ON_ERROR_STOP=1 --username="$POSTGRES_USER" --dbname="$POSTGRES_DB" <<-EOSQL
    CREATE USER rwhead WITH ENCRYPTED PASSWORD 's1em1e_ln1ane' NOCREATEDB;

    GRANT USAGE ON SCHEMA public TO rwhead;
    GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO rwhead;
    GRANT EXECUTE ON ALL FUNCTIONS IN SCHEMA public TO rwhead;
    GRANT EXECUTE ON ALL PROCEDURES IN SCHEMA public TO rwhead;
    GRANT EXECUTE ON ALL ROUTINES IN SCHEMA public TO rwhead;
EOSQL