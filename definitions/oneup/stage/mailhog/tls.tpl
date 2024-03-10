apiVersion: v1
kind: Secret
metadata:
  name: oneup-tls-2024
  namespace: oneup-stage-mailhog
type: kubernetes.io/tls
data:
  tls.crt: <base64 encoded cert>
  tls.key: <base64 encoded key>