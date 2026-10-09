# WordPress em paralelo ao site atual

Este stack não altera o proxy público antes da validação. Instala WordPress e MariaDB na rede exclusiva `fundart_wp_internal`, com porta local `127.0.0.1:63251`. O site estático permanece na porta `63250`.

## Procedimento

1. Criar em `/opt/fundart/.env.wp` variáveis `FUNDART_DB_PASSWORD` e `FUNDART_DB_ROOT_PASSWORD` com valores aleatórios. Não salvar senhas no Git.
2. Executar `docker compose --env-file .env.wp -f docker-compose.wordpress.yml up -d`.
3. Confirmar saúde do MariaDB e WordPress e resposta local em `127.0.0.1:63251/wp-login.php`.
4. Após aprovação: definir login e e-mail do administrador, concluir instalação via WP-CLI ou instalador protegido, criar conteúdo editorial e testar.
5. Salvar backup do banco e `wp-content/uploads`, verificar links e menus, só então mudar o Caddy para a porta `63251`, com reversão para `63250`.
6. Configurar HTTPS, backups agendados e autenticação forte. Não divulgar credenciais no Git.

## Dados
Volumes Docker separados `fundart-wordpress_fundart_db` e `fundart-wordpress_fundart_wp`. Não usar `docker compose down -v` em produção.

## Atenção
Uma instalação WordPress nova não contém automaticamente o acervo privado do domínio antigo. O conteúdo publicamente visível deverá ser importado com conferência editorial; não declarar migração completa antes da auditoria.
