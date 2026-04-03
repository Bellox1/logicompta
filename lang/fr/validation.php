<?php

return [
    'accepted'             => 'Le champ :attribute doit être accepté.',
    'confirmed'            => 'La confirmation du champ :attribute ne correspond pas.',
    'email'                => 'Le champ :attribute doit être une adresse email valide.',
    'max'                  => [
        'numeric' => 'La valeur de :attribute ne peut être supérieure à :max.',
        'string'  => 'Le texte :attribute ne peut avoir plus de :max caractères.',
    ],
    'min'                  => [
        'numeric' => 'La valeur de :attribute doit être au moins de :min.',
        'string'  => 'Le texte :attribute doit avoir au moins :min caractères.',
    ],
    'required'             => 'Le champ :attribute est obligatoire.',
    'unique'               => 'La valeur du champ :attribute est déjà utilisée.',
    
    'attributes' => [
        'name'                  => 'Nom Complet',
        'email'                 => 'Email',
        'password'              => 'Mot de passe',
        'password_confirmation' => 'Confirmation du mot de passe',
    ],
];
