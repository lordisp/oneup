# Deploy and Maintain PHP-MyAdmin

## SSL Certificate

**The current certificate expires on** `<span style="color:red">`**14.06.2023!**

### 1. Request new Certificate from LH PKI

LH Certificates for Webservers can be ordered over the [Enrollment Service for SSL certificates](https://sas.pki.fra.dlh.de/mcert/order).

Select the `Server` option and follow the instructions. At the end of the process, you'll receive a p12 certificate. The password to decrypt the certificate will be sent to the email address you provided during the request process.

In order to create a certificate secret in kubernetes, you have to create .key and .crt from the PKCS12.

#### 1.a. Generate .key and .crt from PKCS12 file

```bash
openssl pkcs12 -in oneup.az.lhgroup.de.p12 -nocerts -out filename.key
openssl pkcs12 -in oneup.az.lhgroup.de.p12 -clcerts -nokeys -out filename.crt
```

> You'll be prompted to enter a passphrase for the private key. For the creation of the k8s secret, you need to decode the private key in the next step

#### 1.b. Decode the private key

```bash
openssl rsa -in .\pma.key -out decrypted.key
```

#### 1.c Verify the Certificate (optional)

```bash
openssl x509 -in filename.crt -text -noout
```

> if you get something like `Expecting: TRUSTED CERTIFICATE`, repeat step **1.a to 1.c**

You should have now four files:

```
filename.p12
filename.key
filename.crt
decrypted.key
```

For the k8s secret, you'll need `filename.crt` and `decrypted.key` to create a tls-secret.

#### 1.e Create a TLS Secret

kubectl create secret tls --cert "filename.crt" oneup-tls-secret --key "unencryed.key"
## Deploy Ingress Controller

Adjust the parameters in `\oneup\definitions\helm\pma_ingress.ps1` and `\oneup\definitions\helm\pma_ingress.yaml` to your needs. Connect to azure powershell and azure cli and run the script.

```powershell
.\pma_ingress.ps1
```
