# Rede Social PHP - Protótipo

## Como usar
1. Importe `migrations/schema.sql` no seu MySQL (por exemplo via phpMyAdmin ou `mysql -u root -p < migrations/schema.sql`).
2. Coloque a pasta `rede-social-php` no diretório público do seu servidor (htdocs ou www).
3. Ajuste `config/config.php` com credenciais do banco.
4. Torne a pasta `public/uploads/` gravável pelo servidor web.
5. Acesse `http://localhost/rede-social-php/public/register.php` e crie um usuário.

## Notas
- O esquema usa colunas geradas (STORED) para a tabela `conversations` (requer MySQL 5.7+).
- Este projeto é um protótipo didático — não pronto para produção sem melhorias de segurança.
