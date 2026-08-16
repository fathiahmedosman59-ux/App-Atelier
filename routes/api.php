<?php

use App\Http\Controllers\Api\FournisseurReponseController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — intégration système fournisseur (stcd-magasin)
|--------------------------------------------------------------------------
*/

Route::middleware('fournisseur.token')->group(function () {

    Route::patch(
        '/bons-commande/{numero}/lignes/{index}',
        [FournisseurReponseController::class, 'updateLigne']
    );

});
