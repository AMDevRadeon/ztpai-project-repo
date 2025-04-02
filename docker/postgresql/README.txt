# Log into database
sudo docker exec -it postgresql-ztpai sh
psql --username "$POSTGRES_USER" --dbname "$POSTGRES_DB"

# Load test database
dropdb --username "$POSTGRES_USER" ztpai
createdb --username "$POSTGRES_USER" ztpai
psql --single-transaction --username "$POSTGRES_USER" -d ztpai < /var/lib/postgresql/data/wdpai_db_dump.sql