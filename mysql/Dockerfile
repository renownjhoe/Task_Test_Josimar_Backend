FROM mysql:8.0

ENV MYSQL_USER=root
ENV MYSQL_PASSWORD=323@34lkssdafkkasdf
ENV MYSQL_DATABASE=blumeDB

# Create the user and grant privileges
RUN mysql -u root -e "CREATE USER IF NOT EXISTS '${MYSQL_USER}'@'%' IDENTIFIED BY '${MYSQL_PASSWORD}';"
RUN mysql -u root -e "GRANT ALL PRIVILEGES ON ${MYSQL_DATABASE}.* TO '${MYSQL_USER}'@'%';"
RUN mysql -u root -e "FLUSH PRIVILEGES;"

EXPOSE 3306