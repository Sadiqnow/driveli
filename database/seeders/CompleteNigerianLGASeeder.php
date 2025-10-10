<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\State;
use App\Models\LocalGovernment;

class CompleteNigerianLGASeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing LGA data (except FCT and Lagos if they exist)
        $this->command->info('Seeding Nigerian Local Government Areas...');

        // Get all Nigerian LGAs organized by state
        $statesWithLgas = $this->getAllNigerianLGAs();

        foreach ($statesWithLgas as $stateCode => $lgas) {
            $state = State::where('code', $stateCode)->first();
            
            if (!$state) {
                $this->command->warn("State with code {$stateCode} not found. Skipping...");
                continue;
            }

            // Check if LGAs for this state already exist
            $existingLgasCount = LocalGovernment::where('state_id', $state->id)->count();
            
            if ($existingLgasCount > 0) {
                $this->command->info("State {$state->name} already has {$existingLgasCount} LGAs. Skipping...");
                continue;
            }

            // Insert LGAs for this state
            $lgaData = [];
            foreach ($lgas as $lgaName) {
                $lgaData[] = [
                    'state_id' => $state->id,
                    'name' => $lgaName,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            LocalGovernment::insert($lgaData);
            $this->command->info("Seeded " . count($lgas) . " LGAs for {$state->name} state");
        }

        $totalLgas = LocalGovernment::count();
        $this->command->info("Total LGAs in database: {$totalLgas}");
    }

    /**
     * Get all Nigerian states and their LGAs
     */
    private function getAllNigerianLGAs(): array
    {
        return [
            'AB' => [ // Abia
                'Aba North', 'Aba South', 'Arochukwu', 'Bende', 'Ikwuano',
                'Isiala Ngwa North', 'Isiala Ngwa South', 'Isuikwuato', 'Obi Ngwa',
                'Ohafia', 'Osisioma', 'Ugwunagbo', 'Ukwa East', 'Ukwa West',
                'Umuahia North', 'Umuahia South', 'Umu Nneochi'
            ],
            'AD' => [ // Adamawa
                'Demsa', 'Fufure', 'Ganye', 'Gayuk', 'Gombi', 'Grie', 'Hong',
                'Jada', 'Larmurde', 'Madagali', 'Maiha', 'Mayo Belwa', 'Michika',
                'Mubi North', 'Mubi South', 'Numan', 'Shelleng', 'Song',
                'Toungo', 'Yola North', 'Yola South'
            ],
            'AK' => [ // Akwa Ibom
                'Abak', 'Eastern Obolo', 'Eket', 'Esit Eket', 'Essien Udim',
                'Etim Ekpo', 'Etinan', 'Ibeno', 'Ibesikpo Asutan', 'Ibiono-Ibom',
                'Ika', 'Ikono', 'Ikot Abasi', 'Ikot Ekpene', 'Ini', 'Itu',
                'Mbo', 'Mkpat-Enin', 'Nsit-Atai', 'Nsit-Ibom', 'Nsit-Ubium',
                'Obot Akara', 'Okobo', 'Onna', 'Oron', 'Oruk Anam',
                'Udung-Uko', 'Ukanafun', 'Uruan', 'Urue-Offong/Oruko', 'Uyo'
            ],
            'AN' => [ // Anambra
                'Aguata', 'Anambra East', 'Anambra West', 'Anaocha', 'Awka North',
                'Awka South', 'Ayamelum', 'Dunukofia', 'Ekwusigo', 'Idemili North',
                'Idemili South', 'Ihiala', 'Njikoka', 'Nnewi North', 'Nnewi South',
                'Ogbaru', 'Onitsha North', 'Onitsha South', 'Orumba North', 'Orumba South',
                'Oyi'
            ],
            'BA' => [ // Bauchi
                'Alkaleri', 'Bauchi', 'Bogoro', 'Damban', 'Darazo', 'Dass',
                'Gamawa', 'Ganjuwa', 'Giade', 'Itas/Gadau', 'Jama\'are', 'Katagum',
                'Kirfi', 'Misau', 'Ningi', 'Shira', 'Tafawa Balewa', 'Toro',
                'Warji', 'Zaki'
            ],
            'BY' => [ // Bayelsa
                'Brass', 'Ekeremor', 'Kolokuma/Opokuma', 'Nembe', 'Ogbia',
                'Sagbama', 'Southern Ijaw', 'Yenagoa'
            ],
            'BN' => [ // Benue
                'Ado', 'Agatu', 'Apa', 'Buruku', 'Gboko', 'Guma', 'Gwer East',
                'Gwer West', 'Katsina-Ala', 'Konshisha', 'Kwande', 'Logo',
                'Makurdi', 'Obi', 'Ogbadibo', 'Ohimini', 'Oju', 'Okpokwu',
                'Oturkpo', 'Tarka', 'Ukum', 'Ushongo', 'Vandeikya'
            ],
            'BO' => [ // Borno
                'Abadam', 'Askira/Uba', 'Bama', 'Bayo', 'Biu', 'Chibok', 'Damboa',
                'Dikwa', 'Gubio', 'Guzamala', 'Gwoza', 'Hawul', 'Jere', 'Kaga',
                'Kala/Balge', 'Konduga', 'Kukawa', 'Kwaya Kusar', 'Mafa',
                'Magumeri', 'Maiduguri', 'Marte', 'Mobbar', 'Monguno', 'Ngala',
                'Nganzai', 'Shani'
            ],
            'CR' => [ // Cross River
                'Abi', 'Akamkpa', 'Akpabuyo', 'Bakassi', 'Bekwarra', 'Biase',
                'Boki', 'Calabar Municipal', 'Calabar South', 'Etung', 'Ikom',
                'Obanliku', 'Obubra', 'Obudu', 'Odukpani', 'Ogoja', 'Yakaar',
                'Yala'
            ],
            'DE' => [ // Delta
                'Aniocha North', 'Aniocha South', 'Bomadi', 'Burutu', 'Ethiope East',
                'Ethiope West', 'Ika North East', 'Ika South', 'Isoko North',
                'Isoko South', 'Ndokwa East', 'Ndokwa West', 'Okpe', 'Oshimili North',
                'Oshimili South', 'Patani', 'Sapele', 'Udu', 'Ughelli North',
                'Ughelli South', 'Ukwuani', 'Uvwie', 'Warri North', 'Warri South',
                'Warri South West'
            ],
            'EB' => [ // Ebonyi
                'Abakaliki', 'Afikpo North', 'Afikpo South', 'Ebonyi', 'Ezza North',
                'Ezza South', 'Ikwo', 'Ishielu', 'Ivo', 'Izzi', 'Ohaozara',
                'Ohaukwu', 'Onicha'
            ],
            'ED' => [ // Edo
                'Akoko-Edo', 'Egor', 'Esan Central', 'Esan North-East', 'Esan South-East',
                'Esan West', 'Etsako Central', 'Etsako East', 'Etsako West',
                'Igueben', 'Ikpoba Okha', 'Oredo', 'Orhionmwon', 'Ovia North-East',
                'Ovia South-West', 'Owan East', 'Owan West', 'Uhunmwonde'
            ],
            'EK' => [ // Ekiti
                'Ado Ekiti', 'Efon', 'Ekiti East', 'Ekiti South-West', 'Ekiti West',
                'Emure', 'Gbonyin', 'Ido Osi', 'Ijero', 'Ikere', 'Ikole',
                'Ilejemeje', 'Irepodun/Ifelodun', 'Ise/Orun', 'Moba', 'Oye'
            ],
            'EN' => [ // Enugu
                'Aninri', 'Awgu', 'Enugu East', 'Enugu North', 'Enugu South',
                'Ezeagu', 'Igbo Etiti', 'Igbo Eze North', 'Igbo Eze South',
                'Isi Uzo', 'Nkanu East', 'Nkanu West', 'Nsukka', 'Oji River',
                'Udenu', 'Udi', 'Uzo Uwani'
            ],
            'FC' => [ // FCT (already seeded but including for completeness)
                'Abaji', 'Bwari', 'Gwagwalada', 'Kuje', 'Kwali', 'Municipal Area Council'
            ],
            'GO' => [ // Gombe
                'Akko', 'Balanga', 'Billiri', 'Dukku', 'Funakaye', 'Gombe',
                'Kaltungo', 'Kwami', 'Nafada', 'Shongom', 'Yamaltu/Deba'
            ],
            'IM' => [ // Imo
                'Aboh Mbaise', 'Ahiazu Mbaise', 'Ehime Mbano', 'Ezinihitte',
                'Ideato North', 'Ideato South', 'Ihitte/Uboma', 'Ikeduru',
                'Isiala Mbano', 'Isu', 'Mbaitoli', 'Ngor Okpala', 'Njaba',
                'Nkwerre', 'Nwangele', 'Obowo', 'Oguta', 'Ohaji/Egbema',
                'Okigwe', 'Orlu', 'Orsu', 'Oru East', 'Oru West', 'Owerri Municipal',
                'Owerri North', 'Owerri West', 'Unuimo'
            ],
            'JI' => [ // Jigawa
                'Auyo', 'Babura', 'Biriniwa', 'Birnin Kudu', 'Buji', 'Dutse',
                'Gagarawa', 'Garki', 'Gumel', 'Guri', 'Gwaram', 'Gwiwa',
                'Hadejia', 'Jahun', 'Kafin Hausa', 'Kaugama', 'Kazaure',
                'Kiri Kasama', 'Kiyawa', 'Maigatari', 'Malam Madori', 'Miga',
                'Ringim', 'Roni', 'Sule Tankarkar', 'Taura', 'Yankwashi'
            ],
            'KD' => [ // Kaduna
                'Birnin Gwari', 'Chikun', 'Giwa', 'Igabi', 'Ikara', 'Jaba',
                'Jema\'a', 'Kachia', 'Kaduna North', 'Kaduna South', 'Kagarko',
                'Kajuru', 'Kaura', 'Kauru', 'Kubau', 'Kudan', 'Lere',
                'Makarfi', 'Sabon Gari', 'Sanga', 'Soba', 'Zangon Kataf', 'Zaria'
            ],
            'KN' => [ // Kano
                'Ajingi', 'Albasu', 'Bagwai', 'Bebeji', 'Bichi', 'Bunkure',
                'Dala', 'Dambatta', 'Dawakin Kudu', 'Dawakin Tofa', 'Doguwa',
                'Fagge', 'Gabasawa', 'Garko', 'Garun Mallam', 'Gaya', 'Gezawa',
                'Gwale', 'Gwarzo', 'Kabo', 'Kano Municipal', 'Karaye', 'Kibiya',
                'Kiru', 'Kumbotso', 'Kunchi', 'Kura', 'Madobi', 'Makoda',
                'Minjibir', 'Nasarawa', 'Rano', 'Rimin Gado', 'Rogo', 'Shanono',
                'Sumaila', 'Takai', 'Tarauni', 'Tofa', 'Tsanyawa', 'Tudun Wada',
                'Ungogo', 'Warawa', 'Wudil'
            ],
            'KT' => [ // Katsina
                'Bakori', 'Batagarawa', 'Batsari', 'Baure', 'Bindawa', 'Charanchi',
                'Dan Musa', 'Dandume', 'Danja', 'Daura', 'Dutsi', 'Dutsin Ma',
                'Faskari', 'Funtua', 'Ingawa', 'Jibia', 'Kafur', 'Kaita',
                'Kankara', 'Kankia', 'Katsina', 'Kurfi', 'Kusada', 'Mai\'Adua',
                'Malumfashi', 'Mani', 'Mashi', 'Matazu', 'Musawa', 'Rimi',
                'Sabuwa', 'Safana', 'Sandamu', 'Zango'
            ],
            'KE' => [ // Kebbi
                'Aleiro', 'Arewa Dandi', 'Argungu', 'Augie', 'Bagudo', 'Birnin Kebbi',
                'Bunza', 'Dandi', 'Fakai', 'Gwandu', 'Jega', 'Kalgo', 'Koko/Besse',
                'Maiyama', 'Ngaski', 'Sakaba', 'Shanga', 'Suru', 'Wasagu/Danko',
                'Yauri', 'Zuru'
            ],
            'KO' => [ // Kogi
                'Adavi', 'Ajaokuta', 'Ankpa', 'Bassa', 'Dekina', 'Ibaji',
                'Idah', 'Igalamela Odolu', 'Ijumu', 'Kabba/Bunu', 'Kogi',
                'Lokoja', 'Mopa Muro', 'Ofu', 'Ogori/Magongo', 'Okehi',
                'Okene', 'Olamaboro', 'Omala', 'Yagba East', 'Yagba West'
            ],
            'KW' => [ // Kwara
                'Asa', 'Baruten', 'Edu', 'Ekiti', 'Ifelodun', 'Ilorin East',
                'Ilorin South', 'Ilorin West', 'Irepodun', 'Isin', 'Kaiama',
                'Moro', 'Offa', 'Oke Ero', 'Oyun', 'Pategi'
            ],
            'LA' => [ // Lagos (already seeded but including for completeness)
                'Agege', 'Ajeromi-Ifelodun', 'Alimosho', 'Amuwo-Odofin', 'Apapa',
                'Badagry', 'Epe', 'Eti Osa', 'Ibeju-Lekki', 'Ifako-Ijaiye',
                'Ikeja', 'Ikorodu', 'Kosofe', 'Lagos Island', 'Lagos Mainland',
                'Mushin', 'Ojo', 'Oshodi-Isolo', 'Shomolu', 'Surulere'
            ],
            'NA' => [ // Nasarawa
                'Akwanga', 'Awe', 'Doma', 'Karu', 'Keana', 'Keffi', 'Kokona',
                'Lafia', 'Nasarawa', 'Nasarawa Egon', 'Obi', 'Toto', 'Wamba'
            ],
            'NI' => [ // Niger
                'Agaie', 'Agwara', 'Bida', 'Borgu', 'Bosso', 'Chanchaga',
                'Edati', 'Gbako', 'Gurara', 'Katcha', 'Kontagora', 'Lapai',
                'Lavun', 'Magama', 'Mariga', 'Mashegu', 'Mokwa', 'Moya',
                'Paikoro', 'Rafi', 'Rijau', 'Shiroro', 'Suleja', 'Tafa', 'Wushishi'
            ],
            'OG' => [ // Ogun
                'Abeokuta North', 'Abeokuta South', 'Ado-Odo/Ota', 'Egbado North',
                'Egbado South', 'Ewekoro', 'Ifo', 'Ijebu East', 'Ijebu North',
                'Ijebu North East', 'Ijebu Ode', 'Ikenne', 'Imeko Afon',
                'Ipokia', 'Obafemi Owode', 'Odeda', 'Odogbolu', 'Ogun Waterside',
                'Remo North', 'Shagamu'
            ],
            'ON' => [ // Ondo
                'Akoko North-East', 'Akoko North-West', 'Akoko South-West',
                'Akoko South-East', 'Akure North', 'Akure South', 'Ese Odo',
                'Idanre', 'Ifedore', 'Ilaje', 'Ile Oluji/Okeigbo', 'Irele',
                'Odigbo', 'Okitipupa', 'Ondo East', 'Ondo West', 'Ose', 'Owo'
            ],
            'OS' => [ // Osun
                'Atakunmosa East', 'Atakunmosa West', 'Aiyedaade', 'Aiyedire',
                'Boluwaduro', 'Boripe', 'Ede North', 'Ede South', 'Egbedore',
                'Ejigbo', 'Ife Central', 'Ife East', 'Ife North', 'Ife South',
                'Ifedayo', 'Ifelodun', 'Ila', 'Ilesa East', 'Ilesa West',
                'Irepodun', 'Irewole', 'Isokan', 'Iwo', 'Obokun', 'Odo Otin',
                'Ola Oluwa', 'Olorunda', 'Oriade', 'Orolu', 'Osogbo'
            ],
            'OY' => [ // Oyo
                'Afijio', 'Akinyele', 'Atiba', 'Atisbo', 'Egbeda', 'Ibadan North',
                'Ibadan North-East', 'Ibadan North-West', 'Ibadan South-East',
                'Ibadan South-West', 'Ibarapa Central', 'Ibarapa East',
                'Ibarapa North', 'Ido', 'Irepo', 'Iseyin', 'Itesiwaju',
                'Iwajowa', 'Kajola', 'Lagelu', 'Ogbomoso North', 'Ogbomoso South',
                'Ogo Oluwa', 'Olorunsogo', 'Oluyole', 'Ona Ara', 'Orelope',
                'Ori Ire', 'Oyo East', 'Oyo West', 'Saki East', 'Saki West', 'Surulere'
            ],
            'PL' => [ // Plateau
                'Barkin Ladi', 'Bassa', 'Bokkos', 'Jos East', 'Jos North',
                'Jos South', 'Kanam', 'Kanke', 'Langtang North', 'Langtang South',
                'Mangu', 'Mikang', 'Pankshin', 'Qua\'an Pan', 'Riyom', 'Shendam', 'Wase'
            ],
            'RI' => [ // Rivers
                'Abua/Odual', 'Ahoada East', 'Ahoada West', 'Akuku-Toru', 'Andoni',
                'Asari-Toru', 'Bonny', 'Degema', 'Eleme', 'Emuoha', 'Etche',
                'Gokana', 'Ikwerre', 'Khana', 'Obio/Akpor', 'Ogba/Egbema/Ndoni',
                'Ogu/Bolo', 'Okrika', 'Omuma', 'Opobo/Nkoro', 'Oyigbo',
                'Port Harcourt', 'Tai'
            ],
            'SO' => [ // Sokoto
                'Binji', 'Bodinga', 'Dange Shuni', 'Gada', 'Goronyo', 'Gudu',
                'Gwadabawa', 'Illela', 'Isa', 'Kebbe', 'Kware', 'Rabah',
                'Sabon Birni', 'Shagari', 'Silame', 'Sokoto North', 'Sokoto South',
                'Tambuwal', 'Tangaza', 'Tureta', 'Wamako', 'Wurno', 'Yabo'
            ],
            'TA' => [ // Taraba
                'Ardo Kola', 'Bali', 'Donga', 'Gashaka', 'Gassol', 'Ibi',
                'Jalingo', 'Karim Lamido', 'Kurmi', 'Lau', 'Sardauna',
                'Takum', 'Ussa', 'Wukari', 'Yorro', 'Zing'
            ],
            'YO' => [ // Yobe
                'Bade', 'Bursari', 'Damaturu', 'Fika', 'Fune', 'Geidam',
                'Gujba', 'Gulani', 'Jakusko', 'Karasuwa', 'Machina', 'Nangere',
                'Nguru', 'Potiskum', 'Tarmuwa', 'Yunusari', 'Yusufari'
            ],
            'ZA' => [ // Zamfara
                'Anka', 'Bakura', 'Birnin Magaji/Kiyaw', 'Bukkuyum', 'Bungudu',
                'Gummi', 'Gusau', 'Kaura Namoda', 'Maradun', 'Maru', 'Shinkafi',
                'Talata Mafara', 'Tsafe', 'Zurmi'
            ]
        ];
    }
}