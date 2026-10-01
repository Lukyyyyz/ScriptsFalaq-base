<?php

namespace App\Policies;

use App\Models\Pergunta;
use App\Models\User;

class PerguntaPolicy
{
    /**
     * Pode deletar se for o autor da pergunta
     * OU o dono do evento ao qual a pergunta pertence.
     */
    public function delete(User $user, Pergunta $pergunta): bool
    {
        $ehAutor = $pergunta->user_id !== null
            && $user->id === (int) $pergunta->user_id;

        $ehDonoDoEvento = $pergunta->evento?->user_id !== null
            && $user->id === (int) $pergunta->evento->user_id;

        return $ehAutor || $ehDonoDoEvento;
    }
}
