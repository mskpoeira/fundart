# Fundart

Atualização do site da Fundart.

## Publicação

- Produção: https://fundart.mskpoeira.com.br
- Repositório: `mskpoeira/fundart`
- Branch de produção: `main`
- VPS: Hostinger `77.37.40.81`
- Aplicação: `/opt/fundart`
- Container: `fundart-web-prod`
- Porta local: `127.0.0.1:63250`
- Proxy/HTTPS: Caddy do host
- Deploy automático: `.github/workflows/deploy.yml`

Todo push na branch `main` que altere o site ou a infraestrutura dispara validação e deploy automático.

### Segredo necessário no GitHub Actions

O repositório precisa do secret `SSH_PRIVATE_KEY`, contendo a chave privada autorizada para o usuário `root` do VPS. A chave nunca deve ser commitada no repositório.
