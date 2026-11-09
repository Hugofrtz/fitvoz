# Verificação da entrega

Executada em 05/11/2026.

## Testes concluídos

- Sintaxe do JavaScript validada com Node.
- Sintaxe dos arquivos PHP validada com PHP 8.3.
- Importação de `banco.sql` em banco MariaDB 10.11 isolado.
- Quatro unidades carregadas e ranking inicialmente vazio.
- Cadastro de duas contas e bloqueio de e-mail duplicado.
- Login correto, rejeição de senha incorreta e logout.
- Rejeição de avaliação sem autenticação e token CSRF inválido.
- Rejeição de nota fora de 1–5 e de personal de outra unidade.
- Gravação e leitura de avaliação, comentário, sugestão e nota de personal.
- Nome público do autor sem e-mail na resposta das avaliações.
- Média 4,0 após notas 5 e 3; média 3,5 após editar a nota 5 para 4.
- Edição sem duplicar a avaliação do usuário na unidade.
- Mudança da ordem do ranking conforme novas notas.
- Tentativa de exclusão por outro usuário preservando a avaliação original.
- Exclusão pelo próprio autor e recálculo do ranking.
- Persistência das avaliações depois de logout e novo login.
- Conferência visual da página inicial no navegador.
- Conferência do formulário com dados de teste: seleção de academia/unidade, cinco estrelas e exibição condicional da nota do personal.

## Limites da verificação

A API e o SQL foram testados juntos em PHP + MariaDB, fora da máquina do usuário. A inspeção visual utilizou respostas de teste para o catálogo e formulário. Não foi executado um teste completo de ponta a ponta no XAMPP do usuário nem uma inspeção em celular físico. O layout inclui regras responsivas. A conexão local depende dos passos no README e das credenciais reais do XAMPP.

As contas e avaliações usadas nos testes NÃO fazem parte do arquivo SQL entregue. Nenhum simulador de banco é incluído no projeto final: a interface entregue utiliza `api.php` e MySQL/MariaDB.
