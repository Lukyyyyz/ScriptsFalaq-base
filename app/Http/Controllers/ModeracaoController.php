<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreModeracaoRequest;
use App\Models\Evento;
use App\Models\Moderacao;
use App\Models\Pergunta;
use Illuminate\Http\Request;

class ModeracaoController extends Controller
{
    /**
     * Participante denuncia uma pergunta.
     */
    public function store(StoreModeracaoRequest $request, Pergunta $pergunta)
    {
        $pergunta->moderacoes()->create($request->validated());

        return back()->with('sucesso', 'Denúncia registrada. Obrigado por ajudar a manter o evento saudável!');
    }

    /**
     * Fila de moderação do evento (apenas o dono do evento).
     */
    public function index(Evento $evento)
    {
        $this->authorize('moderate', $evento);

        $moderacoes = Moderacao::whereHas('pergunta', fn ($q) => $q->where('evento_id', $evento->id))
            ->with('pergunta')
            ->latest()
            ->paginate(15);

        return view('moderacoes.index', compact('evento', 'moderacoes'));
    }

    /**
     * Aprova ou rejeita a pergunta denunciada (apenas o dono do evento).
     */
    public function updateStatus(Request $request, Moderacao $moderacao)
    {
        $this->authorize('moderate', $moderacao->pergunta->evento);

        $dados = $request->validate([
            'status' => ['required', 'in:aprovado,rejeitado'],
        ]);

        $moderacao->update($dados);

        return back()->with('sucesso', 'Status da moderação atualizado.');
    }
}
