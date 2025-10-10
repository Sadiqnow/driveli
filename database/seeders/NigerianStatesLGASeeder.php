<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\State;
use App\Models\LocalGovernment;
use Illuminate\Support\Facades\DB;

/**
 * Nigerian States and Local Government Areas Seeder
 * 
 * Seeds the database with complete data for all 36 states, FCT, and their LGAs
 * 
 * @package Database\Seeders
 */
class NigerianStatesLGASeeder extends Seeder
{
    /**
     * Complete Nigerian states and LGAs data
     */
    private $statesLGAs = [
        'Abia' => [
            'code' => 'AB',
            'lgas' => [
                'Aba North', 'Aba South', 'Arochukwu', 'Bende', 'Ikwuano',
                'Isiala Ngwa North', 'Isiala Ngwa South', 'Isuikwuato', 'Obi Ngwa',
                'Ohafia', 'Osisioma', 'Ugwunagbo', 'Ukwa East', 'Ukwa West',
                'Umuahia North', 'Umuahia South', 'Umu Nneochi'
            ]
        ],
        'Adamawa' => [
            'code' => 'AD',
            'lgas' => [
                'Demsa', 'Fufure', 'Ganye', 'Gayuk', 'Gombi', 'Grie', 'Hong',
                'Jada', 'Lamurde', 'Madagali', 'Maiha', 'Mayo Belwa', 'Michika',
                'Mubi North', 'Mubi South', 'Numan', 'Shelleng', 'Song',
                'Toungo', 'Yola North', 'Yola South'
            ]
        ],
        'Akwa Ibom' => [
            'code' => 'AK',
            'lgas' => [
                'Abak', 'Eastern Obolo', 'Eket', 'Esit Eket', 'Essien Udim',
                'Etim Ekpo', 'Etinan', 'Ibeno', 'Ibesikpo Asutan', 'Ibiono-Ibom',
                'Ika', 'Ikono', 'Ikot Abasi', 'Ikot Ekpene', 'Ini', 'Itu',
                'Mbo', 'Mkpat-Enin', 'Nsit-Atai', 'Nsit-Ibom', 'Nsit-Ubium',
                'Obot Akara', 'Okobo', 'Onna', 'Oron', 'Oruk Anam', 'Udung-Uko',
                'Ukanafun', 'Uruan', 'Urue-Offong/Oruko', 'Uyo'
            ]
        ],
        'Anambra' => [
            'code' => 'AN',
            'lgas' => [
                'Aguata', 'Anambra East', 'Anambra West', 'Anaocha', 'Awka North',
                'Awka South', 'Ayamelum', 'Dunukofia', 'Ekwusigo', 'Idemili North',
                'Idemili South', 'Ihiala', 'Njikoka', 'Nnewi North', 'Nnewi South',
                'Ogbaru', 'Onitsha North', 'Onitsha South', 'Orumba North',
                'Orumba South', 'Oyi'
            ]
        ],
        'Bauchi' => [
            'code' => 'BA',
            'lgas' => [
                'Alkaleri', 'Bauchi', 'Bogoro', 'Damban', 'Darazo', 'Dass',
                'Gamawa', 'Ganjuwa', 'Giade', 'Itas/Gadau', 'Jama\'are', 'Katagum',
                'Kirfi', 'Misau', 'Ningi', 'Shira', 'Tafawa Balewa', 'Toro',
                'Warji', 'Zaki'
            ]
        ],
        'Bayelsa' => [
            'code' => 'BY',
            'lgas' => [
                'Brass', 'Ekeremor', 'Kolokuma/Opokuma', 'Nembe', 'Ogbia',
                'Sagbama', 'Southern Ijaw', 'Yenagoa'
            ]
        ],
        'Benue' => [
            'code' => 'BN',
            'lgas' => [
                'Ado', 'Agatu', 'Apa', 'Buruku', 'Gboko', 'Guma', 'Gwer East',
                'Gwer West', 'Katsina-Ala', 'Konshisha', 'Kwande', 'Logo',
                'Makurdi', 'Obi', 'Ogbadibo', 'Ohimini', 'Oju', 'Okpokwu',
                'Otukpo', 'Tarka', 'Ukum', 'Ushongo', 'Vandeikya'
            ]
        ],
        'Borno' => [
            'code' => 'BO',
            'lgas' => [
                'Abadam', 'Askira/Uba', 'Bama', 'Bayo', 'Biu', 'Chibok',
                'Damboa', 'Dikwa', 'Gubio', 'Guzamala', 'Gwoza', 'Hawul',
                'Jere', 'Kaga', 'Kala/Balge', 'Konduga', 'Kukawa', 'Kwaya Kusar',
                'Mafa', 'Magumeri', 'Maiduguri', 'Marte', 'Mobbar', 'Monguno',
                'Ngala', 'Nganzai', 'Shani'
            ]
        ],
        'Cross River' => [
            'code' => 'CR',
            'lgas' => [
                'Abi', 'Akamkpa', 'Akpabuyo', 'Bakassi', 'Bekwarra', 'Biase',
                'Boki', 'Calabar Municipal', 'Calabar South', 'Etung', 'Ikom',
                'Obanliku', 'Obubra', 'Obudu', 'Odukpani', 'Ogoja', 'Yakurr', 'Yala'
            ]
        ],
        'Delta' => [
            'code' => 'DE',
            'lgas' => [
                'Aniocha North', 'Aniocha South', 'Bomadi', 'Burutu', 'Ethiope East',
                'Ethiope West', 'Ika North East', 'Ika South', 'Isoko North',
                'Isoko South', 'Ndokwa East', 'Ndokwa West', 'Okpe', 'Oshimili North',
                'Oshimili South', 'Patani', 'Sapele', 'Udu', 'Ughelli North',
                'Ughelli South', 'Ukwuani', 'Uvwie', 'Warri North', 'Warri South',
                'Warri South West'
            ]
        ],
        'Ebonyi' => [
            'code' => 'EB',
            'lgas' => [
                'Abakaliki', 'Afikpo North', 'Afikpo South', 'Ebonyi', 'Ezza North',
                'Ezza South', 'Ikwo', 'Ishielu', 'Ivo', 'Izzi', 'Ohaozara', 'Ohaukwu', 'Onicha'
            ]
        ],
        'Edo' => [
            'code' => 'ED',
            'lgas' => [
                'Akoko-Edo', 'Egor', 'Esan Central', 'Esan North-East', 'Esan South-East',
                'Esan West', 'Etsako Central', 'Etsako East', 'Etsako West', 'Igueben',
                'Ikpoba Okha', 'Oredo', 'Orhionmwon', 'Ovia North-East', 'Ovia South-West',
                'Owan East', 'Owan West', 'Uhunmwonde'
            ]
        ],
        'Ekiti' => [
            'code' => 'EK',
            'lgas' => [
                'Ado Ekiti', 'Efon', 'Ekiti East', 'Ekiti South-West', 'Ekiti West',
                'Emure', 'Gbonyin', 'Ido Osi', 'Ijero', 'Ikere', 'Ikole', 'Ilejemeji',
                'Irepodun/Ifelodun', 'Ise/Orun', 'Moba', 'Oye'
            ]
        ],
        'Enugu' => [
            'code' => 'EN',
            'lgas' => [
                'Aninri', 'Awgu', 'Enugu East', 'Enugu North', 'Enugu South',
                'Ezeagu', 'Igbo Etiti', 'Igbo Eze North', 'Igbo Eze South', 'Isi Uzo',
                'Nkanu East', 'Nkanu West', 'Nsukka', 'Oji River', 'Udenu', 'Udi', 'Uzo Uwani'
            ]
        ],
        'FCT' => [
            'code' => 'FC',
            'lgas' => [
                'Abaji', 'Abuja Municipal', 'Bwari', 'Gwagwalada', 'Kuje', 'Kwali'
            ]
        ],
        'Gombe' => [
            'code' => 'GO',
            'lgas' => [
                'Akko', 'Balanga', 'Billiri', 'Dukku', 'Funakaye', 'Gombe',
                'Kaltungo', 'Kwami', 'Nafada', 'Shongom', 'Yamaltu/Deba'
            ]
        ],
        'Imo' => [
            'code' => 'IM',
            'lgas' => [
                'Aboh Mbaise', 'Ahiazu Mbaise', 'Ehime Mbano', 'Ezinihitte',
                'Ideato North', 'Ideato South', 'Ihitte/Uboma', 'Ikeduru', 'Isiala Mbano',
                'Isu', 'Mbaitoli', 'Ngor Okpala', 'Njaba', 'Nkwerre', 'Nwangele',
                'Obowo', 'Oguta', 'Ohaji/Egbema', 'Okigwe', 'Orlu', 'Orsu',
                'Oru East', 'Oru West', 'Owerri Municipal', 'Owerri North', 'Owerri West', 'Unuimo'
            ]
        ],
        'Jigawa' => [
            'code' => 'JI',
            'lgas' => [
                'Auyo', 'Babura', 'Biriniwa', 'Birnin Kudu', 'Buji', 'Dutse',
                'Gagarawa', 'Garki', 'Gumel', 'Guri', 'Gwaram', 'Gwiwa',
                'Hadejia', 'Jahun', 'Kafin Hausa', 'Kazaure', 'Kiri Kasma',
                'Kiyawa', 'Kaugama', 'Maigatari', 'Malam Madori', 'Miga', 'Ringim',
                'Roni', 'Sule Tankarkar', 'Taura', 'Yankwashi'
            ]
        ],
        'Kaduna' => [
            'code' => 'KD',
            'lgas' => [
                'Birnin Gwari', 'Chikun', 'Giwa', 'Igabi', 'Ikara', 'Jaba',
                'Jema\'a', 'Kachia', 'Kaduna North', 'Kaduna South', 'Kagarko',
                'Kajuru', 'Kaura', 'Kauru', 'Kubau', 'Kudan', 'Lere', 'Makarfi',
                'Sabon Gari', 'Sanga', 'Soba', 'Zangon Kataf', 'Zaria'
            ]
        ],
        'Kano' => [
            'code' => 'KN',
            'lgas' => [
                'Ajingi', 'Albasu', 'Bagwai', 'Bebeji', 'Bichi', 'Bunkure',
                'Dala', 'Dambatta', 'Dawakin Kudu', 'Dawakin Tofa', 'Doguwa',
                'Fagge', 'Gabasawa', 'Garko', 'Garun Mallam', 'Gaya', 'Gezawa',
                'Gwale', 'Gwarzo', 'Kabo', 'Kano Municipal', 'Karaye', 'Kibiya',
                'Kiru', 'Kumbotso', 'Kunchi', 'Kura', 'Madobi', 'Makoda',
                'Minjibir', 'Nasarawa', 'Rano', 'Rimin Gado', 'Rogo', 'Shanono',
                'Sumaila', 'Takai', 'Tarauni', 'Tofa', 'Tsanyawa', 'Tudun Wada',
                'Ungogo', 'Warawa', 'Wudil'
            ]
        ],
        'Katsina' => [
            'code' => 'KT',
            'lgas' => [
                'Bakori', 'Batagarawa', 'Batsari', 'Baure', 'Bindawa', 'Charanchi',
                'Dandume', 'Danja', 'Dan Musa', 'Daura', 'Dutsi', 'Dutsin Ma',
                'Faskari', 'Funtua', 'Ingawa', 'Jibia', 'Kafur', 'Kaita', 'Kankara',
                'Kankia', 'Katsina', 'Kurfi', 'Kusada', 'Mai\'Adua', 'Malumfashi',
                'Mani', 'Mashi', 'Matazu', 'Musawa', 'Rimi', 'Sabuwa', 'Safana',
                'Sandamu', 'Zango'
            ]
        ],
        'Kebbi' => [
            'code' => 'KB',
            'lgas' => [
                'Aleiro', 'Arewa Dandi', 'Argungu', 'Augie', 'Bagudo', 'Birnin Kebbi',
                'Bunza', 'Dandi', 'Fakai', 'Gwandu', 'Jega', 'Kalgo', 'Koko/Besse',
                'Maiyama', 'Ngaski', 'Sakaba', 'Shanga', 'Suru', 'Wasagu/Danko',
                'Yauri', 'Zuru'
            ]
        ],
        'Kogi' => [
            'code' => 'KG',
            'lgas' => [
                'Adavi', 'Ajaokuta', 'Ankpa', 'Bassa', 'Dekina', 'Ibaji',
                'Idah', 'Igalamela Odolu', 'Ijumu', 'Kabba/Bunu', 'Kogi', 'Lokoja',
                'Mopa Muro', 'Ofu', 'Ogori/Magongo', 'Okehi', 'Okene', 'Olamaboro',
                'Omala', 'Yagba East', 'Yagba West'
            ]
        ],
        'Kwara' => [
            'code' => 'KW',
            'lgas' => [
                'Asa', 'Baruten', 'Edu', 'Ekiti', 'Ifelodun', 'Ilorin East',
                'Ilorin South', 'Ilorin West', 'Irepodun', 'Isin', 'Kaiama',
                'Moro', 'Offa', 'Oke Ero', 'Oyun', 'Pategi'
            ]
        ],
        'Lagos' => [
            'code' => 'LA',
            'lgas' => [
                'Agege', 'Ajeromi-Ifelodun', 'Alimosho', 'Amuwo-Odofin', 'Apapa',
                'Badagry', 'Epe', 'Eti-Osa', 'Ibeju-Lekki', 'Ifako-Ijaiye',
                'Ikeja', 'Ikorodu', 'Kosofe', 'Lagos Island', 'Lagos Mainland',
                'Mushin', 'Ojo', 'Oshodi-Isolo', 'Shomolu', 'Surulere'
            ]
        ],
        'Nasarawa' => [
            'code' => 'NA',
            'lgas' => [
                'Akwanga', 'Awe', 'Doma', 'Karu', 'Keana', 'Keffi', 'Kokona',
                'Lafia', 'Nasarawa', 'Nasarawa Egon', 'Obi', 'Toto', 'Wamba'
            ]
        ],
        'Niger' => [
            'code' => 'NI',
            'lgas' => [
                'Agaie', 'Agwara', 'Bida', 'Borgu', 'Bosso', 'Chanchaga',
                'Edati', 'Gbako', 'Gurara', 'Katcha', 'Kontagora', 'Lapai',
                'Lavun', 'Magama', 'Mariga', 'Mashegu', 'Mokwa', 'Moya',
                'Paikoro', 'Rafi', 'Rijau', 'Shiroro', 'Suleja', 'Tafa', 'Wushishi'
            ]
        ],
        'Ogun' => [
            'code' => 'OG',
            'lgas' => [
                'Abeokuta North', 'Abeokuta South', 'Ado-Odo/Ota', 'Egbado North',
                'Egbado South', 'Ewekoro', 'Ifo', 'Ijebu East', 'Ijebu North',
                'Ijebu North East', 'Ijebu Ode', 'Ikenne', 'Imeko Afon',
                'Ipokia', 'Obafemi Owode', 'Odeda', 'Odogbolu', 'Ogun Waterside',
                'Remo North', 'Shagamu'
            ]
        ],
        'Ondo' => [
            'code' => 'ON',
            'lgas' => [
                'Akoko North-East', 'Akoko North-West', 'Akoko South-West',
                'Akoko South-East', 'Akure North', 'Akure South', 'Ese Odo',
                'Idanre', 'Ifedore', 'Ilaje', 'Ile Oluji/Okeigbo', 'Irele',
                'Odigbo', 'Okitipupa', 'Ondo East', 'Ondo West', 'Ose', 'Owo'
            ]
        ],
        'Osun' => [
            'code' => 'OS',
            'lgas' => [
                'Atakunmosa East', 'Atakunmosa West', 'Aiyedaade', 'Aiyedire',
                'Boluwaduro', 'Boripe', 'Ede North', 'Ede South', 'Ife Central',
                'Ife East', 'Ife North', 'Ife South', 'Egbedore', 'Ejigbo',
                'Ifedayo', 'Ifelodun', 'Ila', 'Ilesa East', 'Ilesa West',
                'Irepodun', 'Irewole', 'Isokan', 'Iwo', 'Obokun', 'Odo Otin',
                'Ola Oluwa', 'Olorunda', 'Oriade', 'Orolu', 'Osogbo'
            ]
        ],
        'Oyo' => [
            'code' => 'OY',
            'lgas' => [
                'Afijio', 'Akinyele', 'Atiba', 'Atisbo', 'Egbeda', 'Ibadan North',
                'Ibadan North-East', 'Ibadan North-West', 'Ibadan South-East',
                'Ibadan South-West', 'Ibarapa Central', 'Ibarapa East',
                'Ibarapa North', 'Ido', 'Irepo', 'Iseyin', 'Itesiwaju', 'Iwajowa',
                'Kajola', 'Lagelu', 'Ogbomoso North', 'Ogbomoso South', 'Ogo Oluwa',
                'Olorunsogo', 'Oluyole', 'Ona Ara', 'Orelope', 'Ori Ire', 'Oyo East',
                'Oyo West', 'Saki East', 'Saki West', 'Surulere'
            ]
        ],
        'Plateau' => [
            'code' => 'PL',
            'lgas' => [
                'Bokkos', 'Barkin Ladi', 'Bassa', 'Jos East', 'Jos North',
                'Jos South', 'Kanam', 'Kanke', 'Langtang North', 'Langtang South',
                'Mangu', 'Mikang', 'Pankshin', 'Qua\'an Pan', 'Riyom', 'Shendam', 'Wase'
            ]
        ],
        'Rivers' => [
            'code' => 'RI',
            'lgas' => [
                'Abua/Odual', 'Ahoada East', 'Ahoada West', 'Akuku-Toru', 'Andoni',
                'Asari-Toru', 'Bonny', 'Degema', 'Eleme', 'Emuoha', 'Etche',
                'Gokana', 'Ikwerre', 'Khana', 'Obio/Akpor', 'Ogba/Egbema/Ndoni',
                'Ogu/Bolo', 'Okrika', 'Omuma', 'Opobo/Nkoro', 'Oyigbo',
                'Port Harcourt', 'Tai'
            ]
        ],
        'Sokoto' => [
            'code' => 'SO',
            'lgas' => [
                'Binji', 'Bodinga', 'Dange Shuni', 'Gada', 'Goronyo', 'Gudu',
                'Gwadabawa', 'Illela', 'Isa', 'Kebbe', 'Kware', 'Rabah',
                'Sabon Birni', 'Shagari', 'Silame', 'Sokoto North', 'Sokoto South',
                'Tambuwal', 'Tangaza', 'Tureta', 'Wamako', 'Wurno', 'Yabo'
            ]
        ],
        'Taraba' => [
            'code' => 'TA',
            'lgas' => [
                'Ardo Kola', 'Bali', 'Donga', 'Gashaka', 'Gassol', 'Ibi',
                'Jalingo', 'Karim Lamido', 'Kumi', 'Lau', 'Sardauna', 'Takum',
                'Ussa', 'Wukari', 'Yorro', 'Zing'
            ]
        ],
        'Yobe' => [
            'code' => 'YO',
            'lgas' => [
                'Bade', 'Bursari', 'Damaturu', 'Fika', 'Fune', 'Geidam',
                'Gujba', 'Gulani', 'Jakusko', 'Karasuwa', 'Machina', 'Nangere',
                'Nguru', 'Potiskum', 'Tarmuwa', 'Yunusari', 'Yusufari'
            ]
        ],
        'Zamfara' => [
            'code' => 'ZA',
            'lgas' => [
                'Anka', 'Bakura', 'Birnin Magaji/Kiyaw', 'Bukkuyum', 'Bungudu',
                'Gummi', 'Gusau', 'Kaura Namoda', 'Maradun', 'Maru', 'Shinkafi',
                'Talata Mafara', 'Chafe', 'Zurmi'
            ]
        ]
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Handle case where command is not set (when called from other places)
        if (!$this->command) {
            echo "🌍 Seeding Nigerian States and Local Government Areas...\n";
        } else {
            $this->command->info('🌍 Seeding Nigerian States and Local Government Areas...');
        }
        
        try {
            DB::beginTransaction();
            
            // Clear existing data if re-seeding
            $refresh = $this->command && $this->command->hasOption('refresh') ? $this->command->option('refresh') : false;
            if ($refresh) {
                if ($this->command) {
                    $this->command->info('🗑️  Clearing existing state and LGA data...');
                } else {
                    echo "🗑️  Clearing existing state and LGA data...\n";
                }
                LocalGovernment::truncate();
                State::truncate();
            }

            $stateCount = 0;
            $lgaCount = 0;

            foreach ($this->statesLGAs as $stateName => $stateData) {
                if ($this->command) {
                    $this->command->info("📍 Processing: {$stateName}");
                } else {
                    echo "📍 Processing: {$stateName}\n";
                }
                
                // Create or update state
                $state = State::firstOrCreate(
                    ['name' => $stateName],
                    [
                        'code' => $stateData['code'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );

                $stateCount++;

                // Create LGAs for this state
                foreach ($stateData['lgas'] as $lgaName) {
                    LocalGovernment::firstOrCreate(
                        [
                            'state_id' => $state->id,
                            'name' => $lgaName
                        ],
                        [
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );

                    $lgaCount++;
                }

                if ($this->command) {
                    $this->command->info("   ✅ Created {$state->name} with " . count($stateData['lgas']) . " LGAs");
                } else {
                    echo "   ✅ Created {$state->name} with " . count($stateData['lgas']) . " LGAs\n";
                }
            }

            DB::commit();

            if ($this->command) {
                $this->command->info('');
                $this->command->info('🎉 Successfully seeded Nigerian location data:');
                $this->command->info("   📊 States: {$stateCount}");
                $this->command->info("   🏘️  LGAs: {$lgaCount}");
                $this->command->info('');
            } else {
                echo "\n🎉 Successfully seeded Nigerian location data:\n";
                echo "   📊 States: {$stateCount}\n";
                echo "   🏘️  LGAs: {$lgaCount}\n\n";
            }

            // Verify data integrity
            $this->verifyData();

        } catch (\Exception $e) {
            DB::rollBack();
            if ($this->command) {
                $this->command->error('❌ Failed to seed states and LGAs: ' . $e->getMessage());
            } else {
                echo "❌ Failed to seed states and LGAs: " . $e->getMessage() . "\n";
            }
            throw $e;
        }
    }

    /**
     * Verify the seeded data integrity
     *
     * @return void
     */
    private function verifyData()
    {
        if ($this->command) {
            $this->command->info('🔍 Verifying data integrity...');
        } else {
            echo "🔍 Verifying data integrity...\n";
        }
        
        $stateCount = State::count();
        $lgaCount = LocalGovernment::count();
        
        // Expected counts
        $expectedStates = 37; // 36 states + FCT
        $expectedLgas = array_sum(array_map(function($state) {
            return count($state['lgas']);
        }, $this->statesLGAs));

        if ($stateCount === $expectedStates && $lgaCount === $expectedLgas) {
            if ($this->command) {
                $this->command->info('✅ Data integrity check passed!');
            } else {
                echo "✅ Data integrity check passed!\n";
            }
        } else {
            if ($this->command) {
                $this->command->warn("⚠️  Data integrity warning:");
                $this->command->warn("   Expected {$expectedStates} states, found {$stateCount}");
                $this->command->warn("   Expected {$expectedLgas} LGAs, found {$lgaCount}");
            } else {
                echo "⚠️  Data integrity warning:\n";
                echo "   Expected {$expectedStates} states, found {$stateCount}\n";
                echo "   Expected {$expectedLgas} LGAs, found {$lgaCount}\n";
            }
        }

        // Check for orphaned LGAs
        $orphanedLgas = LocalGovernment::whereNotIn('state_id', State::pluck('id'))->count();
        if ($orphanedLgas > 0) {
            if ($this->command) {
                $this->command->warn("⚠️  Found {$orphanedLgas} orphaned LGAs without valid state references");
            } else {
                echo "⚠️  Found {$orphanedLgas} orphaned LGAs without valid state references\n";
            }
        }

        // Show top 5 states by LGA count
        $topStates = State::withCount('localGovernments')
            ->orderBy('local_governments_count', 'desc')
            ->limit(5)
            ->get();

        if ($this->command) {
            $this->command->info('🏆 Top 5 states by LGA count:');
            foreach ($topStates as $state) {
                $this->command->info("   {$state->name}: {$state->local_governments_count} LGAs");
            }
        } else {
            echo "🏆 Top 5 states by LGA count:\n";
            foreach ($topStates as $state) {
                echo "   {$state->name}: {$state->local_governments_count} LGAs\n";
            }
        }
    }
}