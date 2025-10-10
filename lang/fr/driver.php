<?php

return [
    'categories' => [
        'commercial_truck' => 'Conducteur de Camion Commercial',
        'professional' => 'Conducteur Professionnel',
        'public' => 'Conducteur Public',
        'executive' => 'Conducteur Exécutif',
    ],

    'employment' => [
        'part_time' => 'Temps Partiel',
        'full_time' => 'Temps Plein',
        'contract' => 'Contrat',
        'assignment' => 'Basé sur Mission',
    ],

    'status' => [
        'active' => 'Actif',
        'inactive' => 'Inactif',
        'suspended' => 'Suspendu',
        'blocked' => 'Bloqué',
    ],

    'verification' => [
        'pending' => 'En Attente',
        'verified' => 'Vérifié',
        'rejected' => 'Rejeté',
        'reviewing' => 'En Cours d\'Examen',
    ],

    'kyc' => [
        'not_started' => 'Pas Commencé',
        'pending' => 'En Attente',
        'in_progress' => 'En Cours',
        'completed' => 'Terminé',
        'rejected' => 'Rejeté',
        'expired' => 'Expiré',
    ],

    'steps' => [
        'step_1' => [
            'title' => 'Profil de Base et Sélection de Catégorie',
            'description' => 'Configurez votre profil de base et choisissez votre catégorie de conducteur',
            'progress' => '30% Terminé',
        ],
        'step_2' => [
            'title' => 'Exigences Spécifiques à la Catégorie',
            'description' => 'Complétez les exigences spécifiques à votre catégorie de conducteur',
            'progress' => '65% Terminé',
        ],
        'step_3' => [
            'title' => 'Vérification et Intégration',
            'description' => 'Vérification finale et configuration de la plateforme',
            'progress' => '100% Terminé',
        ],
    ],

    'registration' => [
        'welcome_title' => 'Rejoignez le Réseau Mondial de Conducteurs',
        'welcome_subtitle' => 'Connectez-vous avec des opportunités dans le monde entier',
        'category_selection_title' => 'Quel type de conducteur êtes-vous?',
        'category_selection_subtitle' => 'Choisissez la catégorie qui décrit le mieux votre expertise de conduite',
        'employment_preference_title' => 'Quelle est votre préférence d\'emploi?',
        'employment_preference_subtitle' => 'Sélectionnez comment vous préférez travailler',
    ],

    'messages' => [
        'profile_updated' => 'Profil mis à jour avec succès!',
        'kyc_step_completed' => 'Étape KYC complétée avec succès!',
        'kyc_submitted' => 'KYC soumis pour examen avec succès!',
        'documents_uploaded' => 'Documents téléchargés avec succès!',
        'verification_pending' => 'Votre profil est en cours d\'examen. Nous vous informerons une fois vérifié.',
    ],

    'dashboard' => [
        'welcome' => 'Bon retour, :name!',
        'kyc_progress' => 'Progrès KYC',
        'profile_completion' => 'Achèvement du Profil',
        'recent_activity' => 'Activité Récente',
        'earnings_overview' => 'Aperçu des Gains',
        'job_opportunities' => 'Opportunités Disponibles',
    ],
];