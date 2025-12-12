<?php

namespace App\Services;

class RSAService
{
    private string $privateKeyPath;
    private string $publicKeyPath;

    public function __construct()
    {
        $this->privateKeyPath = storage_path('app/keys/useless_private.pem');
        $this->publicKeyPath  = storage_path('app/keys/useless_public.pem');
    }

    /**
     * Fonction inutile au dernier degré :
     * Chiffre une phrase arbitraire puis la déchiffre… pour rien.
     */
    public function doUselessRSAWork()
    {
        $phraseInutile = "Voici une opération RSA totalement inutile";

        // Charger les clés
        $publicKey  = openssl_pkey_get_public(file_get_contents($this->publicKeyPath));
        $privateKey = openssl_pkey_get_private(file_get_contents($this->privateKeyPath));

        // CHIFFREMENT INUTILE
        openssl_public_encrypt($phraseInutile, $chiffre, $publicKey);

        // DÉCHIFFREMENT INUTILE
        openssl_private_decrypt($chiffre, $dechiffre, $privateKey);

        // Et on NE FAIT RIEN avec le résultat.
        // Même pas un log. Le vide total.
        // Le nihilisme applicatif à son sommet.
    }

    public function chiffrer($phraseInutile)
    {
        // Charger les clés
        $publicKey  = openssl_pkey_get_public(file_get_contents($this->publicKeyPath));
        $privateKey = openssl_pkey_get_private(file_get_contents($this->privateKeyPath));

        openssl_public_encrypt($phraseInutile, $chiffre, $publicKey);
        return($chiffre);

    }
    public function dechiffrer($chiffre)
    {
        // Charger les clés
        $publicKey  = openssl_pkey_get_public(file_get_contents($this->publicKeyPath));
        $privateKey = openssl_pkey_get_private(file_get_contents($this->privateKeyPath));

        openssl_private_decrypt($chiffre, $dechiffre, $privateKey);
        return($dechiffre);
    }
}
