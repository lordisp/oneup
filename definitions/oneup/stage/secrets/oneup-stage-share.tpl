apiVersion: v1
kind: Secret
metadata:
  name: azure-file-secret
  namespace: oneup-stage
type: Opaque
data:
  azurestorageaccountname: <base64-encoded-name>
  azurestorageaccountkey: <base64-encoded-key>