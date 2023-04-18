<?php

// DB Settings
$db_host = 'db';
$db_database = 'db';
$db_user = 'db';
$db_password = 'db';

echo "TESTs 3-6<br/>\n";
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

echo "<b>All database selection</b><br/>\n";
$sql = "select tc.name as `category`, tc.parent_id as `category_parent_id`,
tp.name as `product`, tp.price, tf.name as `feature`, tf.value as `feature_value`
from test3_category tc
left join test3_products tp  on tp.category_id = tc.id
left join test3_features tf  on tf.product_id = tp.id
order by tc.name asc, tp.name asc, tf.name asc;";
makeSelection($sql);

echo "<b>TEST 4: Напишіть запит, який виведе назву категорії, назву та ціну товару, ціна якого є найвищою.</b><br/>\n";
$sql = "select tc.name as `category_name`, tp.name as `product_name`, tp.price
from test3_products tp
left join test3_category tc  on tp.category_id = tc.id
order by tp.price desc limit 1;";
makeSelection($sql);


echo "<b>TEST 5: Напишіть запит, який виведе список, що складається з назви характеристики та кількості товару, який має цю характеристику, відсортований за кількістю від більшого до меншого.</b><br/>\n";
$sql = "select tf.name as `feature_name`, count(*) as `products` from test3_features tf
left join test3_products tp on tp.id = tf.product_id
group by tf.name
order by products desc;";
makeSelection($sql);

echo "<b>TEST 6: Напишіть запит, який виведе список найменувань товару та його ціни, ціна якого знаходиться в межах від 100 до 200 включно і назва категорії закінчується текстом 'ama'</b><br/>\n";
$sql = "select tc.name as `category_name`, tp.name as `product_name`, tp.price from test3_products tp
left join test3_category tc  on tp.category_id = tc.id
where tp.price > 100
and tp.price < 200
and tc.name like '%ama'
order by tp.price desc";
makeSelection($sql);


// =======================================================================================================
function dropTables()
{
    global $pdo;
    // Drop tables
    echo "<hr>\n";
    $sqlDropTables = [
        'DROP TABLE IF EXISTS `test3_features`;',
        'DROP TABLE IF EXISTS `test3_products`;',
        'DROP TABLE IF EXISTS `test3_category`;'
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
        'CREATE TABLE `test3_category`(
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `parent_id` int(10) unsigned NOT NULL DEFAULT 0,
        `name` VARCHAR(100) NOT NULL,
        PRIMARY KEY(id),
        KEY `test3_category_parent_id_IDX` (`parent_id`)
        ) ENGINE=InnoDB ;',

        'CREATE TABLE `test3_products` (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `category_id` int(10) unsigned NOT NULL,
        `name` varchar(100) NOT NULL,
        `price` int(10),
       PRIMARY KEY (`id`),
        KEY `product_category_id_foreign` (`category_id`),
       CONSTRAINT `product_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `test3_category` (`id`)
        ) ENGINE=InnoDB ;',

        'CREATE TABLE `test3_features` (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `product_id`   int(10) unsigned NOT NULL,
        `name`   varchar(100) NOT NULL,
        `value` int(10),
       PRIMARY KEY (`id`),
        KEY `features_product_id_foreign` (`product_id`),
        CONSTRAINT `pf_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `test3_products` (`id`)

       ) ENGINE=InnoDB ;'

    ];

    foreach ($sqlCreateTables as $sqlCreate) {
        echo $sqlCreate."<br/>\n";
        $pdo->exec($sqlCreate);
    }
    echo "Tables created successfully!<br/>\n";
}

function fillTables()
{
    global $pdo;
    // Fill tables
    echo "<hr>\n";
    $sql = "INSERT INTO db.test3_category (id, parent_id, name) VALUES
            (1, 0, 'Category 1'),
            (2, 1, 'Category 2-1'),
            (3, 1, 'Category 3-1'),
            (4, 1, 'Category 4-1'),
            (5, 4, 'Category 5-4 ama'),
            (6, 4, 'Category 6-4');";
    $pdo->exec($sql);

    $sql = "INSERT INTO db.test3_products (id, category_id, name, price) VALUES
             (1, 2, 'Product 1-2', 202),
             (2, 2, 'Product 2-2', 170),
             (3, 2, 'Product 3-2', 195),
             (4, 3, 'Product 4-3', 89),
             (5, 3, 'Product 5-3', 220),
             (6, 3, 'Product 6-3', 125),
             (7, 5, 'Product 7-5', 170),
             (8, 5, 'Product 8-5', 145),
             (9, 5, 'Product 9-5', 135),
             (10, 6, 'Product 10-6', 158),
             (11, 6, 'Product 11-6', 231),
             (12, 6, 'Product 12-6', 301),
             (13, 2, 'Product 13-2', 160),
             (14, 5, 'Product 14-5', 90),
             (15, 5, 'Product 15-5', 160),
             (16, 5, 'Product 16-5', 210),
             (17, 5, 'Product 17-5', 85)
    ";
    $pdo->exec($sql);

    $sql = "INSERT INTO db.test3_features (product_id, name, value) VALUES
            (1, 'Feature 1', 100),(1, 'Feature 2', 40),(1, 'Feature 3', 145),
            (2, 'Feature 2', 80), (2, 'Feature 4', 110),
            (3, 'Feature 1', 80), (3, 'Feature 3', 130), (3, 'Feature 5', 90),
            (4, 'Feature 2', 13), (4, 'Feature 3', 46), (4, 'Feature 4', 120),(4, 'Feature 5', 77),
            (5, 'Feature 2', 34),(5, 'Feature 4', 56),(5, 'Feature 5', 99),
            (6, 'Feature 1', 45), (6, 'Feature 4', 67),
            (7, 'Feature 3', 45), (7, 'Feature 4', 23),
            (8, 'Feature 3', 78), (8, 'Feature 4', 22), (8, 'Feature 5', 110),
            (9, 'Feature 1', 56), (9, 'Feature 3', 34), (9, 'Feature 5', 92),
            (10, 'Feature 1', 44), (10, 'Feature 3', 78), (10, 'Feature 4', 130),(10, 'Feature 5', 100),
            (11, 'Feature 1', 92),(11, 'Feature 3', 34),(11, 'Feature 5', 78),
            (12, 'Feature 2', 45), (12, 'Feature 5', 101),
            (13, 'Feature 1', 68), (13, 'Feature 4', 72), (13, 'Feature 5', 81),
            (14, 'Feature 2', 12), (14, 'Feature 3', 78), (14, 'Feature 4', 91),(14, 'Feature 5', 93),
            (15, 'Feature 1', 43),(15, 'Feature 4', 21),(15, 'Feature 5', 78),
            (16, 'Feature 1', 47), (16, 'Feature 5', 55),
            (17, 'Feature 1', 38), (17, 'Feature 4', 104), (17, 'Feature 5', 200)
    ";
    $pdo->exec($sql);
    echo "Tables filled successfully!<br/>\n";
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

