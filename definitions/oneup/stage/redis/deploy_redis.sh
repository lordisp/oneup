#!/bin/bash
# https://github.com/bitnami/charts/tree/main/bitnami/redis/#installing-the-chart

# Variables
RELEASE_NAME="redis"
NAMESPACE="oneup-stage-redis"
CHART="bitnami/redis"
VALUES_FILE="redis-values.yaml"

# Add the Bitnami Helm repository
helm repo add bitnami https://charts.bitnami.com/bitnami

# Update your local Helm chart repository cache
helm repo update

# Check if the Redis release is already deployed
DEPLOYED=$(helm list -n "$NAMESPACE" --short | grep "^$RELEASE_NAME$" || true)

if [ -n "$DEPLOYED" ]; then
    echo "'$RELEASE_NAME' is already deployed; upgrading using '$VALUES_FILE' values file."
    helm upgrade --install "$RELEASE_NAME" $CHART -f "$VALUES_FILE" --namespace "$NAMESPACE" --create-namespace
else
    echo "'$RELEASE_NAME' is not deployed; installing using '$VALUES_FILE' values file."
    helm install "$RELEASE_NAME" $CHART -f "$VALUES_FILE" -n "$NAMESPACE"  --create-namespace
    helm upgrade --install "$RELEASE_NAME" $CHART -f "$VALUES_FILE" --namespace "$NAMESPACE" --create-namespace
fi