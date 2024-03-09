#!/bin/bash

# Certificate details
COUNTRY="DE"
STATE="Germany"
LOCALITY="Frankfurt am Main"
ORGANIZATION="Deutsche Lufthansa AG"
ORG_UNIT="FRA GI/TI" # Optional: Organizational Unit
EMAIL="cloud.giti@dlh.de" # Optional: Email Address

# Redis-specific details
NAMESPACE="oneup-stage-redis"
APP_NAMESPACE="oneup-stage"
SECRET_NAME="redis-tls"
SERVER_CN="redis-server" # Server Common Name
CLIENT_CN="redis-client" # Client Common Name (optional)

# Delete existing certificates in the tls directory
rm tls/*.crt tls/*.key tls/*.csr tls/*.srl 2>/dev/null
# Delete existing Kubernetes Secret
kubectl delete secret $SECRET_NAME -n $NAMESPACE 2>/dev/null

# Generate CA
openssl genrsa -out tls/ca.key 4096
openssl req -new -x509 -key tls/ca.key -sha256 \
  -subj "/C=${COUNTRY}/ST=${STATE}/L=${LOCALITY}/O=${ORGANIZATION}/OU=${ORG_UNIT}/CN=RedisCA/emailAddress=${EMAIL}" \
  -days 1024 -out tls/ca.crt

# Generate Server Certificate
openssl genrsa -out tls/redis-server.key 4096
openssl req -new -key tls/redis-server.key \
  -subj "/C=${COUNTRY}/ST=${STATE}/L=${LOCALITY}/O=${ORGANIZATION}/OU=${ORG_UNIT}/CN=${SERVER_CN}/emailAddress=${EMAIL}" \
  -out tls/redis-server.csr
openssl x509 -req -in tls/redis-server.csr -CA tls/ca.crt -CAkey tls/ca.key -CAcreateserial \
  -out tls/redis-server.crt -days 500 -sha256

# Generate Client Certificate (Optional)
openssl genrsa -out tls/redis-client.key 4096
openssl req -new -key tls/redis-client.key \
  -subj "/C=${COUNTRY}/ST=${STATE}/L=${LOCALITY}/O=${ORGANIZATION}/OU=${ORG_UNIT}/CN=${CLIENT_CN}/emailAddress=${EMAIL}" \
  -out tls/redis-client.csr
openssl x509 -req -in tls/redis-client.csr -CA tls/ca.crt -CAkey tls/ca.key -CAcreateserial \
  -out tls/redis-client.crt -days 500 -sha256

# Verify Certificates
openssl verify -CAfile tls/ca.crt tls/redis-server.crt
openssl verify -CAfile tls/ca.crt tls/redis-client.crt

# Create Kubernetes Secret with TLS Certificates
kubectl create secret generic $SECRET_NAME \
  --from-file=tls/redis-server.crt \
  --from-file=tls/redis-server.key \
  --from-file=tls/ca.crt \
  -n $NAMESPACE --dry-run=client -o yaml | kubectl apply -f -

# Create Kubernetes Secret with TLS Certificates for the Application
kubectl create secret generic $SECRET_NAME \
  --from-file=tls/redis-client.crt \
  --from-file=tls/redis-client.key \
  --from-file=tls/ca.crt \
  -n $APP_NAMESPACE --dry-run=client -o yaml | kubectl apply -f -


