<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IpConditionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('ip_condition')->insert([
            // category: 1-Fresh Fruit, 2-Product, 3-Fresh Vegetables, 4-Planting Material, 5-Planting Media, 6-Others,7-Woods Product,8-Fertilizer,9-Animal Feed
            [
                'category' => 1,
                'item_name' => 'CORN',
                'addional_condition' => '<pre>7) The issuance of Phytosanitary Certificate (PC) should be based on the Malaysian Permit Import (IP)  reference number :(IPXXXXX).
8) Subject to quarantine inspection upon arrival in Kota Kinabalu.</pre>',
                'quantity_limit' => null,
                //'date_limit' => null,
                'country' => json_encode(['SMY', 'CN', 'IN', 'PK']),
                'usage' => json_encode(['Fresh Produce', 'For Animal Consumption']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category' => 1,
                'item_name' => 'DURIAN',
                'addional_condition' => '<pre>7) The issuance of Phytosanitary Certificate (PC) should be based on the Malaysian Permit Import (IP)  reference number :(IPXXXXX).
8) Subject to quarantine inspection upon arrival in Kota Kinabalu.</pre>',
                'quantity_limit' => null,
                //'date_limit' => null,
                'country' => json_encode(['SWK', 'SMY']),
                'usage' => json_encode(['Fresh Produce']),
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'category' => 4,
                'item_name' => 'POKOK DURIAN',
                'addional_condition' => '<pre>"7) The issuance of Phytosanitary Certificate (PC) should be based on the Malaysian Permit Import (IP) reference number : (IPXXXX/2025). 
8) Additional Declaration: 
1. All the plants are to be cleansed of any soil.
2. The plants must be healthy and taken from an accredited farm by planting material verification scheme Department of Agriculture (DOA) of the exporting country.
3. All plants are to be dipped in 0.2% Malathion 80 E.C. + 0.4% Thiram + 0.3% Nemacur for 5 minutes (or any suitable insecticide, fungicide and Nematicide) prior to shipment.
4. Subject to quarantine inspection upon arrival in Kota Kinabalu."
</pre>',
                'quantity_limit' => null,
                //'date_limit' => null,
                'country' => json_encode(['SNP']),
                'usage' => json_encode(['Planting Material']),
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'category' => 1,
                'item_name' => 'AVOCADO',
                'addional_condition' => '',
                'quantity_limit' => null,
                //'date_limit' => null,
                'country' => json_encode(['AU', 'KE', 'MX', 'NZ', 'KR', 'SWK', 'ES', 'TR', 'US', 'SMY']),
                'usage' => json_encode(['Planting Material']),
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'category' => 1,
                'item_name' => 'BANANA',
                'addional_condition' => "'7) The issuance of Phytosanitary Certificate (PC) should be based on the Malaysian Permit Import (IP)  reference number :(IPXXXXX).
8) Subject to quarantine inspection upon arrival in Kota Kinabalu.",
                'quantity_limit' => null,
                //'date_limit' => null,
                'country' => json_encode(['SWK', 'SMY']),
                'usage' => json_encode(['Planting Material']),
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'category' => 1,
                'item_name' => 'CITRUS (LEMON, ORANGE, MANDARIN, LIMES, POMELO, GRAPEFRUITS, TANGERINE)',
                'addional_condition' => "IMPORT CONDITION:
1.     Import License is to be sought from the relevant Ministry if required. 
2. A copy of this Import Permit (IP) must send to the consignor.
3. Consignment must be accompanied with:
I.  Import Permit (IP)
II. Phytosanitary Certificate (PC) which has the Malaysian Import Permit (IP) reference number and/or reference number of the quarantine treatment certificate (if related) printed at the additional declaration column. 
III. The quarantine treatment certificate (if related).
4. Treatment must be done prior to export and consignment must be exported within fourteen (14) days from the date of PC issued.
5. Consignment must be inspected and tested according to appropriate official procedures and are considered to be free from soil, pests, diseases, weed seeds, contaminants and regulated articles by National Plant Protection Organization (NPPO) of exporting country.
6. Consignment is subjected to visual inspection, examination or analysis prior to clearance by DOA Sabah Plant Quarantine officer upon arrival at the point entry into Sabah.

ADDITIONAL DECLARATION:
NPPO must include this Additional Declaration in the PC:
I.The issuance of this PC based on the Malaysia IP reference number: IPxxxxxx
II.The citrus must be free from the following quarantine and regulated non quarantine pests:
Insect:
1.Fruit flies species (based on the list of fruit flies present in exporting countries)
Bacteria:
1.Liberobacter asiaticum (Citrus Greening) 
2.Liberobacter africanum (Citrus Greening)

TREATMENT:
Nil

ADDITIONAL TREATMENT FOR PACKAGING MATERIALS:
1.Jute Gunny Sacks

All the Jute Gunny Sack (Packing Material) are to be fumigated with Methyl Bromide at 32 gm per cubic metre for 24 hours at normal temperature (or equivalent) prior to shipment.

2.Bamboo Basket
3.Wood Packaging Material (WPM)

 Any packaging material used based on wood or bamboo must be treated with:
 - Fumigation using Methyl Bromide (MB) at 48g/m3 for duration of 24 hours
 - Heat Treatment at 56℃ for minimum duration 30 continuos minutes
 (Or other approved treatment in accordance with International Standards for Phytosanitary Measures (ISPM) 
 No.15 may also be acceptable)

POST ENTRY REQUIREMENT:
1.DOA Sabah Plant Quarantine officer(s) will take samples at the point entry and send to Post Entry Quarantine Station Kinarut, for screening of pests, diseases and other regulated articles.
2.If any pest, diseases and other regulated articles are present during the post-entry quarantine screening process, Department of Agriculture (DOA) Sabah have the right to suspend future importation, until the cause of the non-compliance is investigated, identified and rectified to the satisfaction of DOA Sabah.
3.All cost incurred during PEQ activities will be borne by importer.

OTHER REQUIREMENT:
The consignment must be accompanied by a Phytosanitary Certificate from the country of origin and re-export Phytosanitary Certificate from the re-exporting country.",
                'quantity_limit' => null,
                //'date_limit' => null,
                'country' => json_encode(['AU', 'CN', 'EG', 'ZA', 'TH', 'US', 'SMY']),
                'usage' => json_encode(['Planting Material']),
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'category' => 1,
                'item_name' => 'CORN/MAIZE (WHOLE FRUIT)',
                'addional_condition' => "'7) The issuance of Phytosanitary Certificate (PC) should be based on the Malaysian Permit Import (IP)  reference number :(IPXXXXX).
8) Subject to quarantine inspection upon arrival in Kota Kinabalu.",
                'quantity_limit' => null,
                //'date_limit' => null,
                'country' => json_encode(['AR', 'CN', 'IN', 'PK', 'SMY']),
                'usage' => json_encode(['Planting Material']),
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'category' => 1,
                'item_name' => 'DATES',
                'addional_condition' => "",
                'quantity_limit' => null,
                //'date_limit' => null,
                'country' => json_encode(['EG', 'IN', 'ZA', 'SMY']),
                'usage' => json_encode(['Planting Material']),
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'category' => 1,
                'item_name' => 'LONGAN',
                'addional_condition' => "'7) The issuance of Phytosanitary Certificate (PC) should be based on the Malaysian Permit Import (IP)  reference number :(IPXXXXX).
