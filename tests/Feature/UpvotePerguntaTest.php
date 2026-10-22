<?php

namespace Tests\Feature;

use App\Models\Evento;
use App\Models\Pergunta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpvotePerguntaTest extends TestCase
{
    use RefreshDatabase;

    private function criarPergunta(string $texto = 'Pergunta?'): Pergunta
    {
        $evento = Evento::create(['titulo' => 'Evento', 'descricao' => 'Desc', 'data_evento' => now()]);

        return Pergunta::create(['evento_id' => $evento->id, 'texto' => $texto, 'status' => 'aprovado']);
    }

    private function usuario(): User
    {
        return User::factory()->create();
    }

    public function test_relacionamentos_sao_belongs_to_many(): void
    {
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class, (new Pergunta)->votos());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class, (new User)->perguntasVotadas());
    }

    public function test_anonimo_nao_pode_votar(): void
    {
        $pergunta = $this->criarPergunta();

        $this->post(route('perguntas.votar', $pergunta))->assertRedirect();

        $this->assertDatabaseCount('pergunta_user', 0);
    }

    public function test_usuario_autenticado_vota(): void
    {
        $user = $this->usuario();
        $pergunta = $this->criarPergunta();

        $this->actingAs($user)->post(route('perguntas.votar', $pergunta))->assertRedirect();

        $this->assertDatabaseHas('pergunta_user', ['pergunta_id' => $pergunta->id, 'user_id' => $user->id]);
    }

    public function test_segundo_clique_remove_o_voto(): void
    {
        $user = $this->usuario();
        $pergunta = $this->criarPergunta();

        $this->actingAs($user)->post(route('perguntas.votar', $pergunta));
        $this->actingAs($user)->post(route('perguntas.votar', $pergunta));

        $this->assertDatabaseMissing('pergunta_user', ['pergunta_id' => $pergunta->id, 'user_id' => $user->id]);
    }

    public function test_pivot_impede_voto_duplicado(): void
    {
        $user = $this->usuario();
        $pergunta = $this->criarPergunta();
        $pergunta->votos()->attach($user->id);

        $this->expectException(\Illuminate\Database\QueryException::class);
        $pergunta->votos()->attach($user->id);
    }

    public function test_perguntas_ordenadas_por_votos(): void
    {
        $evento = Evento::create(['titulo' => 'Evento', 'descricao' => 'Desc', 'data_evento' => now()]);
        $pouca = Pergunta::create(['evento_id' => $evento->id, 'texto' => 'Pouco votada', 'status' => 'aprovado']);
        $muita = Pergunta::create(['evento_id' => $evento->id, 'texto' => 'Muito votada', 'status' => 'aprovado']);

        $pouca->votos()->attach($this->usuario()->id);
        $muita->votos()->attach([$this->usuario()->id, $this->usuario()->id, $this->usuario()->id]);

        $this->get(route('eventos.show', $evento->id))
            ->assertOk()
            ->assertSeeInOrder(['Muito votada', 'Pouco votada']);
    }
}
