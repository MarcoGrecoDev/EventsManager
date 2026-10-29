<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ForgotUsernameController extends Controller
{
    public function showForm()
    {
        return view('auth.forgot-username');
    }

    /**
     * L'utente fornisce solo l'email (per definizione non ricorda lo username):
     * se corrisponde a un account, lo username viene inviato a quella stessa
     * email (mai mostrato a video, per non confermare a chiunque se un'email
     * è registrata o no).
     */
    public function send(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = $request->input('email');
        $user = User::where('email', $email)->first();

        if (!$user) {
            return back()->with('error', 'Nessun utente trovato con questa email.');
        }

        Mail::raw(
            "Ciao {$user->nome},\n\n" .
            "Hai richiesto il recupero del tuo username per Excursio.\n\n" .
            "Il tuo username è: {$user->username}\n\n" .
            "Puoi usarlo per accedere alla pagina di login, insieme alla tua password.\n\n" .
            "Se non hai richiesto tu questo recupero, ignora questa email.\n\n" .
            "Saluti,\nIl team di Excursio",
            function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Excursio - Recupero username');
            }
        );

        return back()->with('success', 'Ti abbiamo inviato il tuo username a questa email. Controlla la tua posta.');
    }
}
