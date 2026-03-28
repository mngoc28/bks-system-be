#!/bin/bash

openssl req -nodes -new -x509 -days 365 \
    -keyout ssl/server.key \
    -out ssl/server.crt \
    -subj "/C=VN/ST=DN/L=DN/O=ST/OU=IT/CN=mrb-app.local" \
    -addext "subjectAltName=DNS:mrb-app.local" \