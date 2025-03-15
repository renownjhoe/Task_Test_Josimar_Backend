FROM mysql:8.0

ENV MYSQL_USER=appuser
ENV MYSQL_PASSWORD=apppassword
ENV MYSQL_DATABASE=blumeDB

COPY entrypoint.sh /docker-entrypoint.sh
RUN chmod +x /docker-entrypoint.sh

ENTRYPOINT ["/docker-entrypoint.sh", "mysqld"]

EXPOSE 3306