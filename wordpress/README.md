# FUNDART — versão WordPress

Este diretório contém o tema WordPress do portal reestruturado, preservando o layout visual de referência do projeto e preparado para edição nativa de conteúdos.

## Estado da migração

**Tema preparado no repositório; ainda não instalado nem ativado no servidor.** O projeto principal continua servido por Nginx enquanto não houver uma migração WordPress aprovada e testada. Não substituir automaticamente o Dockerfile nem o compose de produção, evitando perda de conteúdo e indisponibilidade.

## Implantação segura

1. Obter backup autorizado e atualizado do WordPress de fundart.com.br: banco MySQL/MariaDB, `wp-content/uploads`, páginas, posts, taxonomias, menus, URLs internas e usuários, sem publicar senhas no GitHub.
2. Criar ambiente WordPress isolado de homologação com PHP e MySQL/MariaDB; banco e uploads em volumes persistentes com backup e HTTPS.
3. Copiar `wordpress/themes/fundart` para `wp-content/themes/fundart`; instalar/ativar e configurar logotipo oficial no painel.
4. Migrar páginas, posts, arquivos, categorias e endereços antigos para os caminhos correspondentes; revisar links internos, mídias, permalinks e acessibilidade. Não inventar documentos ou notícias.
5. Configurar o menu em **Aparência → Menus**; preencher páginas de Ouvidoria Setorial, Conselho, Editais, Oficinas e Agenda.
6. Confirmar URLs do Decreto nº 9.201/2026, Fala.BR, Informa.BR, Webmail, portal de transparência e redes sociais. Todos os links usam a mesma aba.
7. Testar edições e publicações no painel, segurança, atualizações, backup/restore, responsividade, menu, links e busca antes do corte de produção.
8. Mudar o deploy e proxy somente após validação expressa da equipe responsável, com possibilidade de rollback.

## Funcionalidades do tema

- Home com hero configurável em **Aparência → Personalizar**, 7 atalhos, eventos e notícias WordPress reais;
- Menu hierárquico e submenu editáveis via painel;
- Custom Post Types: Eventos culturais, Oficinas, Editais e Conselhos;
- Taxonomia de 7 categorias de edital cadastrada na ativação;
- Ouvidoria Setorial: template **Ouvidoria Setorial**, com aviso de competência e canais oficiais;
- Acessibilidade A+/A−, alto contraste, menu responsivo;
- Links e formulários na mesma aba;
- Busca nativa WordPress, posts publicados, páginas editáveis.

## Integrações pendentes

Importação do banco e uploads originais, mapeamento integral das subpáginas e taxonomias, verificação dos endereços externos e publicação no novo domínio. Tais etapas exigem acesso administrativo autorizado ou exportação/backup original e não são concluídas pela simples criação deste tema.
