#!/bin/bash

RELEASE_NAME="mailhog"
NAMESPACE="oneup-stage-mailhog"
CHART="codecentric/mailhog"
REPOSITORY="https://codecentric.github.io/helm-charts"
VALUES_FILE="values.yaml"

# Add the codecentric Helm repository
helm repo add codecentric "$REPOSITORY"

# Update your local Helm chart repository cache
helm repo update

# Check if the Mailhog release is already deployed
DEPLOYED=$(helm list -n "$NAMESPACE" --short | grep "^$RELEASE_NAME$" || true)

if [ -n "$DEPLOYED" ]; then
    echo "'$RELEASE_NAME' is already deployed; upgrading using '$VALUES_FILE' values file."
    helm upgrade --install "$RELEASE_NAME" $CHART -f "$VALUES_FILE" --namespace "$NAMESPACE" --create-namespace
else
    echo "'$RELEASE_NAME' is not deployed; installing using '$VALUES_FILE' values file."
    helm install "$RELEASE_NAME" $CHART -f "$VALUES_FILE" -n "$NAMESPACE"  --create-namespace
    helm upgrade --install "$RELEASE_NAME" $CHART -f "$VALUES_FILE" --namespace "$NAMESPACE" --create-namespace
fi