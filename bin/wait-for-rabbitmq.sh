#!/bin/bash

# RabbitMQ nastavení
HOST="rabbitmq"
PORT=5672
RETRY_INTERVAL=5 # Počet sekund mezi pokusy

echo "Start waiting for RabbitMQ at $HOST:$PORT..."

# Čekání na RabbitMQ dostupnost
while ! nc -z $HOST $PORT; do
    echo "$(date) - Čekám na dostupnost RabbitMQ na $HOST:$PORT..."
    sleep $RETRY_INTERVAL
done

echo "RabbitMQ na $HOST:$PORT je dostupný. Spouštím PHP-FPM..."

# Spuštění PHP-FPM
exec "$@"
