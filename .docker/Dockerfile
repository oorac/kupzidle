# Použijte existující PHP image jako základ
FROM wodby/php:8.1-dev

# Instalace supervisord a netcat
USER root
RUN apk add --no-cache supervisor netcat-openbsd jq

# Kopírování konfiguračních souborů supervisord do kontejneru
COPY ./.docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Kopírování skriptu pro čekání na RabbitMQ
COPY ./bin/wait-for-rabbitmq.sh /usr/local/bin/wait-for-rabbitmq.sh
RUN chmod +x /usr/local/bin/wait-for-rabbitmq.sh

# Kopírování Xdebug konfiguračního souboru
#COPY ./.docker/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini


COPY ./.docker/php.ini /usr/local/etc/php

# Kopírování monitorovacího skriptu
#COPY ./bin/monitor_cpu.sh /usr/local/bin/monitor_cpu.sh
RUN #chmod +x /usr/local/bin/monitor_cpu.sh

# Kopírování skriptu pro správu consumerů
#COPY ./bin/manage_consumers.sh /usr/local/bin/manage_consumers.sh
RUN #chmod +x /usr/local/bin/manage_consumers.sh

# Nastavení pracovního adresáře
WORKDIR /srv

# Spuštění supervisord při startu kontejneru
CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
