FROM postgres:latest

ENV POSTGRES_USER postgres_admin
ENV POSTGRES_PASSWORD admin
ENV POSTGRES_DB ztpai

COPY ./docker/postgresql/initpostgres.sh /docker-entrypoint-initdb.d
