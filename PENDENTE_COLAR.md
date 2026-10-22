# Trechos que dependem de arquivos ausentes no zip

Estes arquivos não vieram no projeto (app/Models/Pergunta.php, app/Models/User.php,
app/Models/Evento.php, app/Http/Controllers/EventoController.php, bootstrap/app.php).
Cole os trechos abaixo neles.

## app/Models/Pergunta.php  (Sprint 07, Ticket #014 + Sprint 06, Ticket #010)
```php
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

public function moderacoes(): HasMany
{
    return $this->hasMany(Moderacao::class);
}

public function votos(): BelongsToMany
{
    return $this->belongsToMany(User::class)->withTimestamps();
}
```

## app/Models/User.php  (Sprint 06, Ticket #010)
```php
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

public function perguntasVotadas(): BelongsToMany
{
    return $this->belongsToMany(Pergunta::class, 'pergunta_user')->withTimestamps();
}
```

## app/Http/Controllers/EventoController.php  (Sprint 06, Tickets #011 e #012)
```php
public function votar(Pergunta $pergunta)
{
    $pergunta->votos()->toggle(auth()->id());

    return back()->with('sucesso', 'Seu voto foi atualizado!');
}

// Dentro de show(): substituir a query de perguntas por
$perguntas = Pergunta::where('evento_id', $evento->id)
    ->with('evento')
    ->withCount('votos')
    ->withExists(['votos as votado' => fn ($q) => $q->where('users.id', auth()->id())])
    ->orderByDesc('votos_count')
    ->orderByDesc('created_at')
    ->paginate(15);
```

## app/Models/Evento.php
A EventoPolicy precisa ser encontrada automaticamente pelo Laravel. Confirme que
Evento::class fica em App\Models e que a política está em App\Policies\EventoPolicy.
Se o Evento tiver outro nome de model, ajuste a classe em EventoPolicy.php.

## bootstrap/app.php  (só se o teste de visitante anônimo der RouteNotFoundException)
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->redirectGuestsTo(fn () => route('login.create'));
})
```

## Comandos
```
php artisan migrate
php artisan test --filter=UpvotePerguntaTest
```
