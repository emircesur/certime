If PHP OpenSSL is unavailable or key generation fails, generate keys manually using the system `openssl` CLI.

Commands (run from project root):

```bash
mkdir -p data/keys
openssl genrsa -out data/keys/pdf_signer.key 2048
openssl req -new -x509 -key data/keys/pdf_signer.key -out data/keys/pdf_signer.crt -days 365 -subj "/CN=CertiMe PDF Signer"
chmod 600 data/keys/pdf_signer.key
chmod 644 data/keys/pdf_signer.crt
```

On Windows (PowerShell), use:

```powershell
mkdir data\keys -Force
openssl genrsa -out data\keys\pdf_signer.key 2048
openssl req -new -x509 -key data\keys\pdf_signer.key -out data\keys\pdf_signer.crt -days 365 -subj "/CN=CertiMe PDF Signer"
icacls data\keys\pdf_signer.key /inheritance:r /grant:r "$($env:USERNAME):R"
```

After creating the files, open the admin keys page (`/admin/keys`) and verify the files are present. Then open a credential and click "Download Signed PDF".
