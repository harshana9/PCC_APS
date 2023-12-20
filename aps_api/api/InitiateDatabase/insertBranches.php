<?php


/**
 * 
 * 
 * 
 * This is just a databse seeder for branch table
 * 
 * 
 * */

//includes
require_once "../../database/dbcon.php";

//Databse Connection
$dbCon = new DbCon();
$conn = $dbCon->getConn();

$name =array('Addalachchenei', 'Akkaraipattu', 'Ampara', 'Kalmunai', 'Karaitivu', 'Mahaoya', 'Maruthamunai', 'Nintavur', 'Pottuvil', 'Sainthamaruthu', 'Sammanthurai', 'Thirukkovil', 'Uhana', 'Anuradhapura', 'Nuwarawewa', 'Eppawala', 'Galenbindunuwewa', 'Galkiriyagama', 'Galnewa', 'Horoupathana', 'Kahatagasdigiliya', 'Kebithigollewa', 'Kekirawa', 'Medawachchiya', 'Meegalewa', 'Nochchiyagama', 'Padaviya', 'Talawa', 'Thambuttegama', 'Badulla', 'Muthiyangana', 'Bandarawela', 'Boralanda', 'Diyatalawa', 'Giradurukotte', 'Haldummulla', 'Haliela', 'Haputale', 'Kandaketiya', 'Keppetiipola', 'Koslanda', 'Lunugala', 'Mahiyangana', 'Passara', 'Uwaparanagama', 'Welimada ', 'Batticaloa', 'Batticaloa Town', 'Chenkalady', 'Eravur', 'Kaluwanchikudy', 'Katankudy', 'Kallar', 'Kokkadicholei', 'Oddamavadi', 'Valachchenai', 'Anamaduwa', 'Chilaw', 'Dankotuwa', 'Kalpitiya', 'Madampe', 'Mahawewa', 'Marawila', 'Nattandiya', 'Puttalam', 'Wennappuwa', 'Central Rd', 'Dam Street', 'Dematagoda', 'Duke Street', 'First City Branch', 'Grandpass', 'Kehelwatta', 'Kotahena', 'Malwatte Rd', 'Mid City', 'Mutwal', 'Olcott Mw.', 'Pettah', 'Sangaraja Maw.', 'Sea Street', 'Bambalapitiya', 'Borella', 'Borella Town(Golden Jubile)', 'Head Quarters', 'Hyde Park Corner', 'Kirillapona', 'Kollupitiya Co-op.House', 'Liberty Plaza', 'Lucky Plaza', 'Maradana', 'Majestic City', 'Narahenpita', 'Suduwella', 'Thimbirigasyaya', 'Town Hall', 'Union Place', 'Ward Place -Premier Branch', 'Wellawatte', 'Awissawella', 'Battaramulla', 'Boralesgamuwa', 'Dehiwala', 'Dehiwala Galle Rd.', 'Gangodawila', 'Hanwella', 'Homagama', 'Kaduwela', 'Katubedda', 'Kesbewa', 'Kolonnawa', 'Kotikawatta', 'Kottawa', 'Maharagama', 'Moratumulla', 'Moratuwa', 'Mount Lavinia', 'Nugegoda', 'Nugegoda City', 'Piliyandala', 'Piliyandala City ', 'Pitakotte', 'Ratmalana', 'Athurugiriya', 'Ahangama', 'Ambalngoda', 'Baddegama', 'Balapitiya', 'Batapola', 'Elpitiya', 'Galle Fort', 'Galle Main', 'Hikkaduwa', 'Imaduwa', 'Karapitiya', 'Koggala', 'Talgaswala', 'Udugama', 'Uragasmanhandiya', 'Wanduramba', 'Delgoda', 'Gampaha', 'Ganemulla', 'Ja-ela', 'Kadawatha', 'Kandana', 'Katunaike', 'Kelaniya', 'Kiribathgoda', 'Kirindiwela', 'Mahara', 'Malwana', 'Maradagahamula', 'Meerigama', 'Minuwangoda', 'Nittambuwa', 'Pamunugama', 'Pugoda', 'Ragama', 'Seeduwa', 'Veyangoda', 'Wattala', 'Yakkala', 'Kochchikade', 'Negombo', 'Ambalantota', 'Angunakolapelessa', 'Beliatta', 'Hambantota', 'Kudawella', 'Middeniya', 'Ranna', 'Suriyawewa', 'Tangalle', 'Tissamaharama', 'Walasmulla', 'Weeraketiya', 'Atchuvely', 'Chankanai', 'Chavakachcheri', 'Chunnakam', 'Kannathiddy', 'J/Main Street', 'J/Stanley Road', 'J/University', 'Tellipalai', 'Kayts', 'Kodikamam', 'Nelliady', 'Point Pedro', 'Velvettithurai', 'Aluthgama', 'Badureliya', 'Bandaragama', 'Beruwala', 'Bulathsinghala', 'Horana', 'Ingiriya', 'Kalutara', 'Maggona', 'Matugama', 'Neboda', 'Panadura', 'Panadura Town', 'Pelawatta', 'Wadduwa', 'Akurana', 'Alawathugoda', 'Ankumbura', 'Daulagala', 'Deltota', 'Galagedara', 'Hataraliyadda', 'Gampola', 'Hasalaka', 'Kadugannawa', 'Kandy', 'Kandy City Centre', 'Katugastota', 'Menikhinna', 'Nawalapitiya', 'Panwila', 'Peradeniya', 'Pilimatalawa', 'Poojapitiya', 'Pussellawa', 'Senkadagala', 'Teldeniya', 'Wattagama', 'Gelioya', 'Aranayaka', 'Bulathkohupitiya', 'Dehiowita', 'Deraniyagala', 'Galigamuwa', 'Gonagaldeniya', 'Hemmathagama', 'Kegalle Main', 'Kegalle Bazzar', 'Kotiyakumbura', 'Mawanella', 'Rambukkana', 'Ruwanwella', 'Thulhiriya', 'Warakapola', 'Yatiyantota', 'Alawwa', 'Bingiriya', 'Galgamuwa', 'Giriulla', 'Hettipola', 'Ibbagamuwa', 'Kobeigane', 'Ethugalpura', 'Kuru-Maliyadewa', 'Kuliyapitiya', 'Kurunagala', 'Maho', 'Makandura', 'Mawathagama', 'Melsiripura', 'Narammala', 'Nikaweratiya', 'Polgahawela', 'Polpitigama ', 'Pothuhera', 'Ridigama', 'Wariyapola', 'Dambulla', 'Galewela', 'Matale', 'Naula', 'Pallepola', 'Raththota', 'Ukuwela', 'Wilgamuwa', 'Akuressa', 'Deniyaya', 'Devinuwara', 'Dickwella', 'Gandara', 'Hakmana', 'Kamburupitiya', 'Matara Dha.Maw', 'Matara Uyanwatta', 'Morawaka', 'Urubokka', 'Walasgala', 'Weligama', 'Badalkumbura', 'Bibila', 'Buttala', 'Kataragama', 'Medagama', 'Monaragala', 'Thanamalwila', 'Wellawaya', 'Siyambalanduwa', 'Bogawantalawa', 'Ginigathhena', 'Hatton', 'Maskeliya', 'Nildandahinna', 'Nuwaraeliya', 'Pundaluoya', 'Ragala', 'Rikillagaskada', 'Talawakele', 'Udapussellawa', 'Hanguranketha', 'Balangoda', 'Eheliyagoda', 'Embilipitiya', 'Godakawela', 'Kahawatta', 'Kalawana', 'Kaltota', 'Kiriella', 'Kuruwita', 'Nivitigala', 'Pallebedda', 'Pelmadulla', 'Rakwana', 'Ratnapura', 'Rathnapura Town', 'Udawalawa', 'Aralaganwila', 'Bakamuna', 'Dehiattakandiya', 'Habarana', 'Hingurakgoda', 'Medirigiriya', 'Polonnaruwa', 'Polonnaruwa T.', 'Thambala (SC Code 923)', 'Welikanda', 'Kantalai', 'Kinniya', 'Muttur', 'Pulmuday', 'Serunuwara', 'Trincomalee', 'Trincomalee T.Br.', 'Chettikulam', 'Kilinochchi', 'Mankulam', 'Mullaitivu', 'Paranthan', 'Murunkan', 'Mannar', 'Vauniya');

