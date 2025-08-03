# 🤖 Telegram Bot (Laravel + Sail)

Este projeto é um **bot do Telegram** desenvolvido em Laravel 12 utilizando **Docker (Sail)** e **DevContainer**.  
Ele simula um fluxo de entrada em uma comunidade VIP, integrando diretamente com a **Telegram Bot API**

---

## ✅ Pré-requisitos

Antes de iniciar o projeto, certifique-se de ter instalado em sua máquina:

- [Docker](https://www.docker.com/get-started) (necessário para rodar o Laravel Sail)

## 📦 Instalação

### 1. Clonar o projeto
```bash
git clone https://github.com/walisonvini/telegram-bot.git
cd telegram-bot
```

### 2. Copiar variáveis de ambiente
```bash
cp .env.example .env
```

### 3. Instalar dependências

Não é necessário ter **PHP** instalado localmente, pois o comando roda dentro de um container Docker.

```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v $(pwd):/var/www/html \
    -w /var/www/html \
    laravelsail/php84-composer \
    composer install
```

### 4. Subir os containers
```bash
./vendor/bin/sail up -d
```

## 5. Executar as migrations
```bash
./vendor/bin/sail artisan migrate
```

## 6. Gerar a chave da aplicação
```bash
./vendor/bin/sail artisan key:generate
```

## 🚀 Configuração do Webhook
Para expor sua aplicação local ao Telegram, utilize o comando do Sail:
```bash
./vendor/bin/sail share
```

⚠️ **Nota:** Caso o `sail share` não funcione corretamente com **HTTPS**, você pode utilizar a integração com o **ngrok** já incluída no projeto.  

1. Crie uma conta gratuita no [ngrok](https://dashboard.ngrok.com/login).  
2. Gere seu **Auth Token** no painel do ngrok.  
3. Adicione o token no arquivo `.env`:

```env
NGROK_AUTHTOKEN=seu-token-aqui
```

4. Reinicie os containers para aplicar a nova configuração:

```bash
./vendor/bin/sail down
./vendor/bin/sail up -d
```

5. Informe a URL HTTPS gerada pelo `sail share` ou pelo **ngrok** como endereço público do seu bot.
```env
TELEGRAM_WEBHOOK_URL=seu-token-aqui
```

## 🤖 Configuração do Telegram Bot

Para integrar seu projeto com o Telegram:

1. Crie um bot através do [@BotFather](https://t.me/BotFather) no Telegram.  
2. Copie o **token** fornecido pelo BotFather.  
3. Adicione o token no seu arquivo `.env`:

```env
TELEGRAM_BOT_TOKEN=seu-token-aqui
```

### Ativar o Webhook no Telegram

Após configurar a URL pública (via `sail share` ou **ngrok**), ative o webhook no Telegram executando o método `setWebhook` do serviço `TelegramServices`.

No shell, entre no Tinker do Laravel:

```bash
./vendor/bin/sail artisan tinker
```

E execute o comando:

```bash
app(App\Services\TelegramServices::class)->setWebhook();
```
