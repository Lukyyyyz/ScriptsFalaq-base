# Ticket 3 — EventoController (arquivo não veio no zip)

Laravel 12: o Controller base NÃO inclui `authorize()`. Em `app/Http/Controllers/Controller.php`:

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Controller
{
    use AuthorizesRequests;
}
```

Em `app/Http/Controllers/EventoController.php`:

```php
use App\Models\Pergunta;

public function destroyPergunta(Pergunta $pergunta)
{
    $this->authorize('delete', $pergunta);

    $pergunta->delete();

    return back()->with('sucesso', 'Pergunta excluída com sucesso!');
}
```

Pré-requisito no model `App\Models\Pergunta`:

```php
public function evento() { return $this->belongsTo(Evento::class); }
```

(Opcional, evita N+1 na lista) no `show()`: `->with('evento')` na query das perguntas.
