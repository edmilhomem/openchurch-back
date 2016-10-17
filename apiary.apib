FORMAT: 1A
HOST: http://localhost/openchurch/openchurch-back

# openchurch-back

Polls is a simple API allowing consumers to view polls and vote in them.

## Parâmetros de URL

Todas as rotas `GET` podem receber os seguintes parâmetros de URL:

- **[include](http://jsonapi.org/format/#fetching-relationships)**,
  e.g. `GET /igrejas?include=presbiterio` para incluir propriedades do relacionamento entre igrejas e os presbitérios 
  onde estão estabelecidas
- **[fields](http://jsonapi.org/format/#fetching-sparse-fieldsets)**,
  e.g. `GET /igrejas?fields[igrejas]=nome,created_at` para retornar uma coleção de igrejas contendo apenas o nome na 
   e a data de criação (`created_at`) na representação de cada documento da coleção (igreja)
- **[sort](http://jsonapi.org/format/#fetching-sorting)**,
  e.g. `GET /accounts?sort=username` to sort all accounts ascending by name
- **[page](http://jsonapi.org/format/#fetching-pagination)**,
  e.g. `GET /accounts?page[number]=1&page[size]=10` to return only the first
  10 accounts
- **[filter](http://jsonapi.org/format/#fetching-filtering)**,
  The `filter` is not defined by JSON API. Implementations must specify if how
  the `filter` query parameter is supported.

# Group Estados

## Coleção de Estados [/estados]

### Lista todos os Estados [GET]

Retorn a lista dos Estados. A utilização dos parâmetros de URL modifica a lista.

+ Response 200 (application/json)

    + Attributes (EstadosCollection)


## Estado [/estados/{id}]

+ Parameters
    + id (number) - Identificador do Estado

### Retorna um Estado [GET]

Retor um Estado com base no `id` informado como parâmetro de rota.

+ Response 200 (application/json)

    + Attributes (Estado)

### Create a New Question [POST]

You may create your own question using this action. It takes a JSON
object containing a question and a collection of answers in the
form of choices.

+ Request (application/json)

        {
            "question": "Favourite programming language?",
            "choices": [
                "Swift",
                "Python",
                "Objective-C",
                "Ruby"
            ]
        }

+ Response 201 (application/json)

    + Headers

            Location: /questions/2

    + Body

            {
                "question": "Favourite programming language?",
                "published_at": "2015-08-05T08:40:51.620Z",
                "choices": [
                    {
                        "choice": "Swift",
                        "votes": 0
                    }, {
                        "choice": "Python",
                        "votes": 0
                    }, {
                        "choice": "Objective-C",
                        "votes": 0
                    }, {
                        "choice": "Ruby",
                        "votes": 0
                    }
                ]
            }


## Data Structures

### Estado
+ id: 1 (number, required)
+ published_at: `2014-11-11T08:40:51.620Z` (required)
+ url: /questions/1 (required)
+ choices (array[Choice], required)

### EstadosCollection
+ data (array[Estado], required)

### Choice
+ choice: Javascript (required)
+ url: /questions/1/choices/1 (required)
+ votes: 2048 (number, required)