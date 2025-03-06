# Log into database
sudo docker exec -it postgresql-wdpai sh
psql --username "$POSTGRES_USER" --dbname "$POSTGRES_DB"

# Load test database
dropdb --username "$POSTGRES_USER" wdpai
createdb --username "$POSTGRES_USER" wdpai
psql --single-transaction --username "$POSTGRES_USER" -d wdpai < /var/lib/postgresql/data/wdpai_db_dump.sql