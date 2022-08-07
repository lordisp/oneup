$Subscription = "LHG_SM_GIXI_P"
$REGISTRY_NAME = "acrlhggixiservicesp.azurecr.io"
$login = $false

$ControllerImage = "ingress-nginx/controller"
$ControllerTag = "v1.2.1"
$PatchImage = "ingress-nginx/kube-webhook-certgen"
$PatchTag = "v1.1.1"
$DefaultBackendImage = "defaultbackend-amd64"
$DefaultBackendTag = "1.5"

if($login)
{
    az account set -s $Subscription
    az acr login --name $REGISTRY_NAME
}

$SERVICE_PATH = "$( $PSScriptRoot )\ingress-config.yaml"

helm repo update
helm upgrade nginx-ingress ingress-nginx/ingress-nginx `
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
