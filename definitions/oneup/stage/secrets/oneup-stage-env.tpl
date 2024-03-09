apiVersion: v1
kind: Secret
metadata:
  name: oneup-env
  namespace: oneup-stage
  labels:
    app: oneup
data:
  ADMIN: <base64-encoded-password>
  APP_KEY: <base64-encoded-password>
  DB_PASSWORD: <base64-encoded-password>
  HUB_RESOURCE_GROUP: <base64-encoded-name>
  HUB_SUBSCRIPTION: <base64-encoded-name>
  MAIL_PASSWORD: <base64-encoded-password>
  REDIS_PASSWORD: <base64-encoded-password>
  PASSPORT_PERSONAL_ACCESS_CLIENT_ID: <base64-encoded-id>
  PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET: <base64-encoded-secret>
  SNOW_CLIENT_SECRET: <base64-encoded-secret>