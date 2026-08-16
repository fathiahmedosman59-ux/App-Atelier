<?php

// Référentiel des opérations de maintenance/contrôle véhicule, avec temps de main
// d'œuvre indicatifs (en minutes). Utilisé pour suggérer automatiquement la durée
// (en heures) d'une ligne "main d'œuvre" de devis quand sa désignation correspond
// à une opération connue — la durée proposée est le temps_max arrondi au 1/4 h
// supérieur (cf. App\Services\ReservationService::dureeDefautHeures pour le même
// principe côté Service Rapide).
//
// Sections 10 (check-list avant/après départ) et 11 (suivi administratif) du
// référentiel source ne sont pas incluses ici : ce sont des tâches de gestion de
// flotte, pas des travaux atelier facturables sur un devis.

return [
    // 1. Contrôle technique réglementaire
    ['designation' => 'Visite technique périodique (VTP) / contrôle technique obligatoire', 'categorie' => 'Contrôle technique réglementaire', 'temps_min' => 30, 'temps_max' => 45],
    ['designation' => 'Contre-visite après réparation suite à défaillance majeure', 'categorie' => 'Contrôle technique réglementaire', 'temps_min' => 15, 'temps_max' => 20],
    ['designation' => 'Contrôle antipollution (émissions, opacité fumées diesel)', 'categorie' => 'Contrôle technique réglementaire', 'temps_min' => 10, 'temps_max' => 15],
    ['designation' => 'Contrôle du dispositif de limitation de vitesse (PL/transport)', 'categorie' => 'Contrôle technique réglementaire', 'temps_min' => 15, 'temps_max' => 20],
    ['designation' => 'Contrôle sonomètre / niveau sonore réglementaire', 'categorie' => 'Contrôle technique réglementaire', 'temps_min' => 10, 'temps_max' => 10],
    ['designation' => 'Vérification des équipements de sécurité obligatoires', 'categorie' => 'Contrôle technique réglementaire', 'temps_min' => 10, 'temps_max' => 15],
    ['designation' => 'Contrôle des dimensions et du poids (charge utile, PTAC)', 'categorie' => 'Contrôle technique réglementaire', 'temps_min' => 15, 'temps_max' => 20],

    // 2.1 Lubrification et fluides
    ['designation' => 'Vidange huile moteur + remplacement filtre à huile', 'categorie' => 'Lubrification et fluides', 'temps_min' => 30, 'temps_max' => 45],
    ['designation' => 'Contrôle et complément liquide de refroidissement', 'categorie' => 'Lubrification et fluides', 'temps_min' => 10, 'temps_max' => 15],
    ['designation' => 'Vidange liquide de refroidissement (renouvellement complet)', 'categorie' => 'Lubrification et fluides', 'temps_min' => 45, 'temps_max' => 60],
    ['designation' => 'Purge du circuit de freinage (liquide de frein)', 'categorie' => 'Lubrification et fluides', 'temps_min' => 45, 'temps_max' => 60],
    ['designation' => 'Vidange huile de boîte de vitesses (manuelle)', 'categorie' => 'Lubrification et fluides', 'temps_min' => 30, 'temps_max' => 45],
    ['designation' => 'Vidange huile de boîte de vitesses (automatique)', 'categorie' => 'Lubrification et fluides', 'temps_min' => 45, 'temps_max' => 75],
    ['designation' => 'Vidange huile de pont / différentiel', 'categorie' => 'Lubrification et fluides', 'temps_min' => 30, 'temps_max' => 45],
    ['designation' => 'Contrôle et appoint liquide de direction assistée', 'categorie' => 'Lubrification et fluides', 'temps_min' => 10, 'temps_max' => 15],
    ['designation' => 'Contrôle niveau liquide lave-glace', 'categorie' => 'Lubrification et fluides', 'temps_min' => 5, 'temps_max' => 5],

    // 2.2 Filtration
    ['designation' => 'Remplacement filtre à air moteur', 'categorie' => 'Filtration', 'temps_min' => 15, 'temps_max' => 20],
    ['designation' => 'Remplacement filtre à carburant', 'categorie' => 'Filtration', 'temps_min' => 20, 'temps_max' => 30],
    ['designation' => 'Remplacement filtre d\'habitacle', 'categorie' => 'Filtration', 'temps_min' => 15, 'temps_max' => 20],
    ['designation' => 'Nettoyage / remplacement filtre à particules (FAP/DPF)', 'categorie' => 'Filtration', 'temps_min' => 60, 'temps_max' => 120],

    // 2.3 Allumage et injection
    ['designation' => 'Contrôle et remplacement des bougies d\'allumage', 'categorie' => 'Allumage et injection', 'temps_min' => 30, 'temps_max' => 45],
    ['designation' => 'Contrôle et remplacement des bougies de préchauffage', 'categorie' => 'Allumage et injection', 'temps_min' => 45, 'temps_max' => 60],
    ['designation' => 'Contrôle et nettoyage des injecteurs', 'categorie' => 'Allumage et injection', 'temps_min' => 60, 'temps_max' => 90],
    ['designation' => 'Contrôle du système d\'alimentation en carburant', 'categorie' => 'Allumage et injection', 'temps_min' => 30, 'temps_max' => 45],

    // 2.4 Distribution et transmission
    ['designation' => 'Remplacement courroie de distribution (+ galets, pompe à eau)', 'categorie' => 'Distribution et transmission', 'temps_min' => 180, 'temps_max' => 300],
    ['designation' => 'Remplacement chaîne de distribution', 'categorie' => 'Distribution et transmission', 'temps_min' => 240, 'temps_max' => 480],
    ['designation' => 'Contrôle et remplacement courroie d\'accessoires', 'categorie' => 'Distribution et transmission', 'temps_min' => 30, 'temps_max' => 45],
    ['designation' => 'Contrôle de l\'embrayage (jeu, usure disque)', 'categorie' => 'Distribution et transmission', 'temps_min' => 20, 'temps_max' => 30],
    ['designation' => 'Remplacement de l\'embrayage complet', 'categorie' => 'Distribution et transmission', 'temps_min' => 180, 'temps_max' => 300],
    ['designation' => 'Contrôle des cardans et soufflets de transmission', 'categorie' => 'Distribution et transmission', 'temps_min' => 20, 'temps_max' => 30],

    // 3. Système de freinage
    ['designation' => 'Contrôle d\'épaisseur des plaquettes/mâchoires', 'categorie' => 'Système de freinage', 'temps_min' => 15, 'temps_max' => 20],
    ['designation' => 'Remplacement des plaquettes de frein (par essieu)', 'categorie' => 'Système de freinage', 'temps_min' => 45, 'temps_max' => 60],
    ['designation' => 'Contrôle et remplacement des disques de frein (par essieu)', 'categorie' => 'Système de freinage', 'temps_min' => 60, 'temps_max' => 90],
    ['designation' => 'Contrôle et remplacement des tambours de frein', 'categorie' => 'Système de freinage', 'temps_min' => 60, 'temps_max' => 90],
    ['designation' => 'Contrôle du frein de stationnement', 'categorie' => 'Système de freinage', 'temps_min' => 15, 'temps_max' => 20],
    ['designation' => 'Purge et remplacement du liquide de frein', 'categorie' => 'Système de freinage', 'temps_min' => 45, 'temps_max' => 60],
    ['designation' => 'Contrôle des flexibles et canalisations de frein', 'categorie' => 'Système de freinage', 'temps_min' => 20, 'temps_max' => 30],
    ['designation' => 'Contrôle du servofrein et du maître-cylindre', 'categorie' => 'Système de freinage', 'temps_min' => 30, 'temps_max' => 45],
    ['designation' => 'Test du système ABS/ESP (diagnostic électronique)', 'categorie' => 'Système de freinage', 'temps_min' => 20, 'temps_max' => 30],

    // 4. Direction et suspension
    ['designation' => 'Contrôle de la géométrie (parallélisme, carrossage, chasse)', 'categorie' => 'Direction et suspension', 'temps_min' => 45, 'temps_max' => 60],
    ['designation' => 'Remplacement rotules de direction/suspension (par côté)', 'categorie' => 'Direction et suspension', 'temps_min' => 60, 'temps_max' => 90],
    ['designation' => 'Remplacement biellettes de barre stabilisatrice', 'categorie' => 'Direction et suspension', 'temps_min' => 30, 'temps_max' => 45],
    ['designation' => 'Remplacement des amortisseurs (train complet)', 'categorie' => 'Direction et suspension', 'temps_min' => 60, 'temps_max' => 90],
    ['designation' => 'Remplacement des ressorts de suspension', 'categorie' => 'Direction et suspension', 'temps_min' => 60, 'temps_max' => 90],
    ['designation' => 'Contrôle/remplacement silentblocs et supports moteur', 'categorie' => 'Direction et suspension', 'temps_min' => 45, 'temps_max' => 90],
    ['designation' => 'Contrôle jeu de direction et crémaillère', 'categorie' => 'Direction et suspension', 'temps_min' => 30, 'temps_max' => 45],
    ['designation' => 'Remplacement de la crémaillère de direction', 'categorie' => 'Direction et suspension', 'temps_min' => 120, 'temps_max' => 180],
    ['designation' => 'Contrôle de la direction assistée', 'categorie' => 'Direction et suspension', 'temps_min' => 30, 'temps_max' => 45],

    // 5. Pneumatiques et roues
    ['designation' => 'Contrôle de la pression des pneumatiques', 'categorie' => 'Pneumatiques et roues', 'temps_min' => 5, 'temps_max' => 10],
    ['designation' => 'Contrôle de l\'usure et des sculptures', 'categorie' => 'Pneumatiques et roues', 'temps_min' => 10, 'temps_max' => 15],
    ['designation' => 'Contrôle de l\'état général (hernies, coupures)', 'categorie' => 'Pneumatiques et roues', 'temps_min' => 10, 'temps_max' => 15],
    ['designation' => 'Permutation des pneumatiques', 'categorie' => 'Pneumatiques et roues', 'temps_min' => 30, 'temps_max' => 40],
    ['designation' => 'Équilibrage des roues (par roue)', 'categorie' => 'Pneumatiques et roues', 'temps_min' => 10, 'temps_max' => 15],
    ['designation' => 'Contrôle du couple de serrage des roues', 'categorie' => 'Pneumatiques et roues', 'temps_min' => 10, 'temps_max' => 10],
    ['designation' => 'Remplacement des pneumatiques (jeu de 4)', 'categorie' => 'Pneumatiques et roues', 'temps_min' => 60, 'temps_max' => 80],
    ['designation' => 'Contrôle roue de secours / kit anticrevaison', 'categorie' => 'Pneumatiques et roues', 'temps_min' => 5, 'temps_max' => 10],

    // 6. Système électrique et électronique
    ['designation' => 'Contrôle de l\'état et charge de la batterie', 'categorie' => 'Système électrique et électronique', 'temps_min' => 10, 'temps_max' => 15],
    ['designation' => 'Contrôle alternateur / démarreur', 'categorie' => 'Système électrique et électronique', 'temps_min' => 30, 'temps_max' => 45],
    ['designation' => 'Remplacement alternateur ou démarreur', 'categorie' => 'Système électrique et électronique', 'temps_min' => 60, 'temps_max' => 90],
    ['designation' => 'Contrôle de l\'éclairage extérieur complet', 'categorie' => 'Système électrique et électronique', 'temps_min' => 15, 'temps_max' => 20],
    ['designation' => 'Réglage de la hauteur des projecteurs', 'categorie' => 'Système électrique et électronique', 'temps_min' => 15, 'temps_max' => 20],
    ['designation' => 'Contrôle éclairage intérieur et témoins tableau de bord', 'categorie' => 'Système électrique et électronique', 'temps_min' => 10, 'temps_max' => 15],
    ['designation' => 'Diagnostic électronique valise OBD', 'categorie' => 'Système électrique et électronique', 'temps_min' => 20, 'temps_max' => 30],
    ['designation' => 'Contrôle des capteurs (ABS, TPMS, régime, température)', 'categorie' => 'Système électrique et électronique', 'temps_min' => 20, 'temps_max' => 30],
    ['designation' => 'Contrôle essuie-glaces et lave-glace', 'categorie' => 'Système électrique et électronique', 'temps_min' => 15, 'temps_max' => 20],
    ['designation' => 'Contrôle du klaxon et avertisseurs sonores', 'categorie' => 'Système électrique et électronique', 'temps_min' => 10, 'temps_max' => 10],
    ['designation' => 'Diagnostic airbags / prétensionneurs', 'categorie' => 'Système électrique et électronique', 'temps_min' => 30, 'temps_max' => 45],

    // 7. Climatisation et chauffage
    ['designation' => 'Contrôle de l\'étanchéité du circuit de climatisation', 'categorie' => 'Climatisation et chauffage', 'temps_min' => 20, 'temps_max' => 30],
    ['designation' => 'Recharge / complément fluide réfrigérant', 'categorie' => 'Climatisation et chauffage', 'temps_min' => 30, 'temps_max' => 45],
    ['designation' => 'Désinfection et nettoyage du circuit', 'categorie' => 'Climatisation et chauffage', 'temps_min' => 20, 'temps_max' => 30],
    ['designation' => 'Contrôle du compresseur de climatisation', 'categorie' => 'Climatisation et chauffage', 'temps_min' => 20, 'temps_max' => 30],
    ['designation' => 'Contrôle du système de chauffage/dégivrage', 'categorie' => 'Climatisation et chauffage', 'temps_min' => 20, 'temps_max' => 30],

    // 8. Carrosserie, sécurité et accessoires
    ['designation' => 'Contrôle de l\'état général de la carrosserie', 'categorie' => 'Carrosserie, sécurité et accessoires', 'temps_min' => 15, 'temps_max' => 20],
    ['designation' => 'Contrôle de l\'étanchéité (joints, pare-brise, toit)', 'categorie' => 'Carrosserie, sécurité et accessoires', 'temps_min' => 15, 'temps_max' => 20],
    ['designation' => 'Contrôle des ceintures de sécurité', 'categorie' => 'Carrosserie, sécurité et accessoires', 'temps_min' => 15, 'temps_max' => 20],
    ['designation' => 'Contrôle des rétroviseurs et du vitrage', 'categorie' => 'Carrosserie, sécurité et accessoires', 'temps_min' => 10, 'temps_max' => 15],
    ['designation' => 'Contrôle des systèmes d\'attelage / arrimage', 'categorie' => 'Carrosserie, sécurité et accessoires', 'temps_min' => 15, 'temps_max' => 20],
    ['designation' => 'Contrôle hayon/plateau/benne et équipements spécifiques', 'categorie' => 'Carrosserie, sécurité et accessoires', 'temps_min' => 20, 'temps_max' => 30],
    ['designation' => 'Contrôle des équipements de levage embarqués', 'categorie' => 'Carrosserie, sécurité et accessoires', 'temps_min' => 20, 'temps_max' => 30],
    ['designation' => 'Traitement anticorrosion et protection carrosserie', 'categorie' => 'Carrosserie, sécurité et accessoires', 'temps_min' => 60, 'temps_max' => 120],

    // 9. Système d'échappement
    ['designation' => 'Contrôle étanchéité et fixation de la ligne d\'échappement', 'categorie' => 'Système d\'échappement', 'temps_min' => 15, 'temps_max' => 20],
    ['designation' => 'Contrôle pot catalytique et sonde lambda', 'categorie' => 'Système d\'échappement', 'temps_min' => 30, 'temps_max' => 45],
    ['designation' => 'Contrôle et nettoyage du filtre à particules', 'categorie' => 'Système d\'échappement', 'temps_min' => 60, 'temps_max' => 90],
    ['designation' => 'Contrôle du silencieux', 'categorie' => 'Système d\'échappement', 'temps_min' => 15, 'temps_max' => 20],

    // Sinistre (mentionné dans le référentiel, hors sections numérotées)
    ['designation' => 'Traitement d\'un sinistre / rapport d\'accident', 'categorie' => 'Sinistre', 'temps_min' => 30, 'temps_max' => 60],
];
