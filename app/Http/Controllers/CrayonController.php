<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Crayon;
use Illuminate\Support\Facades\DB;

class CrayonController extends Controller
{
    // Afficher la liste des crayons
    public function index()
    {
        $crayons = Crayon::all();
        return view('crayons.index', compact('crayons'));
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
    public function edit($id)
    {
        try {
            session_start();
        }
        catch (\Exception){}
        if($_SESSION['login'] == 'true'){
            $crayon = Crayon::findOrFail($id);
            return view('crayons.edit', compact('crayon'));
        }
        else{
            return redirect('/');
        }
    }

    // Mettre à jour les informations du crayon dans la base de données
    public function update(Request $request, $id)
    {
        $request->validate([
            'nom' => 'required',
            'quantite' => 'required|integer|min:0',
        ]);

        $crayon = Crayon::findOrFail($id);
        $crayon->update([
            'nom' => $request->input('nom'),
            'quantite' => $request->input('quantite'),
        ]);

        return redirect('/crayons')->with('success', 'Crayon mis à jour avec succès');
    }

    // Supprimer un crayon de la base de données
    public function destroy($id)
    {
        try {
            session_start();
        }
        catch (\Exception){}
        if($_SESSION['login'] == 'true'){
            $crayon = Crayon::findOrFail($id);
            $crayon->delete();

            return redirect('/crayons')->with('success', 'Crayon supprimé avec succès');
        }
        else{
            return redirect('/');
        }
    }

    public function search(Request $request){
        $crayons = DB::table('crayons')
            ->where('nom', 'like', DB::raw('"%' . $request->texte . '%"'))
            ->get();
        return view('crayons.index', compact('crayons'));
    }
}
