# FitVoz — Feedback de atendimento de academias

Projeto acadêmico de Análise e Desenvolvimento de Sistemas. HTML, CSS e JavaScript no navegador; PHP 8+ e MySQL/MariaDB no servidor. Compatível com XAMPP. Sem Composer, npm ou framework para instalar.

## Instalar no Windows / XAMPP

1. Coloque a pasta `fitvoz` em `C:\xampp\htdocs\fitvoz`. Confirme que `index.html` e `api.php` estão diretamente nessa pasta.
2. Abra o XAMPP Control Panel e inicie **Apache** e **MySQL**.
3. Acesse `http://localhost/phpmyadmin` no navegador.
4. Na aba **Importar**, escolha `C:\xampp\htdocs\fitvoz\banco.sql` e clique em **Executar/Importar**. O script cria o banco e as tabelas. Use um banco novo; o script não apaga dados existentes.
5. Copie `config.example.php`, renomeie a cópia para `config.php` e ajuste host, porta, banco, usuário e senha conforme seu XAMPP. Não envie `config.php` ao GitHub.\n6. Abra `http://localhost/fitvoz/`.
7. Clique em **Entrar / Criar conta**, selecione **Criar conta**, informe nome, e-mail de teste e uma senha com pelo menos 8 caracteres.
7. Avalie as unidades. O ranking surge a partir da primeira avaliação e mostra até três unidades que já receberam notas.

**Não abra index.html com duplo clique.** A integração com o banco precisa de PHP no Apache. Não tenho acesso à sua máquina: a importação e a conexão local são realizadas pelos passos acima.

### Configuração do banco

O arquivo `config.php` usa o padrão de desenvolvimento local do XAMPP:

- Host: `127.0.0.1`
- Porta: `3306`
- Banco: `fitvoz`
- Usuário: `root`
- Senha: vazia

Se você já definiu uma senha ou usa outra porta, altere esses campos. Para hospedar de verdade, use usuário de banco com permissões limitadas, HTTPS e credenciais privadas. Esta entrega foi pensada para execução acadêmica local, não inclui hospedagem online.

## Funcionalidades

- Cadastro, login e logout com sessão PHP.
- Nome público do autor em cada avaliação. E-mail mostrado apenas ao próprio usuário, nunca na listagem pública.
- Busca por academia/unidade, filtro de perfil e ordenação.
- Nome da academia e unidade selecionados separadamente.
- Cinco estrelas para nota geral, atendimento e estrutura.
- Escolha de personal da unidade e nota opcional de 1 a 5 estrelas.
- Comentário e sugestão de melhoria.
- Edição e exclusão da própria avaliação; uma avaliação por usuário/unidade.
- Top 3 calculado a partir da média da nota geral; desempate por quantidade de avaliações, nome e unidade.
- Média e quantidade de avaliações de cada personal.
- Layout responsivo, campos rotulados, navegação por teclado, mensagens de erro e estados vazios.
- Persistência real no MySQL: avaliações continuam após fechar o navegador.

## Dados de exemplo

As quatro unidades e os cinco profissionais são fictícios. Não há avaliações pré-preenchidas nem usuários com senha padrão. O ranking começa vazio para não apresentar notas inventadas. Crie contas de teste e avalie unidades para demonstrar o funcionamento. Alterar o filtro de perfil afeta o catálogo; o Top 3 é sempre geral.

Para cadastrar outras academias e profissionais, utilize as tabelas `academias` e `personais` no phpMyAdmin. O projeto não inclui um painel administrativo. O campo `academia_id` do personal deve apontar para a unidade correta.

## Estrutura e relacionamento

- `usuarios`: dados de login, nome, e-mail único e hash da senha.
- `academias`: cada linha representa uma unidade e seu perfil de treino.
- `personais`: profissional vinculado a uma unidade.
- `avaliacoes`: usuário, unidade, notas, comentário, melhoria e personal opcional.

