apiVersion: v1
kind: Secret
metadata:
  name: oneup-tls-2024
  namespace: oneup-stage
type: kubernetes.io/tls
data:
  tls.crt: <base64-encoded-crt>
  tls.key: <base64-encoded-key>
  oauth-private: <base64-encoded-key>
  oauth-public: <base64-encoded-key>
  mysql-attr-ssl-ca: <base64-encoded-ca>
  mysql-attr-ssl-key: <base64-encoded-key>
  mysql-flex-ssl-ca: <base64-encoded-ca>