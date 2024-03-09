apiVersion: v1
kind: Secret
metadata:
  name: redis-secret
  namespace: oneup-stage-redis
type: Opaque
data:
  redis-password: <base64-encoded-password>