$code=array(228, 63, 15, 23, 223, 181, 346, 296, 164, 338, 64, 224, 189, 8, 220, 170, 177, 301, 179, 218, 51, 150, 42, 96, 246, 171, 43, 315, 219, 10, 269, 37, 209, 151, 268, 195, 225, 216, 250, 240, 260, 251, 58, 116, 156, 16, 75, 113, 227, 123, 190, 65, 339, 342, 340, 102, 267, 24, 291, 125, 215, 303, 322, 83, 9, 76, 298, 297, 71, 1, 46, 126, 259, 308, 312, 176, 214, 275, 139, 56, 277, 310, 78, 320, 204, 25, 319, 210, 309, 331, 236, 200, 119, 143, 86, 167, 14, 362, 145, 29, 208, 348, 19, 337, 97, 229, 49, 196, 313, 327, 194, 98, 328, 306, 290, 91, 336, 174, 335, 103, 359, 279, 80, 364, 188, 35, 87, 154, 234, 73, 13, 169, 136, 247, 343, 329, 272, 131, 197, 325, 118, 26, 332, 239, 273, 175, 276, 55, 237, 202, 217, 191, 100, 198, 21, 278, 318, 93, 316, 324, 79, 222, 333, 142, 34, 72, 205, 244, 7, 288, 265, 345, 264, 67, 61, 120, 350, 107, 108, 110, 109, 284, 104, 30, 162, 31, 105, 361, 106, 285, 141, 84, 283, 121, 311, 161, 41, 300, 39, 282, 70, 249, 148, 321, 261, 262, 153, 294, 183, 206, 257, 114, 341, 18, 140, 159, 3, 357, 89, 157, 53, 211, 57, 256, 358, 274, 158, 112, 74, 363, 248, 252, 293, 180, 185, 238, 221, 27, 299, 355, 69, 101, 81, 270, 54, 47, 149, 172, 184, 92, 144, 207, 281, 334, 226, 28, 12, 52, 137, 199, 344, 82, 124, 59, 360, 280, 193, 163, 138, 115, 2, 146, 241, 128, 201, 122, 117, 132, 243, 135, 307, 130, 133, 152, 32, 60, 271, 304, 77, 347, 11, 147, 168, 258, 68, 230, 62, 365, 354, 302, 186, 178, 127, 134, 173, 36, 353, 38, 292, 22, 17, 85, 45, 245, 155, 235, 289, 266, 263, 192, 349, 160, 129, 88, 317, 295, 253, 242, 330, 203, 6, 231, 5, 232, 351, 254, 90, 94, 95, 352, 233, 66, 255, 356, 48, 165, 20, 111, 166, 44, 40);

    //Enter data to database
    try{
        $count=0;
        for ($i=0; $i < count($code); $i++) { 
            $sql = "INSERT INTO `branch`(`branch_name`, `branch_code`) VALUES (:branch_name, :branch_code);";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':branch_name', $name[$i]);
            $stmt->bindParam(':branch_code', $code[$i]);
            $stmt->execute();
            $count += $stmt->rowCount();

        }
        echo $count;
    }
    catch(PDOException $e){
        $msg = $e->getMessage();
        header("Status: 500 Internal Server Error",false,500);
        header("Error: $msg",false,500);
        exit;
    }

?>