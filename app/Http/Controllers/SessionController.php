<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SessionController extends Controller
{

    // Afficher le formulaire de création de crayon
    public function login(Request $request)
    {
        $user = DB::table('users')
            ->where('email', '=', $request->input('email'))
            ->first();
        if($user != null){
            if(!Hash::check($request->input('password'), $user->password)){ // vérifie si cela est le bon mot de passe
                return view('login');
            }
            try {
                session_start();
            }
            catch (\Exception){}
            $_SESSION['login'] = 'true';
            return redirect('/')->withCookie(cookie('token',$this->creer_token_jwt($user->name) , 60));
        }
        else{
            return view('login');
        }
    }

    // Enregistrer un nouveau crayon dans la base de données
    public function register(Request $request)
    {
       User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password'))
        ]);

        return redirect('/login');
    }

    // Afficher le formulaire de modification de crayon
    public function logout()
    {
        try {
            session_start();
        }
        catch (\Exception){}
        $_SESSION['login'] = 'false';

        return redirect('/')->withCookie(cookie('token', '', 1));

    }

    /**
     * Va créer un token JWT pour être envoyée au client
     * Chaque token aura une signature unique que seulement le serveur peut valider
     * Repris des revisions JWT
     * @param $utilisateur l'utilisateur
     * @return string
     */
    private function creer_token_jwt($utilisateur)
    {
        $header = base64_encode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
        $payload = base64_encode(json_encode(['user' => $utilisateur, 'role' => 'user']));
        $signature = Hash::make($header . $payload); // Hash::make créer un hash unique à chaque fois. Il est donc fort difficile de la brute force.
        $signature_base64 = base64_encode($signature);
        return $header . '.' . $payload . '.' . $signature_base64;
    }
}
