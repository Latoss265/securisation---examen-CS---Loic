<?php

namespace App\Http\Controllers;

use App\Services\RSAService;
use Illuminate\Http\Request;
use App\Models\Crayon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CrayonController extends Controller
{
    // Afficher la liste des crayons
    public function index(RSAService $rsa)
    {
        $crayons = Crayon::all();
        $chiffre_crayon = Crayon::all()->random();
        $chiffre_crayon = base64_encode($rsa->chiffrer($chiffre_crayon->nom));
        $dechiffre_crayon = $rsa->dechiffrer(base64_decode($chiffre_crayon));
        return view('crayons.index', compact('crayons'))->with('chiffre_crayon', $chiffre_crayon)->with('dechiffre_crayon', $dechiffre_crayon);
    }

    public function donothing(\App\Services\RSAService $rsa){
        $rsa->doUselessRSAWork();
    }

    // Afficher le formulaire de création de crayon
    public function create()
    {
        return view('crayons.create');
    }

    // Enregistrer un nouveau crayon dans la base de données
    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required',
            'quantite' => 'required|integer|min:0',
        ]);

        Crayon::create([
            'nom' => $request->input('nom'),
            'quantite' => $request->input('quantite'),
        ]);

        return redirect('/crayons')->with('success', 'Crayon ajouté avec succès');
    }

    // Afficher le formulaire de modification de crayon
    public function edit($id, Request $request)
    {
        try {
            session_start();
        }
        catch (\Exception){}
        if (isset($_SESSION['login'])) {
            if($_SESSION['login'] == 'true'){
                $token = $request->cookie('token');
                if ($this->decodeJWT($token) !== null && $this->decodeJWT($token)->role == 'user')
                {

                    $crayon = Crayon::findOrFail($id);
                    return view('crayons.edit', compact('crayon'));
                }
                else {
                    return redirect('/login');
                }
            }
            else{
                return redirect('/login');
            }
        }
        else {
            return redirect('/login');
        }

    }

    // Mettre à jour les informations du crayon dans la base de données
    public function update(Request $request, $id)
    {
        $request->validate([
            'nom' => 'required',
            'quantite' => 'required|integer|min:0',
        ]);

        $token = $request->cookie('token');
        if ($this->decodeJWT($token) !== null  && $this->decodeJWT($token)->role == 'user')
        {
            $crayon = Crayon::findOrFail($id);
            $crayon->update([
                'nom' => $request->input('nom'),
                'quantite' => $request->input('quantite'),
            ]);
            return redirect('/crayons')->with('success', 'Crayon mis à jour avec succès');
        }
        else {
            abort(403);
        }
    }

    // Supprimer un crayon de la base de données
    public function destroy($id, Request $request)
    {
        try {
            session_start();
        }
        catch (\Exception){}
        if (isset($_SESSION['login'])) {
            if($_SESSION['login'] == 'true'){
                $token = $request->cookie('token');
                if ($this->decodeJWT($token) !== null  && $this->decodeJWT($token)->role == 'user') {
                    $crayon = Crayon::findOrFail($id);
                    $crayon->delete();
                    return redirect('/crayons')->with('success', 'Crayon supprimé avec succès');
                }
                else {
                    return redirect('/login');
                }
            }
            else{
                return redirect('/login');
            }
        }
        else {
            return redirect('/login');
        }

    }

    public function search(Request $request){
        $crayons = DB::table('crayons')
            ->where('nom', 'like', "%".$request->texte."%")
            ->get();
        return view('crayons.index', compact('crayons'));
    }

    /**
     * Vérifie que le token JWT est valide
     * Repris de des revisions JWT
     */
    function decodeJWT($token) {
        try {
            $parts = explode('.', $token);
            if(isset($parts[0])){
                $header = $parts[0];
            }
            else {
                return null;
            }
            if(isset($parts[1])){
                $payload = $parts[1];
                $data = json_decode(base64_decode($payload));
            }
            else {
                return null;
            }
            if(isset($parts[2])){
                $decodedSignature = $parts[2];
            }
            else {
                return null;
            }
        }
        catch(Exception $exception) {
            return null;
        }
        if (!Hash::check($header.$payload, base64_decode($decodedSignature))) {
            return null;
        } else {
            return $data;
        }
    }

}
