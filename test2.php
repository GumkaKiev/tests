<?php

// DB Settings
$db_host = 'db';
$db_database = 'db';
$db_user = 'db';
$db_password = 'db';

// Selection settings
$totalOffices = 15; // Max (total) offices for company
$lowerLimitOfPersons = 2;  // Min persons in single office for rand()
$upperLimitOfPersons = 25;  // Max persons in single office for rand()

echo "Maximum offices : $totalOffices<br/>\n";
echo "Persons per office : random from $lowerLimitOfPersons to $upperLimitOfPersons<br/>\n";
echo "<hr>\n";

// Create DB PDO connection
$dsn = "mysql:host=$db_host;dbname=$db_database;charset=UTF8";

try {
    $pdo = new PDO($dsn, $db_user, $db_password);

    if ($pdo) {
        echo "Connected to the $db_database database successfully!<br/>\n";
    }
} catch (PDOException $e) {
    echo $e->getMessage();
    die();
}

// Making DB structure + seeding it
// Used https://jsonplaceholder.typicode.com/users for persons naming
dropTables();
createTables();
fillTables();

// SQL selections and calculations
echo "<hr>\n";

$sql = "select c.name as `company`, o.name as `office`, count(*) as `persons`, sum(p.salary) as office_salary
from `test2_company` c
left join `test2_office` o on o.company_id = c.id
left join `test2_person` p on p.office_id = o.id
group by o.id
order by office_salary desc;";

echo "<b>SELECT Salary_per_office</b><br/>\n";
makeSelection($sql);

$sql = "select c.name, o.name, count(*) as `persons`
from `test2_company` c
left join `test2_office` o on o.company_id = c.id
left join `test2_person` p on p.office_id = o.id
group by o.id
having `persons` > 5 and `persons` < 19
order by `persons` desc;";

echo "<b>SELECT Office persons 5< >19</b><br/>\n";
makeSelection($sql);

$sql = "select c.name, o.name, count(*) as `persons`
from `test2_company` c
left join `test2_office` o on o.company_id = c.id
left join `test2_person` p on p.office_id = o.id
group by o.id
having `persons` > 3 and `persons` < 23
order by `persons` desc;";

echo "<b>SELECT Office persons 3< >23</b><br/>\n";
makeSelection($sql);


function getFakeUsers()
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, "https://jsonplaceholder.typicode.com/users");
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($curl);
    curl_close($curl);
    return json_decode($result, true);
}

function makeSelection($sql)
{
    global $pdo;
    $selection = $pdo->query($sql);
    $rows = $selection->fetchAll(PDO::FETCH_ASSOC);

    if (count($rows) > 0) {

        echo "<table border=1><thead>\n";
        $headers = $rows[0];
        echo "<tr>\n";
        foreach ($headers as $header => $value) {
            echo "<th>$header</th>\n";
        }
        echo "</tr>\n";
        echo "</thead><tbody>\n";
        foreach ($rows as $row) {
            echo "<tr>\n";
            foreach ($row as $name => $value) {
                echo "<td>$value</td>\n";
            }
            echo "</tr>\n";
        }
        echo "</tbody></table>\n";
    } else {
        echo "NO records found!\n";
    }
    echo "<hr>\n";
}

function dropTables()
{
    global $pdo;
    // Drop tables
    echo "<hr>\n";
    $sqlDropTables = [
        'DROP TABLE IF EXISTS `test2_person`;',
        'DROP TABLE IF EXISTS `test2_office`;',
        'DROP TABLE IF EXISTS `test2_company`;'
    ];

    foreach ($sqlDropTables as $sqlDrop) {
        $pdo->exec($sqlDrop);
    }
    echo "Tables dropped successfully!<br/>\n";
}

function createTables()
{
    global $pdo;
    // Create tables
    echo "<hr>\n";
    $sqlCreateTables = [
        'CREATE TABLE `test2_company`(
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `name`  VARCHAR(100) NOT NULL,
        PRIMARY KEY(id)
        ) ENGINE=InnoDB ;',
        'CREATE TABLE `test2_office` (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `company_id` int(10) unsigned NOT NULL,
        `name` varchar(100) NOT NULL,
       PRIMARY KEY (`id`),
        KEY `office_company_id_foreign` (`company_id`),
       CONSTRAINT `office_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `test2_company` (`id`)
        ) ENGINE=InnoDB ;',
        'CREATE TABLE `test2_person` (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `office_id`   int(10) unsigned NOT NULL,
        `name` VARCHAR(100) NOT NULL,
        `salary` int(10),
       PRIMARY KEY (`id`),
       KEY `person_office_id_foreign` (`office_id`),
        CONSTRAINT `person_office_id_foreign` FOREIGN KEY (`office_id`) REFERENCES `test2_office` (`id`)
       ) ENGINE=InnoDB ;'
    ];

    foreach ($sqlCreateTables as $sqlCreate) {
        $pdo->exec($sqlCreate);
    }
    echo "Tables created successfully!<br/>\n";
}

function fillTables()
{
    global $pdo, $totalOffices, $lowerLimitOfPersons, $upperLimitOfPersons;
    // Fill tables
    echo "<hr>\n";
    $pdo->exec("INSERT INTO db.test2_company (id, name) VALUES(1, 'Test Company');");

    for ($officeNumber = 1; $officeNumber <= $totalOffices; $officeNumber++) {
        $sql = "INSERT INTO db.test2_office (id, company_id, name) VALUES ($officeNumber, 1, 'Office $officeNumber');";
        $pdo->exec($sql);

        // Insert office personal
        $maxPersons = rand($lowerLimitOfPersons, $upperLimitOfPersons);
        $persons = 0;

        $personFakeIndex = 0;
        $fakeUsers = getFakeUsers(); // it returned 10 users only!!

        while ($persons < $maxPersons) {
            if ($personFakeIndex == 10) {
                $fakeUsers = getFakeUsers();
                $personFakeIndex = 0;
            }
            $userName = $fakeUsers[$personFakeIndex]['name'];
            $salary = rand(8, 35) * 10;
            $sql = "INSERT INTO db.test2_person (`office_id`, `name`, `salary`) VALUES ($officeNumber, '$userName', $salary);";
            $pdo->exec($sql);

            $personFakeIndex++;
            $persons++;
        }
    }

    echo "Tables filled successfully!<br/>\n";
}
