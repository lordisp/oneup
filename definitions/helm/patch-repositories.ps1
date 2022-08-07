$Subscription = "LHG_SM_GIXI_P"
$REGISTRY_NAME = "acrlhggixiservicesp.azurecr.io"
$RESOUCRE_GROUP = "RG_LHG_ACR_01_P"
$import = $false
$login = $false

$SOURCE_REGISTRY = "k8s.gcr.io"
$ControllerImage = "ingress-nginx/controller"
$ControllerTag = "v1.2.1"
$PatchImage = "ingress-nginx/kube-webhook-certgen"
$PatchTag = "v1.1.1"
$DefaultBackendImage = "defaultbackend-amd64"
$DefaultBackendTag = "1.5"

if ($login)
{
    az account set -s $Subscription
    az acr login --name $REGISTRY_NAME
}
if ($import)
{
    az acr import --name $REGISTRY_NAME -g $RESOUCRE_GROUP --source "$( $SOURCE_REGISTRY )/$( $ControllerImage ):$( $ControllerTag )" --image "$( $ControllerImage ):$( $ControllerTag )" --force
    az acr import --name $REGISTRY_NAME -g $RESOUCRE_GROUP --source "$( $SOURCE_REGISTRY )/$( $PatchImage ):$( $PatchTag )" --image "$( $PatchImage ):$( $PatchTag )" --force
    az acr import --name $REGISTRY_NAME -g $RESOUCRE_GROUP --source "$( $SOURCE_REGISTRY )/$( $DefaultBackendImage ):$( $DefaultBackendTag )" --image "$( $DefaultBackendImage ):$( $DefaultBackendTag )" --force

    # Add the ingress-nginx repository
    # helm repo add ingress-nginx https://kubernetes.github.io/ingress-nginx
}

$SERVICE_PATH = "$( $PSScriptRoot )\ingress-config.yaml"

# Use Helm to deploy an NGINX ingress controller
helm install nginx-ingress ingress-nginx/ingress-nginx `
    -f $SERVICE_PATH `
    --namespace ingress-basic `
    --create-namespace `
    --set controller.replicaCount = 2 `
    --set controller.nodeSelector."kubernetes\.io/os" = linux `
    --set controller.image.registry = $REGISTRY_NAME `
    --set controller.image.image = $ControllerImage `
    --set controller.image.tag = $ControllerTag `
    --set controller.image.digest = "" `
    --set controller.admissionWebhooks.patch.nodeSelector."kubernetes\.io/os" = linux `
    --set controller.service.annotations."service\.beta\.kubernetes\.io/azure-load-balancer-health-probe-request-path" = /healthz `
    --set controller.admissionWebhooks.patch.image.registry = $REGISTRY_NAME `
    --set controller.admissionWebhooks.patch.image.image = $PatchImage `
    --set controller.admissionWebhooks.patch.image.tag = $PatchTag `
    --set controller.admissionWebhooks.patch.image.digest = "" `
    --set controller.resources.requests.cpu = "100m" `
    --set controller.resources.limits.cpu = "200m" `
    --set controller.resources.requests.memory = "90Mi" `
    --set controller.resources.limits.memory = "180Mi" `
    --set defaultBackend.nodeSelector."kubernetes\.io/os" = linux `
    --set defaultBackend.image.registry = $REGISTRY_NAME `
    --set defaultBackend.image.image = $DefaultBackendImage `
    --set defaultBackend.image.tag = $DefaultBackendTag `
    --set defaultBackend.image.digest = ""
