#!/bin/bash

# Funkce pro získání aktuálního zatížení CPU
get_cpu_usage() {
    # Použití top pro získání zatížení CPU
    top_output=$(top -bn1 | grep "Cpu(s)")
    echo "Top output: $top_output"

    # Extrakce procenta volného CPU z výstupu `top`
    idle_cpu=$(echo "$top_output" | awk -F'id,' '{print $2}' | awk '{print $1}')
    echo "Idle CPU: $idle_cpu"

    # Výpočet využití CPU
    cpu_usage=$(echo "100 - $idle_cpu" | bc)
    echo "Parsed CPU usage: $cpu_usage"
    echo $cpu_usage
}

# Funkce pro získání informací o frontách z RabbitMQ
get_queue_info() {
    curl -u user:password -s 'http://localhost:15672/api/queues' | jq '.[] | {name: .name, messages: .messages}'
}

# Funkce pro přidání konzuméra
add_consumer() {
    COMMAND=$1
    nohup sudo php cli app:$COMMAND > /srv/log/rabbitMQ/${COMMAND}_stdout.log 2> /srv/log/rabbitMQ/${COMMAND}_stderr.log &
    echo "Consumer for $COMMAND started"
}

# Funkce pro odebrání konzuméra
remove_consumer() {
    COMMAND=$1
    pkill -f "php cli app:$COMMAND"
    echo "Consumer for $COMMAND stopped"
}

# Funkce pro kontrolu a úpravu consumerů
manage_consumers() {
    cpu_usage=$(get_cpu_usage)
    echo "Current CPU usage: $cpu_usage%"

    queue_info=$(get_queue_info)
    echo "Queue info: $queue_info"

    # Zjištění fronty s nejvíce zprávami
    busiest_queue=$(echo $queue_info | jq -r 'max_by(.messages) | .name')
    busiest_queue_messages=$(echo $queue_info | jq -r 'max_by(.messages) | .messages')

    echo "Busiest queue: $busiest_queue with $busiest_queue_messages messages"

    if (( $(echo "$cpu_usage > 80" | bc -l) )); then
        echo "High CPU usage detected. Stopping some consumers..."
        remove_consumer $busiest_queue
    elif (( $(echo "$cpu_usage < 50" | bc -l) )); then
        if (( $busiest_queue_messages > 0 )); then
            echo "Low CPU usage and messages in queue. Starting more consumers for $busiest_queue..."
            add_consumer $busiest_queue
        else
            echo "No messages in queue. Stopping consumers for $busiest_queue..."
            remove_consumer $busiest_queue
        fi
    else
        echo "CPU usage is moderate. No changes needed."
    fi
}

# Hlavní smyčka pro pravidelnou kontrolu CPU a front
while true; do
    manage_consumers
    sleep 10 # Kontrola každých 10 sekund
done
