<?php

/*
 * French validation messages for the rules this app uses (mirror of lang/ar).
 * Anything not listed here falls back to Laravel's built-in English messages.
 */
return [
    'required' => 'Le champ :attribute est obligatoire.',
    'string' => 'Le champ :attribute doit être une chaîne de caractères.',
    'email' => 'Le champ :attribute doit être une adresse e-mail valide.',
    'boolean' => 'Le champ :attribute doit être vrai ou faux.',
    'in' => 'La valeur sélectionnée pour :attribute est invalide.',
    'exists' => 'La valeur sélectionnée pour :attribute n’existe pas.',
    'unique' => 'La valeur du champ :attribute est déjà utilisée.',
    'confirmed' => 'La confirmation du champ :attribute ne correspond pas.',
    'date' => 'Le champ :attribute n’est pas une date valide.',
    'numeric' => 'Le champ :attribute doit être un nombre.',
    'current_password' => 'Le mot de passe actuel est incorrect.',
    'regex' => 'Le format du champ :attribute est invalide.',
    'array' => 'Le champ :attribute doit être une liste.',
    'lte' => [
        'numeric' => 'Le champ :attribute ne doit pas dépasser :value.',
        'string' => 'Le champ :attribute ne doit pas dépasser :value caractères.',
    ],
    'integer' => 'Le champ :attribute doit être un entier.',
    'between' => [
        'numeric' => 'Le champ :attribute doit être compris entre :min et :max.',
        'string' => 'Le champ :attribute doit contenir entre :min et :max caractères.',
    ],
    'min' => [
        'numeric' => 'Le champ :attribute doit être au moins :min.',
        'string' => 'Le champ :attribute doit contenir au moins :min caractères.',
        'array' => 'Le champ :attribute doit contenir au moins :min éléments.',
        'file' => 'Le fichier :attribute doit peser au moins :min Ko.',
    ],
    'max' => [
        'numeric' => 'Le champ :attribute ne doit pas dépasser :max.',
        'string' => 'Le champ :attribute ne doit pas dépasser :max caractères.',
        'array' => 'Le champ :attribute ne doit pas contenir plus de :max éléments.',
        'file' => 'Le fichier :attribute ne doit pas dépasser :max Ko.',
    ],

    'custom' => [
        'student_id' => [
            'unique' => 'Cet étudiant est déjà inscrit à ce cours.',
        ],
    ],

    'attributes' => [
        'name' => 'nom complet',
        'email' => 'e-mail',
        'password' => 'mot de passe',
        'phone' => 'numéro de téléphone',
        'city' => 'ville',
        'gender' => 'sexe',
        'guardian_phone' => 'téléphone du tuteur',
        'course_id' => 'cours',
        'enrollment_status' => 'statut d’inscription',
        'financial_status' => 'situation financière',
        'specialty' => 'spécialité',
        'hours' => 'heures de cours',
        'salary_type' => 'type de rémunération',
        'fixed_salary' => 'salaire fixe',
        'commission_rate' => 'taux de commission',
        'status' => 'statut',
        'level' => 'niveau',
        'price' => 'prix',
        'teacher_id' => 'enseignant',
        'capacity' => 'capacité',
        'room' => 'salle',
        'schedule' => 'horaire',
        'student_id' => 'étudiant',
        'group_id' => 'groupe',
        'date' => 'date d’inscription',
        'discount' => 'remise',
        'paid' => 'montant payé',
        'day' => 'jour',
        'amount' => 'montant',
        'method' => 'mode de paiement',
        'label' => 'libellé',
        'category' => 'catégorie',
        'payAmount' => 'montant',
        'current_password' => 'mot de passe actuel',
        'time' => 'horaire',
        'locale' => 'langue',
    ],
];
