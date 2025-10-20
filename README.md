# Para iniciar:

`docker-compose up -d`
O serviço deve ficar disponível em `localhost:9501`

# Collection do postman 

`[projectRoot]\HyperTest.postman_collection.json`

# Testes Automatizados

Em um Terminal dentro do container sendo executado:
`composer test`

# Respostas do Teste

## 1. Arquitetura & Estado

**Estilo:** Monolito Modular com princípio de Adapters.

**Por quê:** É o melhor trade-off para um MVP. Mantém a simplicidade de um monólito para implantação e desenvolvimento. No caso de um projeto mais maduro, o ideal seria partira para uma arquitetura de microsserviços.

**Estado:**
* **Search:** Em memória, por requisição. Salvo no `Redis` enquanto a cotação for válida.
* **Price:** Em um cache rápido como o `Redis`. A chave é o `priceId` e o valor contém os dados do voo e o preço. O `expiresAt` é usado como `TTL` no `Redis`.
* **Book:** Em um banco de dados relacional (SQL) para garantir atomicidade e persistência (ex.: tabelas `bookings`, `travelers`). (Não implementado no teste)

**Consistência:** O serviço `book` deve verificar se o `priceId` ainda existe no `Redis` antes de salvar no banco de dados. Se a chave expirou (não existe), a reserva falha com um erro tratado, evitando condições de corrida.

## 2. Normalização & Polimorfismo

**Unificação:** Usado o padrão Adapter. Cada provedor (ex: `ProviderAAdapter`) implementa uma interface comum (`FlightProviderPort`) e tem a responsabilidade de traduzir sua resposta única para um `DTO` (Data Transfer Object) padronizado do nosso domínio.

**IDs estáveis para flight e fare:** Gerado no passo de busca. É um hash (`md5`) de dados essenciais da oferta (provedor, voos, horários, preço). Isso cria um ID único e determinístico que representa aquela oferta específica e pode ser usado como chave no `Redis`. Possibilita inclusive unificar voos de provedores diferentes, adicionando somente novas ofertas.

## 3. Workflow & Cache

**Ordem (Search → Price → Book):** O fluxo é imposto pelo estado. O `book` depende de um `priceId` que só é gerado pelo `price`. Se o `priceId` não existe ou expirou no `Redis`, o `book` falha. Assim como não há como obter uma reserva de preço se a cotação já expirou.

**Expiração:** Se o `priceId` expira (o `TTL` no `Redis` acaba), a api `book` recusa a reserva. A API não deve tentar refazer o preço automaticamente durante o `book`;

**Cache:**
* **Resultados de Busca:** Podem ser cacheados por um tempo muito curto (1-2 minutos) com a chave sendo um hash dos parâmetros de busca, para reduzir requisições idênticas seguidas aos provedores.

## 4. Resiliência

Embora não tenha sido contemplado no projeto teste, o `priceId` deveria ser salvo junto ao “book” para evitar chamadas repetidas. Como deve ser uma chamada mais demorada para o provedor, seria interessante aplicar também um cache simples de booleano com o id do `price` enquanto a requisição é resolvida