#!/bin/bash

# Funkce pro přidání konzuméra
add_consumer() {
    QUEUE=$1
    COMMAND="consume-${QUEUE}"
    nohup sudo php cli app:$COMMAND > /srv/log/rabbitMQ/${COMMAND}_stdout.log 2> /srv/log/rabbitMQ/${COMMAND}_stderr.log &
    echo "Consumer for $COMMAND started"
}

# Funkce pro odebrání konzuméra
remove_consumer() {
    QUEUE=$1
    COMMAND="consume-${QUEUE}"
    pkill -f "php cli app:$COMMAND"
    echo "Consumer for $COMMAND stopped"
}

# Kontrola argumentů
if [ $# -lt 2 ]; then
    echo "Usage: $0 {add|remove} <queue>"
    exit 1
fi

ACTION=$1
QUEUE=$2

# Přepínač pro akce
case $ACTION in
    add)
        add_consumer $QUEUE
        ;;
    remove)
        remove_consumer $QUEUE
        ;;
    *)
        echo "Invalid action. Use 'add' or 'remove'."
        exit 1
        ;;
esac