8) Subject to quarantine inspection upon arrival in Kota Kinabalu.",
                'quantity_limit' => null,
                //'date_limit' => null,
                'country' => json_encode(['TH', 'VN', 'SMY']),
                'usage' => json_encode(['Planting Material']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category' => 1,
                'item_name' => 'LYCHEE',
                'addional_condition' => "'7) The issuance of Phytosanitary Certificate (PC) should be based on the Malaysian Permit Import (IP)  reference number :(IPXXXXX).
8) Subject to quarantine inspection upon arrival in Kota Kinabalu.",
                'quantity_limit' => null,
                //'date_limit' => null,
                'country' => json_encode(['CN', 'SMY']),
                'usage' => json_encode(['Planting Material']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category' => 1,
                'item_name' => 'MANGO',
                'addional_condition' => "Status Revised Conditions (14042016)
                    Common Name Mango
                    Scientific Name   Mangifera indica
                    Description Form Fresh fruit for consumption

                    Import Condition 1.Import License is to be sought from the relevant Ministry if required.

                    2.A copy of this Import Permit (IP) must be sent to the consignor.

                    3.Consignment must be accompanied with:
                    i.Import Permit (IP)
                    ii.Phytosanitary Certificate (PC) which has the Malaysian Import Permit (IP) reference number and/or reference number of the quarantine treatment certificate (if related) printed at the additional declaration column. 

                    4.Consignment must be inspected and tested according to appropriate official procedures and are considered to be free from soil, pests, diseases, weed seeds contaminants and regulated articles by National Plant Protection Organization (NPPO) of exporting country.

                    5.Consignment is subjected to visual inspection, examination or analysis prior to clearance by Quarantine inspector/officer upon arrival at the point of entry into Sabah, Malaysia.

                    Additional Declaration For CHINA, INDONESIA, MYANMAR, PAKISTAN, PHILIPPINES, SARAWAK, SOUTH AFRICA, SRI LANKA, TAIWAN POC, UNITED STATES, VIETNAM, THAILAND:
                    NPPO must include this Additional Declaration in the PC:
                    i.The issuance of this PC is based on the Malaysia IP reference number: IPxxxxxxxxxxxxx, and

                    ii.The mango fruits were obtained from production area which are free from Mango Seed Weevil (Sternochetus mangiferae).

                    Treatment
                    Nil 
                    Post Entry Requirement
                    1.Quarantine officer(s) will take the samples at the point of entry and send to Plant Quarantine Post Entry Station (PEQ) Kinarut, Sabah, Malaysia for screening of pests, diseases and other regulated articles.

                    2.If any pests, diseases and other regulated articles are present during the post-entry quarantine screening process, Department of Agriculture (DOA) Sabah have the right to suspend future importation, until the cause of the non-compliance is investigated, identified and rectified to the satisfaction of DOA Sabah, Malaysia.

                    3.All cost incurred during PEQ activities will be borne by the importer.

                    Other Requirement All re-export consignment must be accompanied by a PC from the country of origin and re-export PC from the re-exporting country.
",
                'quantity_limit' => null,
                //'date_limit' => null,
                'country' => json_encode(['AU', 'CN', 'ID', 'MM', 'PK', 'PH', 'SWK', 'ZA', 'LK', 'TW', 'TH', 'US', 'VN']),
                'usage' => json_encode(['Planting Material']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category' => 1,
                'item_name' => 'MANGOSTEEN',
                'addional_condition' => "",
                'quantity_limit' => null,
                //'date_limit' => null,
                'country' => json_encode(['AU', 'SMY']),
                'usage' => json_encode(['Planting Material']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category' => 1,
                'item_name' => 'RAMBUTAN',
                'addional_condition' => "'7) The issuance of Phytosanitary Certificate (PC) should be based on the Malaysian Permit Import (IP)  reference number :(IPXXXXX).
8) Subject to quarantine inspection upon arrival in Kota Kinabalu.",
                'quantity_limit' => null,
                //'date_limit' => null,
                'country' => json_encode(['SWK', 'SMY']),
                'usage' => json_encode(['Planting Material']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category' => 3,
                'item_name' => 'BELL PEPPER (CAPSICUM ANNUM)',
                'addional_condition' => "
                **other than the above listed countries, please submit application for consideration of import approval to Plant Biosecurity & Quarantine Division, Department of Agriculture Sabah, Locked Bag,  2050,  Wisma Pertanian Sabah, 88632 KOTA KINABALU.

                Import Condition 
                1.Import License is to be sought from the relevant Ministry if required.
                2.A copy of this Import Permit (IP) must be sent to the consignor.
                3.Consignment must be accompanied with:
                i.Import Permit (IP)
                ii.Phytosanitary Certificate (PC) which has the Malaysian Import Permit (IP) reference number and/or reference number of the quarantine treatment certificate (if related) printed at the additional declaration column. 

                4.Consignment must be inspected and tested according to appropriate official procedures and are considered to be free from soil, pests, diseases, weed seeds contaminants and regulated articles by National Plant Protection Organization (NPPO) of exporting country.
                5.Consignment is subjected to visual inspection, examination or analysis prior to clearance by Quarantine inspector/officer upon arrival at the point of entry into Sabah, Malaysia.



                Additional Declaration ALL COUNTRIES : 
                NPPO must include this Additional Declaration in the PC:
                i.The issuance of this PC is based on the Malaysia IP reference number : IPxxxxxxxxxxxxx
                For THAILAND :
                NPPO must include this Additional Declaration in the PC:
                i.The issuance of this PC is based on the Malaysia IP reference number : IPxxxxxxxxxxxxx
                ii.The fresh fruit must be free from following pests and diseases :
                Insect :
                i. Bactrocera spp.
                Bacteria :
                ii. Xanthomonas vesicatoria (bacterial spot of tomato and pepper)
                Fungi :
                iii. Sclerotinia sclerotiorum (cottony soft rot)

                Treatment Nil
                Post Entry Requirement
                1.Quarantine officer(s) will take the samples of consignment based on symptoms/ signs of infestation at the point of entry and send to Plant Quarantine Post Entry Station (PEQ) Kinarut, Sabah, Malaysia for screening of pests, diseases and other regulated articles.
                2.If any pests, diseases and other regulated articles are present during the post-entry quarantine screening process, Department of Agriculture (DOA) Sabah have the right to suspend future importation, until the cause of the non-compliance is investigated, identified and rectified to the satisfaction of DOA Sabah, Malaysia.
                3.All cost incurred during PEQ activities will be borne by the importer.

                Other Requirement All re-export consignment must be accompanied by a PC from the country of origin and re-export PC from the re-exporting country.
",
                'quantity_limit' => null,
                //'date_limit' => null,
                'country' => json_encode(['AU', 'NL', 'VN']),
                'usage' => json_encode(['Planting Material']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category' => 3,
                'item_name' => 'BIRD EYE CHILI (CAPSICUM ANNUM)',
                'addional_condition' => "",
                'quantity_limit' => null,
                //'date_limit' => null,
                'country' => json_encode(['AU', 'CN', 'JP', 'TH', 'VN']),
                'usage' => json_encode(['Planting Material']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category' => 3,
                'item_name' => 'FRESH GINGER',
                'addional_condition' => "7) The issuance of Phytosanitary Certificate (PC) should be based on the Malaysian Permit Import (IP) reference number : (IPXXXX/2025). 
8) Additional Declaration:
1.  The phytosanitary certificate is to certify that the consignment is being processed and is free from pests and diseases, weed seeds or pathogenic organisms. 
2.  Subject to quarantine inspection upon arrival in Sandakan.",
                'quantity_limit' => null,
                //'date_limit' => null,
                'country' => json_encode(['CN', 'SMY']),
                'usage' => json_encode(['Planting Material']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category' => 3,
                'item_name' => 'FRESH ROUND CABBAGE',
                'addional_condition' => "7) The issuance of Phytosanitary Certificate (PC) should be based on the Malaysian Permit Import (IP) reference number : (IPXXXX/2025). 
8) The PC to declare that the Cabbage is free from any insect, soil and it's taken from an accredited Good Agriculture Practice (GAP) Farm.
9) Subject to quarantine inspection upon arrival in Kota Kinabalu.",
                'quantity_limit' => null,
                //'date_limit' => null,
                'country' => json_encode(['CN']),
                'usage' => json_encode(['Planting Material']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category' => 6,
                'item_name' => 'DRIED CHILIES',
                'addional_condition' => "**other than the above listed countries, please submit application for consideration of import approval to Plant Biosecurity & Quarantine Division, Department of Agriculture Sabah, Locked Bag,  2050,  Wisma Pertanian Sabah, 88632 KOTA KINABALU.

Import Condition 1.Import License is to be sought from the relevant Ministry if required.
2.A copy of this Import Permit (IP) must be sent to the consignor.
3.Consignment must be accompanied with:
i.Import Permit (IP)
ii.Phytosanitary Certificate (PC) which has the Malaysian Import Permit (IP) reference number and/or reference number of the quarantine treatment certificate (if related) printed at the additional declaration column. 
iii.The quarantine treatment certificate (if related)

4.Consignment must be inspected and tested according to appropriate official procedures and are considered to be free from soil, pests, diseases, weed seeds contaminants and regulated articles by National Plant Protection Organization (NPPO) of exporting country.
5.Consignment is subjected to visual inspection, examination or analysis prior to clearance by Quarantine inspector/officer upon arrival at the point of entry into Sabah, Malaysia.



Additional Declaration ALL COUNTRIES : 
NPPO must include this Additional Declaration in the PC:
i.The issuance of this PC is based on the Malaysia IP reference number : IPxxxxxx
For THAILAND :
NPPO must include this Additional Declaration in the PC:
i.The issuance of this PC is based on the Malaysia IP reference number : IPxxxxxxxxxxxxx
ii.The dried chilies must be free from following pests and diseases :
Insect :
i. Bactrocera spp.
Bacteria :
ii. Xanthomonas vesicatoria (bacterial spot of tomato and pepper)
Fungi :
iii. Sclerotinia sclerotiorum (cottony soft rot)

Treatment Nil.

Post Entry Requirement
 1.Quarantine officer(s) will take the samples of consignment based on symptoms/ signs of infestation at the point of entry and send to Plant Quarantine Post Entry Station (PEQ) Kinarut, Sabah, Malaysia for screening of pests, diseases and other regulated articles.
2.If any pests, diseases and other regulated articles are present during the post-entry quarantine screening process, Department of Agriculture (DOA) Sabah have the right to suspend future importation, until the cause of the non-compliance is investigated, identified and rectified to the satisfaction of DOA Sabah, Malaysia.
3.All cost incurred during PEQ activities will be borne by the importer.

Other Requirement All re-export consignment must be accompanied by a PC from the country of origin and re-export PC from the re-exporting country.
",
                'quantity_limit' => null,
                //'date_limit' => null,
                'country' => json_encode(['CN', 'JP', 'TH', 'VN']),
                'usage' => json_encode(['Planting Material']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category' => 6,
                'item_name' => 'DRIED TEA LEAVES',
                'addional_condition' => "",
                'quantity_limit' => null,
                //'date_limit' => null,
                'country' => json_encode(['AR', 'CN', 'IN', 'ID', 'KE', 'PG', 'SWK', 'VN', 'SMY']),
                'usage' => json_encode(['Planting Material']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category' => 6,
                'item_name' => 'FLOUR/STARCH (RICE, TAPIOCA, CORN, WHEAT, CASSAVA)',
                'addional_condition' => "'7) The issuance of Phytosanitary Certificate (PC) should be based on the Malaysian Permit Import (IP)  reference number :(IPXXXXX).
8) Subject to quarantine inspection upon arrival in Kota Kinabalu.",
                'quantity_limit' => null,
                //'date_limit' => null,
                'country' => json_encode(['AU', 'CN', 'DK', 'FI', 'DE', 'HU', 'IN', 'LT', 'NL', 'RO', 'TH', 'US', 'SMY']),
                'usage' => json_encode(['Planting Material']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category' => 6,
                'item_name' => 'LEGUME (GREEN MUNG BEAN,PEANUT, SESAME SEED, RED BEAN/ADZUKI BEAN, GROUNDNUT, YELLOW BAMBOO BEAN, GROUNDNUT, PEANUT) ',
                'addional_condition' => "'7) The issuance of Phytosanitary Certificate (PC) should be based on the Malaysian Permit Import (IP)  reference number :(IPXXXXX).
8) Subject to quarantine inspection upon arrival in Kota Kinabalu.",
                'quantity_limit' => null,
                //'date_limit' => null,
                'country' => json_encode(['AU', 'CA', 'ZA', 'CN', 'EG', 'IN', 'MM', 'NZ', 'SG', 'ZA', 'TH', 'US', 'SMY']),
                'usage' => json_encode(['Planting Material']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category' => 6,
                'item_name' => 'RAW COFFEE BEAN',
                'addional_condition' => "IMPORT CONDITION:
1.  Import License is to be sought from the relevant Ministry if required.
2. A copy of this Import Permit (IP) must sent to the consignor.
3. Consignment must be accompanied with:
I. Import Permit (IP)
II.  Phytosanitary Certificate (PC) which has the Malaysian Import Permit (IP) reference number and/or  reference number of the quarantine treatment certificate (if related) printed at the additional  declaration column. 
III. The quarantine treatment certificate (if related).
4. Treatment must be done prior to export and consignment must be exported within fourteen (14) days from  the date of PC issued.
5. Consignment must be inspected and tasted according to appropriate official procedures and are considered to be free from soil, pests, diseases, weed seeds, contaminants and regulated articles by National Plant Protection Organization (NPPO) of exporting country.
6. Consignment is subjected to visual inspection, examination or analysis prior to clearance by DOA Sabah Plant Quarantine officer upon arrival at the point entry into Sabah.

ADDITIONAL DECLARATION:
NPPO must include this Additional Declaration in the PC:
I.The issuance of this PC based on the Malaysia IP reference number: IPxxxxx

TREATMENT:
Nil

ADDITIONAL TREATMENT FOR PACKAGING MATERIALS:
i.Jute Gunny Sacks

All the Jute Gunny Sack (Packing Material) are to be fumigated with Methyl Bromide at 32 gm per cubic metre for 24 hours at normal temperature (or equivalent) prior to shipment.

ii.Bamboo Basket
iii.Wood Packaging Material (WPM)

 Any packaging material used based on wood or bamboo must be treated with:
 - Fumigation using Methyl Bromide (MB) at 48g/m3 for duration of 24 hours
 - Heat Treatment at 56℃ for minimum duration 30 continuos minutes
 (Or other approved treatment in accordance with International Standards for Phytosanitary Measures (ISPM) No.15
  may also be acceptable)

POST ENTRY REQUIREMENT:
1.DOA Sabah Plant Quarantine officer(s) will take samples at the point entry  and send to Post Entry Quarantine Station Kinarut, for screening of pests, diseases and other regulated articles.
2.If any pest, diseases and other regulated articles are present during the post-entry quarantine screening process, Department of Agriculture (DOA) Sabah have the right to suspend future importation, until the cause of the non-compliance is investigated, identified and rectified to the satisfaction of DOA Sabah.
3.All cost incurred during PEQ activities will be borne by importer.

OTHER REQUIREMENT:
The consignment must be accompanied by a Phytosanitary Certificate from the country of origin and re-export Phytosanitary Certificate from the re-exporting country.",
                'quantity_limit' => null,
                //'date_limit' => null,
                'country' => json_encode(['ID', 'IT', 'SG', 'TW', 'VN', 'SMY']),
                'usage' => json_encode(['Planting Material']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category' => 6,
                'item_name' => 'ROASTED COFFEE BEAN',
                'addional_condition' => "IMPORT CONDITION:
1.  Import License is to be sought from the relevant Ministry if required.
2. A copy of this Import Permit (IP) must sent to the consignor.
3. Consignment must be accompanied with:
I. Import Permit (IP)
II.  Phytosanitary Certificate (PC) which has the Malaysian Import Permit (IP) reference number and/or  reference number of the quarantine treatment certificate (if related) printed at the additional  declaration column. 
III. The quarantine treatment certificate (if related).
4. Treatment must be done prior to export and consignment must be exported within fourteen (14) days from  the date of PC issued.
5. Consignment must be inspected and tasted according to appropriate official procedures and are considered to be free from soil, pests, diseases, weed seeds, contaminants and regulated articles by National Plant Protection Organization (NPPO) of exporting country.
6. Consignment is subjected to visual inspection, examination or analysis prior to clearance by DOA Sabah Plant Quarantine officer upon arrival at the point entry into Sabah.

ADDITIONAL DECLARATION:
NPPO must include this Additional Declaration in the PC:
I.The issuance of this PC based on the Malaysia IP reference number: IPxxxxx

TREATMENT:
Nil

ADDITIONAL TREATMENT FOR PACKAGING MATERIALS:
i.Jute Gunny Sacks

All the Jute Gunny Sack (Packing Material) are to be fumigated with Methyl Bromide at 32 gm per cubic metre for 24 hours at normal temperature (or equivalent) prior to shipment.

ii.Bamboo Basket
iii.Wood Packaging Material (WPM)

 Any packaging material used based on wood or bamboo must be treated with:
 - Fumigation using Methyl Bromide (MB) at 48g/m3 for duration of 24 hours
 - Heat Treatment at 56℃ for minimum duration 30 continuos minutes
 (Or other approved treatment in accordance with International Standards for Phytosanitary Measures (ISPM) No.15
  may also be acceptable)

POST ENTRY REQUIREMENT:
1.DOA Sabah Plant Quarantine officer(s) will take samples at the point entry  and send to Post Entry Quarantine Station Kinarut, for screening of pests, diseases and other regulated articles.
2.If any pest, diseases and other regulated articles are present during the post-entry quarantine screening process, Department of Agriculture (DOA) Sabah have the right to suspend future importation, until the cause of the non-compliance is investigated, identified and rectified to the satisfaction of DOA Sabah.
3.All cost incurred during PEQ activities will be borne by importer.

OTHER REQUIREMENT:
The consignment must be accompanied by a Phytosanitary Certificate from the country of origin and re-export Phytosanitary Certificate from the re-exporting country.",
                'quantity_limit' => null,
                //'date_limit' => null,
                'country' => json_encode(['ID', 'IT', 'SG', 'TW', 'VN', 'SMY']),
                'usage' => json_encode(['Planting Material']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category' => 6,
                'item_name' => 'SOYBEAN',
                'addional_condition' => "'7) The issuance of Phytosanitary Certificate (PC) should be based on the Malaysian Permit Import (IP)  reference number :(IPXXXXX).
8) Subject to quarantine inspection upon arrival in Kota Kinabalu.",
                'quantity_limit' => null,
                //'date_limit' => null,
                'country' => json_encode(['AR', 'AU', 'BE', 'CA', 'IN', 'NZ', 'US', 'SMY']),
                'usage' => json_encode(['Planting Material']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category' => 6,
                'item_name' => 'RICE GRAIN',
                'addional_condition' => "'7) The issuance of Phytosanitary Certificate (PC) should be based on the Malaysian Permit Import (IP)  reference number :(IPXXXXX).
8) Subject to quarantine inspection upon arrival in Kota Kinabalu.",
                'quantity_limit' => null,
                //'date_limit' => null,
                'country' => json_encode(['MM', 'VN', 'SMY']),
                'usage' => json_encode(['Planting Material']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category' => 6,
                'item_name' => 'WHEAT, WHEAT GRAIN',
                'addional_condition' => "IMPORT CONDITION:
1.  Import License is to be sought from the relevant Ministry if required.
2. A copy of this Import Permit (IP) must sent to the consignor.
3. Consignment must be accompanied with:
I. Import Permit (IP)
II.  Phytosanitary Certificate (PC) which has the Malaysian Import Permit (IP) reference number and/or  reference number of the quarantine treatment certificate (if related) printed at the additional  declaration column. 
III. The quarantine treatment certificate (if related).
4. Treatment must be done prior to export and consignment must be exported within fourteen (14) days from  the date of PC issued.
5. Consignment must be inspected and tasted according to appropriate official procedures and are considered to be free from soil, pests, diseases, weed seeds, contaminants and regulated articles by National Plant Protection Organization (NPPO) of exporting country.
6. Consignment is subjected to visual inspection, examination or analysis prior to clearance by DOA Sabah Plant Quarantine officer upon arrival at the point entry into Sabah.

ADDITIONAL DECLARATION:
NPPO must include this Additional Declaration in the PC:
I.The issuance of this PC based on the Malaysia IP reference number: IPxxxxx

TREATMENT:
Nil

ADDITIONAL TREATMENT FOR PACKAGING MATERIALS:
i.Jute Gunny Sacks

All the Jute Gunny Sack (Packing Material) are to be fumigated with Methyl Bromide at 32 gm per cubic metre for 24 hours at normal temperature (or equivalent) prior to shipment.

ii.Bamboo Basket
iii.Wood Packaging Material (WPM)

 Any packaging material used based on wood or bamboo must be treated with:
 - Fumigation using Methyl Bromide (MB) at 48g/m3 for duration of 24 hours
 - Heat Treatment at 56℃ for minimum duration 30 continuos minutes
 (Or other approved treatment in accordance with International Standards for Phytosanitary Measures (ISPM) No.15
  may also be acceptable)

POST ENTRY REQUIREMENT:
1.DOA Sabah Plant Quarantine officer(s) will take samples at the point entry  and send to Post Entry Quarantine Station Kinarut, for screening of pests, diseases and other regulated articles.
2.If any pest, diseases and other regulated articles are present during the post-entry quarantine screening process, Department of Agriculture (DOA) Sabah have the right to suspend future importation, until the cause of the non-compliance is investigated, identified and rectified to the satisfaction of DOA Sabah.
3.All cost incurred during PEQ activities will be borne by importer.

OTHER REQUIREMENT:
The consignment must be accompanied by a Phytosanitary Certificate from the country of origin and re-export Phytosanitary Certificate from the re-exporting country.",
                'quantity_limit' => null,
                //'date_limit' => null,
                'country' => json_encode(['AU', 'CA']),
                'usage' => json_encode(['Planting Material']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category' => 2,
                'item_name' => 'BLOCKBOARD',
                'addional_condition' => "7) The issuance of Phytosanitary Certificate (PC) should be based on the Malaysian Permit Import (IP) reference number : (IPXXXX/2025). 
8) Additional Declaration:
1.  All the consignment are to be fumigated with Methyl Bromide at 48 gm per cubic metre for 24 hours at normal temperature or heat treatment at minimum temperature of 56°C for minimum duration of 30 continuous minutes prior to shipment.
2.  The Consignment must be marked in accordance with the International Plant Protection (IPPC) standards.
3.  Subject to quarantine inspection upon arrival in Kota Kinabalu.",
                'quantity_limit' => null,
                //'date_limit' => null,
                'country' => json_encode(['AR', 'CM', 'CA', 'CN', 'ID', 'SWK', 'US', 'VN', 'SMY']),
                'usage' => json_encode(['Planting Material']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category' => 2,
                'item_name' => 'DOOR',
                'addional_condition' => "",
                'quantity_limit' => null,
                //'date_limit' => null,
                'country' => json_encode(['AR', 'CM', 'CA', 'CN', 'ID', 'SWK', 'US', 'VN', 'SMY']),
                'usage' => json_encode(['Planting Material']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category' => 2,
                'item_name' => 'FINGER WOOD JOINT TIMBER',
                'addional_condition' => "7) The issuance of Phytosanitary Certificate (PC) should be based on the Malaysian Permit Import (IP) reference number : (IPXXXX/2025). 
8) Additional Declaration:
1.  All the consignment are to be fumigated with Methyl Bromide at 48 gm per cubic metre for 24 hours at normal temperature or heat treatment at minimum temperature of 56°C for minimum duration of 30 continuous minutes prior to shipment.
2.  The Consignment must be marked in accordance with the International Plant Protection (IPPC) standards.
3.  Subject to quarantine inspection upon arrival in Kota Kinabalu.",
                'quantity_limit' => null,
                //'date_limit' => null,
                'country' => json_encode(['AR', 'CM', 'CA', 'CN', 'ID', 'SWK', 'US', 'VN', 'SMY']),
                'usage' => json_encode(['Planting Material']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category' => 2,
                'item_name' => 'SAWN TIMBER',
                'addional_condition' => "7) The issuance of Phytosanitary Certificate (PC) should be based on the Malaysian Permit Import (IP) reference number : (IPXXXX/2025). 
8) Additional Declaration:
1.  All the consignment are to be fumigated with Methyl Bromide at 48 gm per cubic metre for 24 hours at normal temperature or heat treatment at minimum temperature of 56°C for minimum duration of 30 continuous minutes prior to shipment.
2.  The Consignment must be marked in accordance with the International Plant Protection (IPPC) standards.
3.  Subject to quarantine inspection upon arrival in Kota Kinabalu.",
                'quantity_limit' => null,
                //'date_limit' => null,
                'country' => json_encode(['AR', 'CM', 'CA', 'CN', 'ID', 'SWK', 'US', 'VN', 'SMY']),
                'usage' => json_encode(['Planting Material']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category' => 2,
                'item_name' => 'TEAK',
                'addional_condition' => "7) The issuance of Phytosanitary Certificate (PC) should be based on the Malaysian Permit Import (IP) reference number : (IPXXXX/2025). 
8) Additional Declaration:
1.  All the consignment are to be fumigated with Methyl Bromide at 48 gm per cubic metre for 24 hours at normal temperature or heat treatment at minimum temperature of 56°C for minimum duration of 30 continuous minutes prior to shipment.
2.  The Consignment must be marked in accordance with the International Plant Protection (IPPC) standards.
3.  Subject to quarantine inspection upon arrival in Kota Kinabalu.",
                'quantity_limit' => null,
                //'date_limit' => null,
                'country' => json_encode(['AR', 'CM', 'CA', 'CN', 'ID', 'SWK', 'US', 'VN', 'SMY']),
                'usage' => json_encode(['Planting Material']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category' => 2,
                'item_name' => 'PLYWOOD',
                'addional_condition' => "7) The issuance of Phytosanitary Certificate (PC) should be based on the Malaysian Permit Import (IP) reference number : (IPXXXX/2025). 
8) Additional Declaration:
1.  All the consignment are to be fumigated with Methyl Bromide at 48 gm per cubic metre for 24 hours at normal temperature or heat treatment at minimum temperature of 56°C for minimum duration of 30 continuous minutes prior to shipment.
2.  The Consignment must be marked in accordance with the International Plant Protection (IPPC) standards.
3.  Subject to quarantine inspection upon arrival in Kota Kinabalu.",
                'quantity_limit' => null,
                //'date_limit' => null,
                'country' => json_encode(['AG', 'CM', 'CA', 'CN', 'ID', 'SWK', 'US', 'VN', 'SMY']),
                'usage' => json_encode(['Planting Material']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category' => 2,
                'item_name' => 'WOOD TIMBER',
                'addional_condition' => "7) The issuance of Phytosanitary Certificate (PC) should be based on the Malaysian Permit Import (IP) reference number : (IPXXXX/2025). 
8) Additional Declaration:
1.  All the consignment are to be fumigated with Methyl Bromide at 48 gm per cubic metre for 24 hours at normal temperature or heat treatment at minimum temperature of 56°C for minimum duration of 30 continuous minutes prior to shipment.
2.  The Consignment must be marked in accordance with the International Plant Protection (IPPC) standards.
3.  Subject to quarantine inspection upon arrival in Kota Kinabalu.",
                'quantity_limit' => null,
                //'date_limit' => null,
                'country' => json_encode(['AR', 'CM', 'CA', 'CN', 'ID', 'SWK', 'US', 'VN', 'SMY']),
                'usage' => json_encode(['Planting Material']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category' => 7,
                'item_name' => 'BROKEN MAIZE',
                'addional_condition' => "",
                'quantity_limit' => null,
                //'date_limit' => null,
                'country' => json_encode(['AR', 'IN', 'PK']),
                'usage' => json_encode(['Planting Material']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category' => 7,
                'item_name' => 'BROKEN RICE',
                'addional_condition' => "IMPORT CONDITION:
1.  Import License is to be sought from the relevant Ministry if required.
2. A copy of this Import Permit (IP) must send to the consignor.
3. Consignment must be accompanied with:
I. Import Permit (IP)
II.  Phytosanitary Certificate (PC) which has the Malaysian Import Permit (IP) reference number and/or  reference  number of the quarantine treatment certificate (if related) printed at the additional declaration column. 
III. The quarantine treatment certificate (if related).
4. Treatment must be done prior to export and consignment must be exported within fourteen (14) days from the date of PC issued.
5. Consignment must be inspected and tasted according to appropriate official procedures and are considered to be free from soil, pests, diseases, weed seeds, contaminants and regulated articles by National Plant Protection Organization (NPPO) of exporting country.
6. Consignment is subjected to visual inspection, examination or analysis prior to clearance by DOA Sabah Plant Quarantine officer upon arrival at the point entry into Sabah.

ADDITIONAL DECLARATION:
NPPO must include this Additional Declaration in the PC:
I.The consignment has been inspected and declare free from soil, pests, diseases, weed seeds, contaminants and  regulated articles.
II.The issuance of this PC based on the Malaysia IP reference number:IP(xxx)and/or treatment certificate   number: xxxxxx
III.The animal feeds (milling, bran & pollard) must be free from the following quarantine pests:

 Insects:
  1. Trogoderma granarium (Khapra beetle country)
       
TREATMENT:
1. Fumigation with methyl bromide (MB) under normal atmospheric pressure (NAP) at 32g/m3 for 24 hours at or above 21oC
 ● Fumigation with MB should not be carried out at temperature below or at 10ºC
 ● The target of the fumigation must not be wrapped in or coated with materials that are impervious to MB.
Or,

2.   Fumigation with Phosphine (PH3) at 5 gm/m3 for 120 hours.

*  All fumigation treatments must be ventilated at or below threshold limit value of the fumigant before export. 
* All treatment must be done by accredited service provider registered by the NPPO of the exporting country.

POST ENTRY REQUIREMENT:
1.DOA Sabah Plant Quarantine officer(s) will take samples at the point entry and send to Post Entry Quarantine Station Kinarut, for screening of pests, diseases and other regulated articles.
2.If any pest, diseases and other regulated articles are present during the post-entry quarantine screening process, Department of Agriculture (DOA) Sabah have the right to suspend future importation, until the cause of the non-compliance is investigated, identified and rectified to the satisfaction of DOA Sabah.
3.All cost incurred during PEQ activities will be borne by importer.

OTHER REQUIREMENT:
The consignment must be accompanied by a Phytosanitary Certificate from the country of origin and re-export Phytosanitary Certificate from the re-exporting country.",
                'quantity_limit' => null,
                //'date_limit' => null,
                'country' => json_encode(['MM', 'SWK', 'VN', 'SMY']),
                'usage' => json_encode(['Planting Material']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category' => 7,
                'item_name' => 'CHAFF (WHEATEN,LUCERNE)',
                'addional_condition' => "IMPORT CONDITION:
1.  Import License is to be sought from the relevant Ministry if required.
2. A copy of this Import Permit (IP) must sent to the consignor.
3. Consignment must be accompanied with:
I. Import Permit (IP)
II.  Phytosanitary Certificate (PC) which has the Malaysian Import Permit (IP) reference number and/or  reference number of the quarantine treatment certificate (if related) printed at the additional  declaration column. 
III. The quarantine treatment certificate (if related).
4. Treatment must be done prior to export and consignment must be exported within fourteen (14) days from  the date of PC issued.
5. Consignment must be inspected and tasted according to appropriate official procedures and are considered to be free from soil, pests, diseases, weed seeds, contaminants and regulated articles by National Plant Protection Organization (NPPO) of exporting country.
6. Consignment is subjected to visual inspection, examination or analysis prior to clearance by DOA Sabah Plant Quarantine officer upon arrival at the point entry into Sabah.

ADDITIONAL DECLARATION:
NPPO must include this Additional Declaration in the PC:
I.The issuance of this PC based on the Malaysia IP reference number: IPxxxxx

TREATMENT:
Nil

ADDITIONAL TREATMENT FOR PACKAGING MATERIALS:
i.Jute Gunny Sacks

All the Jute Gunny Sack (Packing Material) are to be fumigated with Methyl Bromide at 32 gm per cubic metre for 24 hours at normal temperature (or equivalent) prior to shipment.

ii.Bamboo Basket
iii.Wood Packaging Material (WPM)

 Any packaging material used based on wood or bamboo must be treated with:
 - Fumigation using Methyl Bromide (MB) at 48g/m3 for duration of 24 hours
 - Heat Treatment at 56℃ for minimum duration 30 continuos minutes
 (Or other approved treatment in accordance with International Standards for Phytosanitary Measures (ISPM) No.15
  may also be acceptable)

POST ENTRY REQUIREMENT:
1.DOA Sabah Plant Quarantine officer(s) will take samples at the point entry  and send to Post Entry Quarantine Station Kinarut, for screening of pests, diseases and other regulated articles.
2.If any pest, diseases and other regulated articles are present during the post-entry quarantine screening process, Department of Agriculture (DOA) Sabah have the right to suspend future importation, until the cause of the non-compliance is investigated, identified and rectified to the satisfaction of DOA Sabah.
3.All cost incurred during PEQ activities will be borne by importer.

OTHER REQUIREMENT:
The consignment must be accompanied by a Phytosanitary Certificate from the country of origin and re-export Phytosanitary Certificate from the re-exporting country.",
                'quantity_limit' => null,
                //'date_limit' => null,
                'country' => json_encode(['AU', 'US', 'SMY']),
                'usage' => json_encode(['Planting Material']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category' => 7,
                'item_name' => 'CANOLA MEAL',
                'addional_condition' => "7) The issuance of Phytosanitary Certificate (PC) should be based on the Malaysian Permit Import (IP) reference number : (IPXXXX/2025). 
8) Additional Declaration:1. The consignment to be free from contamination with any soil and any weeds seeds. 
2. The consignment to be fumigated with methyl bromide (MB) under normal atmospheric pressure (NAP) at 32g/mᶟ for 24 hours at or above 21°C or fumigated with Phosphine (PH3) at 3g/mᶾ for 120 hours prior to shipment. 
3. Subject to quarantine inspection upon arrival in Kota Kinabalu.",
                'quantity_limit' => null,
                //'date_limit' => null,
                'country' => json_encode(['AU']),
                'usage' => json_encode(['Planting Material']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category' => 7,
                'item_name' => 'CORN GLUTEN MEAL',
                'addional_condition' => "7) The issuance of Phytosanitary Certificate (PC) should be based on the Malaysian Permit Import (IP) reference number : (IPXXXX/2025). 
8) Additional Declaration:1. The consignment to be free from contamination with any soil and any weeds seeds. 
2. The consignment to be fumigated with methyl bromide (MB) under normal atmospheric pressure (NAP) at 32g/mᶟ for 24 hours at or above 21°C or fumigated with Phosphine (PH3) at 3g/mᶾ for 120 hours prior to shipment. 
3. Subject to quarantine inspection upon arrival in Kota Kinabalu.",
                'quantity_limit' => null,
                //'date_limit' => null,
                'country' => json_encode(['AU', 'CN', 'IN', 'NL', 'PK', 'TR', 'US', 'SMY']),
                'usage' => json_encode(['Planting Material']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category' => 7,
                'item_name' => 'DISTILLERS DRIED GRAINS WITH SOLUBLES (DDGS)',
                'addional_condition' => '
                IMPORT CONDITION:
                    1.  Import License is to be sought from the relevant Ministry if required.
                    2. A copy of this Import Permit (IP) must send to the consignor.
                    3. Consignment must be accompanied with:
                    I. Import Permit (IP)
                    II.  Phytosanitary Certificate (PC) which has the Malaysia Import Permit (IP) reference number and/or  reference  number of the quarantine treatment certificate (if related) printed at the additional declaration column. 
                    III. The quarantine treatment certificate (if related).

                    4. Treatment must be done prior to export and consignment must be exported within fourteen (14) days from the date of PC issued.
                    5. Consignment must be inspected and tested according to appropriate official procedures and are considered to be free from soil, pests, diseases, weed seeds, contaminants and regulated articles by National Plant Protection Organization (NPPO) of exporting country.
                    6. Consignment is subjected to visual inspection, examination or analysis prior to clearance by DOA Sabah Plant Quarantine officer upon arrival at the point entry into Sabah.

                    ADDITIONAL DECLARATION:
                    NPPO must include this Additional Declaration in the PC:
                    I.The issuance of this PC based on the Malaysia IP reference number: IP3114/2025

                    TREATMENT:
                    Fumigation with methyl bromide (MB) under normal atmospheric pressure (NAP) at 32g/m3 for 24 hours at or above 21 ℃ or fumigated with Phosphine (PH3) 1.5mg/L for 120 hours specifically for Distillers Dried Grains with Solubles (DDGS) only prior to shipment.

                    ADDITIONAL TREATMENT FOR PACKAGING MATERIALS:
                    a)Jute Gunny Sacks

                    All the Jute Gunny Sack (Packing Material) are to be fumigated with Methyl Bromide at 32 gm per cubic metre for 24 hours at normal temperature (or equivalent) prior to shipment.

                    b)Bamboo Basket
                    c)Wood Packaging Material (WPM)

                    Any packaging material used based on wood or bamboo must be treated with:
                    - Fumigation using Methyl Bromide (MB) at 48g/m3 for duration of 24 hours
                    - Heat Treatment at 56℃ for minimum duration 30 continuos minutes
                    (Or other approved treatment in accordance with International Standards for Phytosanitary Measures (ISPM) No.15
                    may also be acceptable)

                    POST ENTRY REQUIREMENT:
                    1.DOA Sabah Plant Quarantine officer(s) will take samples at the point entry and send to Post Entry Quarantine Station Kinarut,  for screening of pests, diseases and other regulated articles.
                    2.If any pest, diseases and other regulated articles are present during the post-entry quarantine screening process, Department  of Agriculture (DOA) Sabah have the right to suspend future importation, until the cause of the non-compliance is investigated,  identified and rectified to the satisfaction of DOA Sabah.
                    3.All cost incurred during PEQ activities will be borne by importer.

                    OTHER REQUIREMENT:
                    The consignment must be accompanied by a Phytosanitary Certificate from the country of origin and re-export Phytosanitary Certificate from the re-exporting country.
                ',
                'quantity_limit' => null,
                //'date_limit' => null,
                'country' => json_encode(['US']),
                'usage' => json_encode(['Planting Material']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category' => 7,
                'item_name' => 'FISH FEED',
                'addional_condition' => "",
                'quantity_limit' => null,
                //'date_limit' => null,
                'country' => json_encode(['CN', 'TH', 'VN', 'SMY']),
                'usage' => json_encode(['Planting Material']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category' => 7,
                'item_name' => 'HAY (ALFALFA, WHEATEN, OATEN)',
                'addional_condition' => "IMPORT CONDITION:
1.  Import License is to be sought from the relevant Ministry if required.
2. A copy of this Import Permit (IP) must sent to the consignor.
3. Consignment must be accompanied with:
I. Import Permit (IP)
II.  Phytosanitary Certificate (PC) which has the Malaysian Import Permit (IP) reference number and/or  reference number of the quarantine treatment certificate (if related) printed at the additional  declaration column. 
III. The quarantine treatment certificate (if related).
4. Treatment must be done prior to export and consignment must be exported within fourteen (14) days from  the date of PC issued.
5. Consignment must be inspected and tasted according to appropriate official procedures and are considered to be free from soil, pests, diseases, weed seeds, contaminants and regulated articles by National Plant Protection Organization (NPPO) of exporting country.
6. Consignment is subjected to visual inspection, examination or analysis prior to clearance by DOA Sabah Plant Quarantine officer upon arrival at the point entry into Sabah.

ADDITIONAL DECLARATION:
NPPO must include this Additional Declaration in the PC:
I.The issuance of this PC based on the Malaysia IP reference number: IPxxxxx

TREATMENT:
Nil

ADDITIONAL TREATMENT FOR PACKAGING MATERIALS:
i.Jute Gunny Sacks

All the Jute Gunny Sack (Packing Material) are to be fumigated with Methyl Bromide at 32 gm per cubic metre for 24 hours at normal temperature (or equivalent) prior to shipment.

ii.Bamboo Basket
iii.Wood Packaging Material (WPM)

 Any packaging material used based on wood or bamboo must be treated with:
 - Fumigation using Methyl Bromide (MB) at 48g/m3 for duration of 24 hours
 - Heat Treatment at 56℃ for minimum duration 30 continuos minutes
 (Or other approved treatment in accordance with International Standards for Phytosanitary Measures (ISPM) No.15
  may also be acceptable)

POST ENTRY REQUIREMENT:
1.DOA Sabah Plant Quarantine officer(s) will take samples at the point entry  and send to Post Entry Quarantine Station Kinarut, for screening of pests, diseases and other regulated articles.
2.If any pest, diseases and other regulated articles are present during the post-entry quarantine screening process, Department of Agriculture (DOA) Sabah have the right to suspend future importation, until the cause of the non-compliance is investigated, identified and rectified to the satisfaction of DOA Sabah.
3.All cost incurred during PEQ activities will be borne by importer.

OTHER REQUIREMENT:
The consignment must be accompanied by a Phytosanitary Certificate from the country of origin and re-export Phytosanitary Certificate from the re-exporting country.",
                'quantity_limit' => null,
                //'date_limit' => null,
                'country' => json_encode(['AU', 'US', 'SMY']),
                'usage' => json_encode(['Planting Material']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category' => 7,
                'item_name' => 'SESAME MEAL',
                'addional_condition' => "7) The issuance of Phytosanitary Certificate (PC) should be based on the Malaysian Permit Import (IP) reference number : (IPXXXX/2025). 
8) Additional Declaration:1. The consignment to be free from contamination with any soil and any weeds seeds. 
2. The consignment to be fumigated with methyl bromide (MB) under normal atmospheric pressure (NAP) at 32g/mᶟ for 24 hours at or above 21°C or fumigated with Phosphine (PH3) at 3g/mᶾ for 120 hours prior to shipment. 
3. Subject to quarantine inspection upon arrival in Kota Kinabalu.",
                'quantity_limit' => null,
                //'date_limit' => null,
                'country' => json_encode(['PK']),
                'usage' => json_encode(['Planting Material']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category' => 7,
                'item_name' => 'SHIMP FEED',
                'addional_condition' => "",
                'quantity_limit' => null,
                //'date_limit' => null,
                'country' => json_encode(['CN', 'TH', 'VN', 'SMY']),
                'usage' => json_encode(['Planting Material']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category' => 7,
                'item_name' => 'SOYBEAN MEAL',
                'addional_condition' => "7) The issuance of Phytosanitary Certificate (PC) should be based on the Malaysian Permit Import (IP) reference number : (IPXXXX/2025). 
8) Additional Declaration:1. The consignment to be free from contamination with any soil and any weeds seeds. 
2. The consignment to be fumigated with methyl bromide (MB) under normal atmospheric pressure (NAP) at 32g/mᶟ for 24 hours at or above 21°C or fumigated with Phosphine (PH3) at 3g/mᶾ for 120 hours prior to shipment. 
3. Subject to quarantine inspection upon arrival in Kota Kinabalu.",
                'quantity_limit' => null,
                //'date_limit' => null,
                'country' => json_encode(['AR', 'IN', 'PK', 'SG', 'UA', 'US', 'SMY']),
                'usage' => json_encode(['Planting Material']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category' => 7,
                'item_name' => 'WHEAT POLLARD',
                'addional_condition' => "IMPORT CONDITION:
1.  Import License is to be sought from the relevant Ministry if required.
2. A copy of this Import Permit (IP) must sent to the consignor.
3. Consignment must be accompanied with:
I. Import Permit (IP)
II.  Phytosanitary Certificate (PC) which has the Malaysian Import Permit (IP) reference number and/or  reference number of the quarantine treatment certificate (if related) printed at the additional  declaration column. 
III. The quarantine treatment certificate (if related).
4. Treatment must be done prior to export and consignment must be exported within fourteen (14) days from  the date of PC issued.
5. Consignment must be inspected and tasted according to appropriate official procedures and are considered to be free from soil, pests, diseases, weed seeds, contaminants and regulated articles by National Plant Protection Organization (NPPO) of exporting country.
6. Consignment is subjected to visual inspection, examination or analysis prior to clearance by DOA Sabah Plant Quarantine officer upon arrival at the point entry into Sabah.

ADDITIONAL DECLARATION:
NPPO must include this Additional Declaration in the PC:
I.The issuance of this PC based on the Malaysia IP reference number: IPxxxxx

TREATMENT:
Nil

ADDITIONAL TREATMENT FOR PACKAGING MATERIALS:
i.Jute Gunny Sacks

All the Jute Gunny Sack (Packing Material) are to be fumigated with Methyl Bromide at 32 gm per cubic metre for 24 hours at normal temperature (or equivalent) prior to shipment.

ii.Bamboo Basket
iii.Wood Packaging Material (WPM)

 Any packaging material used based on wood or bamboo must be treated with:
 - Fumigation using Methyl Bromide (MB) at 48g/m3 for duration of 24 hours
 - Heat Treatment at 56℃ for minimum duration 30 continuos minutes
 (Or other approved treatment in accordance with International Standards for Phytosanitary Measures (ISPM) No.15
  may also be acceptable)

POST ENTRY REQUIREMENT:
1.DOA Sabah Plant Quarantine officer(s) will take samples at the point entry  and send to Post Entry Quarantine Station Kinarut, for screening of pests, diseases and other regulated articles.
2.If any pest, diseases and other regulated articles are present during the post-entry quarantine screening process, Department of Agriculture (DOA) Sabah have the right to suspend future importation, until the cause of the non-compliance is investigated, identified and rectified to the satisfaction of DOA Sabah.
3.All cost incurred during PEQ activities will be borne by importer.

OTHER REQUIREMENT:
The consignment must be accompanied by a Phytosanitary Certificate from the country of origin and re-export Phytosanitary Certificate from the re-exporting country.",
                'quantity_limit' => null,
                //'date_limit' => null,
                'country' => json_encode(['AU', 'CA', 'JP', 'SMY']),
                'usage' => json_encode(['Planting Material']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category' => 4,
                'item_name' => 'COVER CROP SEED',
                'addional_condition' => "7) The issuance of Phytosanitary Certificate (PC) should be based on the Malaysian Permit Import (IP) reference number : (IPXXXX/2025). 
8) Additional Declaration: 
1. All the seeds including the packing materials are to be fumigated with Methyl Bromide at 32 gm per cubic metre for 2 hours (or heat treatment) prior to shipment.
2. The phytosanitary certificate is to certify that based on examination of the representative samples from each packing, all the seeds including the impurities analysed from the seeds are found not carrying any injurious pests or weed seeds including the Mimosa pigra & Rottboellia exaltata.
3. Subject to quarantine inspection upon arrival in Tawau",
                'quantity_limit' => null,
                //'date_limit' => null,
                'country' => json_encode(['IN', 'ID', 'SMY']),
                'usage' => json_encode(['Planting Material']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category' => 4,
                'item_name' => 'DURIAN SEEDLING',
                'addional_condition' => "'7) The issuance of Phytosanitary Certificate (PC) should be based on the Malaysian Permit Import (IP)  reference number :(IPXXXXX).
8) Subject to quarantine inspection upon arrival in Kota Kinabalu.",
                'quantity_limit' => null,
                //'date_limit' => null,
                'country' => json_encode(['SWK', 'SMY']),
                'usage' => json_encode(['Planting Material']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category' => 4,
                'item_name' => 'JACKFRUIT SEEDLING',
                'addional_condition' => "",
                'quantity_limit' => null,
                //'date_limit' => null,
                'country' => json_encode(['SWK', 'SMY']),
                'usage' => json_encode(['Planting Material']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category' => 4,
                'item_name' => 'OIL PALM',
                'addional_condition' => "7)The issuance of Phytosanitary Certificate (PC) should be based on the Malaysian Permit Import (IP) reference number: (IPXXXX)
8)Additional Declaration:
1. Seeds/Germinated seeds are to be dipped in 0.2% Benomyl or Mancozeb and 0.5% Sodium Hypochlorite for 5 minutes.
2. Phytosanitary Certificate to state that the Seeds are obtained from palm or source free from:
a)   Vascular Wilt (Fusarium oxysporium var.redonlens)
b)   Vascular Wilt (Fusarium oxysporium f.sp elaeidis)
c)   Freekle (Cercospora elaeides)d)   Kiribi disease
e) Lethal yellowing          
f) Tanzania disease 
g)  Marchitez sorpresiva (Phytomonas staheli)
3. Subject to quarantine inspection upon arrival in Kota Kinabalu.",
                'quantity_limit' => null,
                //'date_limit' => null,
                'country' => json_encode(['SWK', 'SMY']),
                'usage' => json_encode(['Planting Material']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category' => 4,
                'item_name' => 'ORCHIDS ',
                'addional_condition' => "7) The issuance of Phytosanitary Certificate (PC) should be based on the Malaysian Permit Import (IP) reference number : (IPXXXX/2025). 
8) Additional Declaration:
1. All the plants are to be bare rooted and free from soil.
2. All plants including planting media (peatmoss/cocopeat/sphagnum) are to be fumigated with Methyl Bromide (100%MB) at 32gm/cubic meter for 2 hours or dipped in 0.2% Malathion 80 E.C. + 0.4% Thiram + 0.3% Nemacur for 5 minutes (or any suitable insecticide, fungicide and Nematicide) prior to shipment.
3. Subject to quarantine inspection upon arrival in Tawau",
                'quantity_limit' => null,
                //'date_limit' => null,
                'country' => json_encode(['CN', 'SWK', 'VN', 'SMY']),
                'usage' => json_encode(['Planting Material']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category' => 4,
                'item_name' => 'PINEAPPLE SUCKER',
                'addional_condition' => "7) The issuance of Phytosanitary Certificate (PC) should be based on the Malaysian Permit Import (IP) reference number : (IPXXXX/2025). 
8) Additional Declaration: 
1. The pineapple suckers must be healthy and taken from an accrediate farm by planting material verification scheme Department of Agriculture (DOA) of the exporting country.    
2. The suckers are to be dipped in 0.2% Thionazin and 0.2% Metalaxyl (or any equivalent Nematicide and Fungicide) for 10 minutes, dried and packed (to aviod damage) prior to shipment.     
3. Subject to Quarantine inspection upon arrival in Kota Kinabalu. Sample to be taken for Post Entry Quarantine (PEQ) screening prior to released of consignment.
4. If  pest (s) and/or disease(s) or regulated articles found during the PEQ screening process, the whole batch of consignment to be confiscate and destroy and DOA Sabah has the right to suspend future importation unitl the cause of non-compliance is being idenfified to the satisfaction of DOA Sabah. All cost incurred during PEQ activites to be borne by the importer.",
                'quantity_limit' => null,
                //'date_limit' => null,
                'country' => json_encode(['SWK', 'SMY']),
                'usage' => json_encode(['Planting Material']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category' => 4,
                'item_name' => 'PISIFERA POLLEN',
                'addional_condition' => "7)The issuance of Phytosanitary Certificate (PC) should be based on the Malaysian Permit Import (IP) reference number: (IPXXXX)
8)Additional Declaration:  
1.  The pollen is to be freeze dried and sealed in ampoule. 
2.  A certificate of origin should be obtained from the producer certifiying the pollen is produced from their seed garden.
3.  Subject to quarantine inspection upon arrival in Kota Kinabalu.",
                'quantity_limit' => null,
                //'date_limit' => null,
                'country' => json_encode(['SMY']),
                'usage' => json_encode(['Planting Material']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category' => 4,
                'item_name' => 'RUZI GRASS',
                'addional_condition' => "",
                'quantity_limit' => null,
                //'date_limit' => null,
                'country' => json_encode(['LA']),
                'usage' => json_encode(['Planting Material']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category' => 4,
                'item_name' => 'SIGNAL GRASS',
                'addional_condition' => "",
                'quantity_limit' => null,
                //'date_limit' => null,
                'country' => json_encode(['AU']),
                'usage' => json_encode(['Planting Material']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category' => 4,
                'item_name' => 'VEGETABLE & FRUIT SEED',
                'addional_condition' => "
7) The issuance of Phytosanitary Certificate (PC) should be based on the Malaysian Permit Import (IP) reference number : (IPXXXX/2025). 
8) Additional Declaration:  
1.  Seeds are to be treated with Thiram at 2 gm per 100 gm seeds (or any suitable fungicide) with dusting method prior to shipment.
2. Subject to quarantine inspection upon arrival in Kota Kinabalu.

",
                'quantity_limit' => null,
                //'date_limit' => null,
                'country' => json_encode(['AU', 'CL', 'DK', 'IT', 'JP', 'ES', 'TH', 'US', 'US', 'SMY']),
                'usage' => json_encode(['Planting Material']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category' => 5,
                'item_name' => 'COCO PEAT',
                'addional_condition' => "7) The issuance of Phytosanitary Certificate (PC) should be based on the Malaysian Permit Import (IP) reference number : (IPXXXX/2025). 
8) Additional Declaration:
1.  The consignment is to be free from contamination with any soil or any weed seeds.
2.  The consignment is to be fumigated with Phosphine gas at 2 gm/cubic metre for 72 hours or Methyl Bromide at the rate of 32 gm/cubic metre for 4 hours at normal atmospheric pressure (NAP) or heat treatment prior to shipment.
3.  Subject to quarantine inspection upon arrival in Kota Kinabalu.",
                'quantity_limit' => null,
                //'date_limit' => null,
                'country' => json_encode(['CA', 'CN', 'DK', 'EE', 'FI', 'DE', 'IN', 'LV', 'LT', 'NL', 'NZ', 'PH', 'PL', 'TW']),
                'usage' => json_encode(['Planting Material']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category' => 5,
                'item_name' => 'PEAT MOSS',
                'addional_condition' => "7) The issuance of Phytosanitary Certificate (PC) should be based on the Malaysian Permit Import (IP) reference number : (IPXXXX/2025). 
8) Additional Declaration:
1.  The consignment is to be free from contamination with any soil or any weed seeds.
2.  The consignment is to be fumigated with Phosphine gas at 2 gm/cubic metre for 72 hours or Methyl Bromide at the rate of 32 gm/cubic metre for 4 hours at normal atmospheric pressure (NAP) or heat treatment prior to shipment.
3.  Subject to quarantine inspection upon arrival in Kota Kinabalu.",
                'quantity_limit' => null,
                //'date_limit' => null,
                'country' => json_encode(['CA', 'CN', 'DK', 'EE', 'FI', 'DE', 'IN', 'LV', 'LT', 'NL', 'NZ', 'PH', 'PL', 'LK', 'TW']),
                'usage' => json_encode(['Planting Material']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category' => 5,
                'item_name' => 'SPHAGNUM',
                'addional_condition' => "7) The issuance of Phytosanitary Certificate (PC) should be based on the Malaysian Permit Import (IP) reference number : (IPXXXX/2025). 
8) Additional Declaration:
1. All the plants are to be bare rooted and free from soil.
2. All plants including planting media (peatmoss/cocopeat/sphagnum) are to be fumigated with Methyl Bromide (100%MB) at 32gm/cubic meter for 2 hours or dipped in 0.2% Malathion 80 E.C. + 0.4% Thiram + 0.3% Nemacur for 5 minutes (or any suitable insecticide, fungicide and Nematicide) prior to shipment.
3. Subject to quarantine inspection upon arrival in Tawau",
                'quantity_limit' => null,
                //'date_limit' => null,
                'country' => json_encode(['CA', 'CN', 'DK', 'EE', 'FI', 'DE', 'IN', 'LV', 'LT', 'NL', 'NZ', 'PH', 'PL', 'LK', 'TW']),
                'usage' => json_encode(['Planting Material']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
