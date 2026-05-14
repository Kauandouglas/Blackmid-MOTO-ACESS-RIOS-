# ⚡ Quick Start - Mercado Pago

## 30 Segundos para Começar

### 1. Configurar .env
```bash
# Copie do seu painel Mercado Pago
MERCADOPAGO_ACCESS_TOKEN=APP_USR_xxxxxxx
MERCADOPAGO_WEBHOOK_SECRET=seu_secret_aqui
```

### 2. Configurar Webhook no Mercado Pago
```
URL: https://seusite.com.br/webhooks/mercadopago
Eventos: payment.created, payment.updated
```

### 3. Pronto! 🎉
- Loja pronta para receber pagamentos
- Clientes podem pagar com Cartão, PIX ou Boleto
- Webhooks automáticos

---

## Teste Rápido

### Cartão Teste (Válido)
- **Número:** `4111 1111 1111 1111`
- **Vencimento:** `12/25`
- **CVV:** `123`

### Cartão Teste (Recusado)
- **Número:** `5555 5555 5555 4444`
- **Vencimento:** `12/25`
- **CVV:** `123`

---

## Debugging

### Log de Erros
```bash
tail -f storage/logs/laravel.log
```

### Teste de Conexão
```bash
# Verificar se credenciais estão corretas
php artisan tinker
> config('services.mercadopago.access_token')
```

### URLs Importantes
- **Checkout:** `/checkout`
- **Webhook:** `/webhooks/mercadopago`
- **Admin Pagamentos:** `/admin/pagamentos`

---

## Documentação Completa

Veja os arquivos de documentação:
- 📖 [SUMMARY_MIGRATION.md](SUMMARY_MIGRATION.md) - Resumo geral
- 📖 [MERCADOPAGO_MIGRATION.md](MERCADOPAGO_MIGRATION.md) - Detalhes técnicos
- 📖 [TESTING_MERCADOPAGO.md](TESTING_MERCADOPAGO.md) - Guia de testes

---

## Suporte Rápido

| Problema | Solução |
|----------|---------|
| "Invalid credentials" | Verifique credenciais no `.env` |
| Webhook não funciona | Valide URL e secret no painel |
| Pagamento não confirma | Verifique logs em `storage/logs/` |
| PIX não mostra QR | Confirme que PIX está habilitado no Mercado Pago |

---

**Tudo pronto! Boa venda! 🚀**
