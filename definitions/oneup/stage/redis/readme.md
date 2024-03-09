# Redis Cluster Deployment and Maintenance Guide

This document outlines the procedures for deploying a high-availability Redis cluster on Kubernetes, including initial setup, upgrade processes, password renewal, and TLS certificate management.

## Prerequisites

- Kubernetes cluster
- `kubectl` configured to communicate with your cluster
- `helm` installed on your local machine

## Deployment Steps

### 1. Creating the Namespace

First, you need to create a namespace for your Redis deployment. Define this in a file named `redis-namespace.yaml`:

```yaml
apiVersion: v1
kind: Namespace
metadata:
  name: oneup-stage-redis
```

Apply it using `kubectl`:

```shell
kubectl apply -f redis-namespace.yaml
```

### 2. Setting up the Redis Password Secret

To create a secret for the Redis password, follow these steps:

1. Base64 encode your Redis password:

   ```shell
   echo -n 'your-redis-password' | base64
   ```

2. Populate a `redis-secret.yaml` file with the encoded password:

   ```yaml
   apiVersion: v1
   kind: Secret
   metadata:
     name: redis-secret
     namespace: oneup-stage-redis
   type: Opaque
   data:
     redis-password: <base64-encoded-password>
   ```

3. Apply the secret using `kubectl`:

   ```shell
   kubectl apply -f redis-secret.yaml
   ```

### 3. Deploying or Upgrading Redis

To deploy or upgrade Redis, you will use the `deploy_redis.sh` script. This script intelligently determines whether to perform a `helm install` or `helm upgrade` based on if Redis is already deployed in the specified namespace.

Ensure your `redis-values.yaml` file is correctly configured for your deployment needs, including TLS settings, node affinity, and resource allocations.

Execute the script:

```shell
./deploy_redis.sh
```

## Upgrade Process

To upgrade your Redis deployment, update your `redis-values.yaml` with the new configurations or Redis chart version. Then, run the `deploy_redis.sh` script again. The script will automatically handle the upgrade process:

```shell
./deploy_redis.sh
```

## Renewing the Redis Password

To change the Redis password:

1. Update the password in the `redis-secret.yaml` file and apply the changes:

   ```shell
   kubectl apply -f redis-secret.yaml
   ```

2. Run the `deploy_redis.sh` script to restart the Redis pods and apply the new password:

   ```shell
   ./deploy_redis.sh
   ```

## Maintaining TLS Certificates

### Generating and applying new TLS certificates for Redis

Generating new TLS certificates involves creating a Certificate Authority (CA), and then using it to create and sign the server and client certificates. Below is a detailed process and a script that automates the generation of these certificates for Redis.

### Process Overview

1. **Generate a Certificate Authority (CA):**
   - Create a CA private key.
   - Create a CA certificate using the CA private key.

2. **Generate a Server Certificate:**
   - Create a private key for the server.
   - Create a Certificate Signing Request (CSR) for the server.
   - Sign the server CSR with the CA certificate to create the server certificate.

3. **Generate a Client Certificate (Optional):**
   - Create a private key for the client.
   - Create a CSR for the client.
   - Sign the client CSR with the CA certificate to create the client certificate.

### Prerequisites

- OpenSSL installed on your machine.

### Script: `generate_tls_certificates.sh`

### Instructions

1. Replace the placeholder values (`YourState`, `YourCity`, `YourOrganization`, etc.) with actual details relevant to your organization and Redis setup.
2. Save the script as `generate_tls_certificates.sh`.
3. Make the script executable:

   ```shell
   chmod +x generate_tls_certificates.sh
   ```

4. Run the script:

   ```bash
   ./generate_tls_certificates.sh
   ```

This script will create the necessary certificates and a Kubernetes secret named `redis-tls` in the `oneup-stage-redis` namespace. Update your Redis deployment to use this secret for TLS.

### Note

Remember to securely store the generated private keys (`ca.key`, `redis-server.key`, `redis-client.key`) and certificates. The CA certificate (`ca.crt`) will be needed by Redis clients to establish trust with the server.

To apply the new certificates, run the `deploy_redis.sh` script:

   ```shell
   ./deploy_redis.sh
   ```

## Conclusion

This guide provides the necessary steps to deploy, upgrade, and maintain a secure and efficient Redis environment on Kubernetes. Regularly review your deployment configurations and maintain your secrets and certificates to ensure ongoing security and performance.