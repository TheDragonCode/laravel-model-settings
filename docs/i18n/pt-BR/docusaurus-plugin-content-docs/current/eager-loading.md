---
sidebar_position: 4
title: Carregamento antecipado
description: Evite consultas N+1 ao ler configurações de coleções de modelos Eloquent.
---

[← Trabalhando com configurações](settings.md) · [Voltar ao README](https://github.com/TheDragonCode/laravel-model-settings#readme) · [Configuração →](configuration.md)

# Carregamento antecipado

## Carregar configurações com os modelos

A leitura preguiçosa das configurações carrega a relação `modelSettings`. Para uma coleção, isso
gera uma consulta adicional de configurações por modelo.

Carregue a relação antecipadamente quando o resultado contiver vários modelos:

```php
$users = User::query()
    ->with('modelSettings')
    ->get();

$timezones = $users->map(
    fn (User $user) => $user->settings()->get('timezone')
);
```

A relação carregada antecipadamente contém as sobrescritas de cada modelo e todos os valores padrão
herdados. As chamadas seguintes de `get()` e `all()` usam a relação carregada.

## Carregar configurações depois da consulta

Use `loadMissing()` quando os modelos já estiverem disponíveis:

```php
$users->loadMissing('modelSettings');

$settings = $users->map(
    fn (User $user) => $user->settings()->all()
);
```

## Comportamento das consultas

Quando os modelos pais são buscados e suas configurações são lidas em seguida, o carregamento
preguiçoso e o antecipado têm o mesmo custo para um modelo. Para uma coleção, a diferença é visível:

| Modelos pais carregados | Carregamento preguiçoso | Carregamento antecipado |
|-------------------------|-------------------------|-------------------------|
| 1 | 2 consultas | 2 consultas |
| N | 1 + N consultas | 2 consultas |

O caminho de carregamento antecipado usa:

1. Uma consulta para os modelos pais.
2. Uma consulta para seus valores padrão e sobrescritas.

A consulta de configurações inclui os valores padrão da classe e todos os identificadores de modelo
solicitados. Em seguida, a relação copia os valores padrão herdados para o resultado carregado de
cada modelo e substitui as chaves correspondentes pelas sobrescritas desse modelo.

Esse comportamento é coberto para chaves primárias inteiras, UUID e ULID.

## Alterações depois do carregamento antecipado

`set()` e `forget()` limpam a relação `modelSettings` carregada nesse modelo. A próxima leitura
recarrega a relação e não reutiliza um valor desatualizado.

A alteração ainda executa suas próprias consultas de escrita. O carregamento antecipado muda apenas
as leituras seguintes.

## Veja também

- [Trabalhando com configurações](settings.md) — entenda como padrões e sobrescritas são combinados.
- [Referência da API](api-reference.md) — diferencie os métodos do serviço e a relação.
- [Configuração](configuration.md) — configure a conexão e o modelo de armazenamento.
