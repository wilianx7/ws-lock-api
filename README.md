# ws-lock-api

Essas instruções farão com que você tenha uma cópia do projeto em execução na sua máquina local para fins de desenvolvimento e teste.

## Pré-requisitos

* PHP >= 7.4 [link](https://www.php.net/downloads.php);
* Composer [link](https://getcomposer.org/download/);
* MySQL [link](https://www.mysql.com/downloads/);

## Configurando o projeto

Todos comandos citados a seguir, devem ser executados na linha de comando da sua máquina. Portanto, navegue até a pasta do projeto para poder executar os comandos abaixo especificados:

```php
composer install
```

Com um paralelo ao JavaScript, esse comando funciona como o NPM, o "composer install" vai instalar todas as dependências do Laravel necessárias para executar o projeto em sua máquina

A seguir, execute o seguinte comando:

**Mac/Linux:**

```php
cp .env.example .env 
```

**Windows:**

```php
copy .env.example .env 
```

Este comando, vai criar um outro arquivo de nome '.env'. É esse arquivo que conterá as variaveis de ambiente do projeto.
Para mais informações clique [aqui](https://laravel.com/docs/8.x/configuration#environment-configuration)

A seguir, execute o seguinte comando:

```php
php artisan key:generate
```
Este comando vai gerar uma chave no arquivo '.env' que permitira rodar o projeto.

O comando abaixo, gera um token no arquivo .env que será utilizado como key pair com o Token de cada usuário autenticado.
```php
php artisan jwt:secret
```

Depois de executar os comandos acima, entre no arquivo .env e faça a sua configuração com os dados do banco (DB_DATABASE, DB_USERNAME, DB_PASSWORD) e outros.

Após configurar as variáveis no arquivo .env, execute o seguinte comando para salvar as alterações:

```php
php artisan config:cache
```

## Configurando o banco de dados

**Para prosseguir com os próximos passos, você deverá ter em sua máquina um schema no banco de dados com o nome especificado no .env (DB_DATABASE).**

Uma vez que o .env está configurado e salvo com o comando de cache, execute o seguinte comando:

```php
php artisan migrate --seed
```

Esse comando irá criar as tabelas do projeto em seu banco de dados especificado no .env e um usuário padrão com os dados:
**Login:** admin
**Senha:** p#mB2%f;<cnc(Vx:

## Executando a API

Para rodar a API e testar o aplicativo localmente, você precisará obter o endereço IP da sua máquina e liberar o acesso externo da porta 9000.

Após isso, execute o seguinte comando para iniciar o servidor:

```php
php artisan serve --host=MEU_IP (substituir pelo seu IP) --port=9000 
```

## Executando PhpUnit

Para rodar os testes unitários do PhpUnit, primeiramente você deve parar a execução do servidor. Após isso, execute o comando:

```php
php artisan dev:prepare-env
```

Esse comando irá preparar o ambiente de testes da API.

Em seguida, para iniciar os testes execute:

```php
php artisan test
```

Com isso, os testes serão realizados e os resultados exibidos no console.

**Para voltar a utilizar a API como servidor, você deverá rodar o comando:**

```php
php artisan dev:prepare-env
```

# Servidor MQTT

Para comunicação entre a API e os microcontroladores (ESP8266) instalados em cada fechadura, se faz necessária a utilização de um servidor MQTT. Portanto, a seguir serão descritos os passos para instalação e configuração do mesmo. [link](https://mosquitto.org/)

## Linux

No Linux, o mosquitto fica instalado no diretório /etc/mosquitto. Nesse diretório, há um arquivo chamado "acl" que contém todos os usuários permitidos no servidor, bem como as regras para postagem e subscrição nos tópicos. Nessa etapa, você pode configurar seu MQTT server como preferir ou utilizar os seguintes arquivos pré configurados:

[mosquitto-configuration.zip](https://github.com/wilianx7/ws-lock-api/files/6290848/mosquitto-configuration.zip)

**Faça a extração dos arquivos e cole no diretório /etc/mosquitto**

Após esse procedimento, execute o seguinte comando:

```
sudo systemctl restart mosquitto
```

A seguir, você precisará informar as configurações do MQTT na sua API Laravel. Para isso, navegue até o arquivo .env da API e substitua as seguintes chaves:

![image](https://user-images.githubusercontent.com/42422976/114285960-6eec7f80-9a31-11eb-916c-8d5556277ca5.png)

No caso de ter utilizado os arquivos pré configurados acima, basta alterar o MQTT_HOST para o IP da sua máquina.

**Lembre-se:** após cada alteração feita no .env, o comando ```php artisan config:cache``` deve ser executado e o servidor reiniciado.
