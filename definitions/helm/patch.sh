#!/bin/zsh
REGISTRY_NAME=acrlhggixiservicesp.azurecr.io
RESOUCRE_GROUP=RG_LHG_ACR_01_P
SOURCE_REGISTRY=k8s.gcr.io
CONTROLLER_IMAGE=ingress-nginx/controller
VERSION=4.2.3
CONTROLLER_TAG=v1.3.0
PATCH_IMAGE=ingress-nginx/kube-webhook-certgen
PATCH_TAG=v1.3.0
DEFAULTBACKEND_IMAGE=defaultbackend-amd64
DEFAULTBACKEND_TAG=1.5
CONFIG=/definitions/helm/ingress-config.yaml

az acr import --name $REGISTRY_NAME -g $RESOUCRE_GROUP --source $SOURCE_REGISTRY/$CONTROLLER_IMAGE:$CONTROLLER_TAG --image $CONTROLLER_IMAGE:$CONTROLLER_TAG --force
az acr import --name $REGISTRY_NAME -g $RESOUCRE_GROUP --source $SOURCE_REGISTRY/$PATCH_IMAGE:$PATCH_TAG --image $PATCH_IMAGE:$PATCH_TAG --force
az acr import --name $REGISTRY_NAME -g $RESOUCRE_GROUP --source $SOURCE_REGISTRY/$DEFAULTBACKEND_IMAGE:$DEFAULTBACKEND_TAG --image $DEFAULTBACKEND_IMAGE:$DEFAULTBACKEND_TAG --force

helm repo update

helm upgrade nginx-ingress ingress-nginx/ingress-nginx \
    --force \
    --version $VERSION \
    --namespace ingress-basic \
    --set controller.replicaCount=2 \
    --set controller.nodeSelector."kubernetes\.io/os"=linux \
    --set controller.image.registry=$REGISTRY_NAME \
    --set controller.image.image=$CONTROLLER_IMAGE \
    --set controller.image.tag=$CONTROLLER_TAG \
    --set controller.image.digest="" \
    --set controller.admissionWebhooks.patch.nodeSelector."kubernetes\.io/os"=linux \
    --set controller.service.annotations."service\.beta\.kubernetes\.io/azure-load-balancer-health-probe-request-path"=/healthz \
    --set controller.admissionWebhooks.patch.image.registry=$REGISTRY_NAME \
    --set controller.admissionWebhooks.patch.image.image=$PATCH_IMAGE \
    --set controller.admissionWebhooks.patch.image.tag=$PATCH_TAG \
    --set controller.admissionWebhooks.patch.image.digest="" \
    --set controller.resources.requests.cpu="100m" \
    --set controller.resources.limits.cpu="200m" \
    --set controller.resources.requests.memory="90Mi" \
    --set controller.resources.limits.memory="180Mi" \
    --set defaultBackend.nodeSelector."kubernetes\.io/os"=linux \
    --set defaultBackend.image.registry=$REGISTRY_NAME \
    --set defaultBackend.image.image=$DEFAULTBACKEND_IMAGE \
    --set defaultBackend.image.tag=$DEFAULTBACKEND_TAG \
    --set defaultBackend.image.digest="" \
    --timeout 5m00s \
    -f $CONFIG