#!/bin/bash

# Puerto inicial
PORT=8000

# Buscar un puerto libre
while lsof -i :$PORT >/dev/null 2>&1; do
    PORT=$((PORT+1))
done

echo "----------------------------------------"
echo "Servidor local iniciado con éxito"
echo "Proyecto: vetpractica"
echo "URL: http://localhost:$PORT"
echo "----------------------------------------"

# Iniciar servidor PHP sin router
php -S localhost:$PORT
