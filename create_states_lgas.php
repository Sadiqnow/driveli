<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    // Create basic Nigerian states and LGAs
    $statesData = [
        ['name' => 'Lagos', 'code' => 'LA', 'lgas' => ['Agege', 'Ajeromi-Ifelodun', 'Alimosho', 'Amuwo-Odofin', 'Apapa', 'Badagry', 'Epe', 'Eti Osa', 'Ibeju-Lekki', 'Ifako-Ijaiye', 'Ikeja', 'Ikorodu', 'Kosofe', 'Lagos Island', 'Lagos Mainland', 'Mushin', 'Ojo', 'Oshodi-Isolo', 'Shomolu', 'Surulere']],
        ['name' => 'Abuja (FCT)', 'code' => 'FC', 'lgas' => ['Abaji', 'Bwari', 'Gwagwalada', 'Kuje', 'Kwali', 'Municipal Area Council']],
        ['name' => 'Kano', 'code' => 'KN', 'lgas' => ['Ajingi', 'Albasu', 'Bagwai', 'Bebeji', 'Bichi', 'Bunkure', 'Dala', 'Dambatta', 'Dawakin Kudu', 'Dawakin Tofa', 'Doguwa', 'Fagge', 'Gabasawa', 'Garko', 'Garun Mallam', 'Gaya', 'Gezawa', 'Gwale', 'Gwarzo', 'Kabo', 'Kano Municipal', 'Karaye', 'Kibiya', 'Kiru', 'Kumbotso', 'Kunchi', 'Kura', 'Madobi', 'Makoda', 'Minjibir', 'Nasarawa', 'Rano', 'Rimin Gado', 'Rogo', 'Shanono', 'Sumaila', 'Takai', 'Tarauni', 'Tofa', 'Tsanyawa', 'Tudun Wada', 'Ungogo', 'Warawa', 'Wudil']],
        ['name' => 'Rivers', 'code' => 'RI', 'lgas' => ['Abua/Odual', 'Ahoada East', 'Ahoada West', 'Akuku-Toru', 'Andoni', 'Asari-Toru', 'Bonny', 'Degema', 'Eleme', 'Emuoha', 'Etche', 'Gokana', 'Ikwerre', 'Khana', 'Obio/Akpor', 'Ogba/Egbema/Ndoni', 'Ogu/Bolo', 'Okrika', 'Omuma', 'Opobo/Nkoro', 'Oyigbo', 'Port Harcourt', 'Tai']],
        ['name' => 'Kaduna', 'code' => 'KD', 'lgas' => ['Birnin Gwari', 'Chikun', 'Giwa', 'Igabi', 'Ikara', 'Jaba', 'Jema\'a', 'Kachia', 'Kaduna North', 'Kaduna South', 'Kagarko', 'Kajuru', 'Kaura', 'Kauru', 'Kubau', 'Kudan', 'Lere', 'Makarfi', 'Sabon Gari', 'Sanga', 'Soba', 'Zangon Kataf', 'Zaria']],
        ['name' => 'Oyo', 'code' => 'OY', 'lgas' => ['Afijio', 'Akinyele', 'Atiba', 'Atisbo', 'Egbeda', 'Ibadan North', 'Ibadan North-East', 'Ibadan North-West', 'Ibadan South-East', 'Ibadan South-West', 'Ibarapa Central', 'Ibarapa East', 'Ibarapa North', 'Ido', 'Irepo', 'Iseyin', 'Itesiwaju', 'Iwajowa', 'Kajola', 'Lagelu', 'Ogbomoso North', 'Ogbomoso South', 'Ogo Oluwa', 'Olorunsogo', 'Oluyole', 'Ona Ara', 'Orelope', 'Ori Ire', 'Oyo East', 'Oyo West', 'Saki East', 'Saki West', 'Surulere']]
    ];
    
    foreach ($statesData as $stateData) {
        echo "Creating state: {$stateData['name']}" . PHP_EOL;
        
        $state = \App\Models\State::firstOrCreate(
            ['name' => $stateData['name']],
            ['code' => $stateData['code']]
        );
        
        foreach ($stateData['lgas'] as $lgaName) {
            \App\Models\LocalGovernment::firstOrCreate([
                'name' => $lgaName,
                'state_id' => $state->id
            ]);
        }
        
        echo "Created {$state->name} with " . count($stateData['lgas']) . " LGAs" . PHP_EOL;
    }
    
    echo "Final counts:" . PHP_EOL;
    echo "States: " . \App\Models\State::count() . PHP_EOL;
    echo "LGAs: " . \App\Models\LocalGovernment::count() . PHP_EOL;
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}