# Guia de Teste - Mercado Pago

## Pré-requisitos

1. Conta Mercado Pago ativa em [www.mercadopago.com.br](https://www.mercadopago.com.br)
2. Credenciais do Mercado Pago configuradas no `.env`

## Obter Credenciais

### Access Token

1. Acesse sua conta no Mercado Pago
2. Vá para **Configurações** → **Credenciais**
3. Copie o **Access Token** (modo teste ou produção)
4. Cole no `.env` como `MERCADOPAGO_ACCESS_TOKEN`

### Webhook Secret

1. Acesse **Aplicações** → **Seus Aplicativos**
2. Clique em seu aplicativo (ou crie um novo)
3. Vá em **Webhooks**
4. Copie o **Token de Segurança/Secret**
5. Cole no `.env` como `MERCADOPAGO_WEBHOOK_SECRET`

## Configurar .env

```env
MERCADOPAGO_ACCESS_TOKEN=APP_USR_xxxxxxxxxxxxxxxxxxxxxxxxxxxx
MERCADOPAGO_WEBHOOK_SECRET=seu_webhook_secret_aqui
```

## Testes Manuais

### 1. Teste do Checkout

1. Acesse a loja e adicione um produto ao carrinho
2. Vá para o checkout
3. Selecione **Mercado Pago** como método de pagamento
4. Clique em **Finalizar Pedido**
5. Você deve ser redirecionado para o checkout do Mercado Pago

### 2. Teste de Pagamento (Modo Teste)

No modo teste do Mercado Pago, use estas credenciais:

**Cartão de teste (Válido):**
- Número: `4111 1111 1111 1111`
- Vencimento: `12/25` (qualquer mês/ano futuro)
- CVV: `123`
- Titular: Qualquer nome

**Cartão de teste (Recusado):**
- Número: `5555 5555 5555 4444`
- Vencimento: `12/25`
- CVV: `123`

### 3. Teste de PIX

1. Selecione **PIX** como método no checkout
2. Você receberá um QR Code
3. Faça a leitura com seu banco para simular pagamento

### 4. Teste de Boleto

1. Selecione **Boleto** como método no checkout
2. Um boleto será gerado para visualização
3. Simule o pagamento do boleto em seu aplicativo de testes

## Validações

### ✅ Verificar Criação de Preferência

A API deve retornar um objeto com estrutura similar a:

```json
{
  "id": "123456789-abcd1234",
  "init_point": "https://www.mercadopago.com.br/checkout/v1/123456789",
  "sandbox_init_point": "https://sandbox.mercadopago.com.br/checkout/v1/123456789",
  "client_id": "123456789",
  "preference_id": "123456789-abcd1234",
  "back_urls": {
    "success": "https://seusite.com.br/checkout/payment/mercadopago/success/1",
    "pending": "https://seusite.com.br/checkout/payment/mercadopago/pending/1",
    "failure": "https://seusite.com.br/checkout/payment/cancel/1"
  }
}
```

### ✅ Verificar Webhook

1. Acesse **Monitoramento** no painel do Mercado Pago
2. Depois de um pagamento, você deve ver eventos de webhook
3. Logs do webhook devem aparecer no seu sistema

### ✅ Verificar Banco de Dados

Após um pagamento bem-sucedido:

```sql
-- Verificar se o pedido foi marcado como pago
SELECT id, status, payment_status FROM orders WHERE id = [order_id];

-- Esperado: payment_status = 'paid'
```

## URLs Necessárias para Configurar

Configure estes URLs no painel do Mercado Pago:

### URLs de Retorno (Back URLs)

- **Success**: `https://seusite.com.br/checkout/payment/mercadopago/success/{order_id}`
- **Pending**: `https://seusite.com.br/checkout/payment/mercadopago/pending/{order_id}`
- **Failure**: `https://seusite.com.br/checkout/payment/cancel/{order_id}`

### Webhook

- **URL**: `https://seusite.com.br/webhooks/mercadopago`
- **Eventos**: `payment.created`, `payment.updated`, `charge.completed`

## Troubleshooting

### Erro: "Invalid credentials"

- Verifique se o `MERCADOPAGO_ACCESS_TOKEN` está correto
- Certifique-se de que a conta Mercado Pago está ativa
- Regenere o token se necessário

### Erro: "Invalid signature" no webhook

- Verifique se `MERCADOPAGO_WEBHOOK_SECRET` está correto
- Certifique-se de que o Secret foi copiado integralmente
- Teste a assinatura manualmente usando a ferramenta de teste do Mercado Pago

### Erro: "Payment not found"

- A preferência pode não ter sido criada corretamente
- Verifique os logs da API do Mercado Pago
- Certifique-se de que todos os campos obrigatórios estão sendo enviados

### Cliente não retorna após pagamento

- Verifique as back_urls configuradas
- Certifique-se de que as rotas estão corretas
- Teste manualmente se a rota `/checkout/payment/mercadopago/success/{id}` é acessível

## Logs

Verificar logs do sistema em:

```
storage/logs/laravel.log
```

Procure por:
- Erros de API do Mercado Pago
- Falhas na verificação de webhook
- Erros ao processar pagamentos

## Próximas Etapas

1. ✅ Configurar credenciais Mercado Pago
2. ✅ Configurar webhooks no painel
3. ✅ Testar pagamento com cartão teste
4. ✅ Testar pagamento com PIX
5. ✅ Testar pagamento com Boleto
6. ✅ Testar webhook
7. ✅ Ir para produção (usar credenciais reais)

## Contato Mercado Pago

- Site: [www.mercadopago.com.br](https://www.mercadopago.com.br)
- Documentação: [developers.mercadopago.com.br](https://developers.mercadopago.com.br)
- Suporte: [Contato Mercado Pago](https://www.mercadopago.com.br/ajuda)
