## Documentação

Projeto desenvolvido com Lumen em ambiente Local.

* Documentação
* Código limpo e organizado (nomenclatura, etc)
* Padrões (PSRs, design patterns, SOLID)
* Modelagem de Dados
* Manutenibilidade do Código
* Tratamento de erros
* Cuidado com itens de segurança
* Arquitetura
* Service e repository
* Testes unitários
* Testes de Aceitação


### Instalação e configuração

Clone o projeto

`git clone https://github.com/DeyzianeCastelo/Desafio-Back-end.git`

Execute 

`COPY .env.exemple .env no Windows`

Configure o .env de acordo com o seu ambiente Local

Dentro da pasta do projeto rode

` php artisan update`

Crie as migrations com o comando

` php artisan migrate`

Suba o servidor

`  php -S localhost:8000 -t public`


### Para testar rode o seguinte comando no terminal

` vendor/bin/phpunit`