Relações: usuário 1:N avaliações; unidade 1:N avaliações; unidade 1:N personais; personal 1:N avaliações opcionais. A chave única `(usuario_id, academia_id)` impede avaliações duplicadas para a mesma unidade. A API também valida se o personal pertence à unidade.

Fluxo: navegador → `fetch` → `api.php` → PDO → MySQL → JSON → atualização dos cards e ranking.

## Arquivos

- `index.html`: interface e formulários.
- `assets/style.css`: identidade visual e responsividade.
- `assets/app.js`: navegação, requisições e interações.
- `api.php`: autenticação, validações e operações no banco.
- `config.example.php`: modelo de configuração; copie para `config.php` localmente.\n- `config.php`: arquivo local privado, não incluído no repositório.
- `banco.sql`: estrutura e cadastro inicial fictício.
- `.htaccess`: bloqueia acesso HTTP a configuração, SQL e documentação quando usado com Apache/AllowOverride habilitado.

## Segurança implementada e limites

Senhas com `password_hash` / `password_verify`, consultas preparadas PDO, renovação do identificador da sessão no login, cookies HttpOnly/SameSite, token CSRF, validação no servidor, escape de conteúdo no navegador e verificação do proprietário na edição/exclusão. Limitação simples de tentativas de login por sessão. Não inclui verificação de e-mail, recuperação de senha, moderação, limitação distribuída contra abuso ou administração de usuários. Para produção, essas camadas e uma revisão de segurança são necessárias.

## Solução de problemas

- **Mensagem sobre banco:** confira MySQL iniciado, importação de `banco.sql`, porta e senha em `config.php`.
- **404:** confira o nome e o local da pasta em `htdocs`.
- **Apache em porta 8080:** use `http://localhost:8080/fitvoz/`.
- **PHP aparece como texto ou página não conecta:** abra pelo `localhost`, não por `file://` ou servidor estático.
- **Página antiga:** atualize com Ctrl+F5.
- **Sem internet:** a imagem está incluída no projeto e o site continua funcionando. As fontes externas têm fallback para Arial.

## Imagem e fontes

Foto genérica de academia: Unsplash (identificador do arquivo CDN: `photo-1534438327276-14e5300c3a48`). Cópia incluída em `assets/academia.jpg`, origem exata: `https://images.unsplash.com/photo-1534438327276-14e5300c3a48`. Nenhuma marca de academia é associada à foto; a imagem é ilustrativa e se repete nos exemplos. Fontes DM Sans e Manrope via Google Fonts, com fallback local.

## Roteiro para apresentação

1. Mostre o catálogo, a busca e os perfis.
2. Explique as quatro tabelas e suas relações no phpMyAdmin.
3. Crie uma conta e envie uma avaliação com cinco estrelas e uma sugestão.
4. Mostre a linha gravada em `avaliacoes` e a entrada da unidade no ranking.
5. Entre com outra conta e avalie a mesma unidade para demonstrar o cálculo da média.
6. Edite a primeira avaliação e mostre que não foi criada uma duplicata.
7. Exclua uma avaliação e confira o recálculo.


## Publicar no GitHub

1. Crie um repositório no GitHub (recomendo **Private** para o trabalho em grupo, se o professor não exigir público).
2. Use GitHub Desktop ou Git para enviar o conteúdo desta pasta.
3. Confira antes do envio que `config.php` não aparece entre os arquivos a publicar; `.gitignore` já o exclui.
4. Compartilhe o repositório com os integrantes do grupo em **Settings → Collaborators**.

**Importante:** GitHub armazena o código e o histórico do projeto; ele não executa PHP nem hospeda MySQL. Para deixar o site acessível pela internet, vocês ainda precisam de uma hospedagem que aceite PHP e MySQL/MariaDB e configurar nela as credenciais próprias do servidor. Não publique senhas, dados pessoais reais ou avaliações de usuários.
