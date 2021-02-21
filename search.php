<?php

function get_page($page_number, $params) {
    
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, 'https://airports.euroga.org/search.php?icao='.$params['icao'].'&country='.$params['country'].'&type=report&date_from_pick='.$params['date_from_pick'].'&date_from='.$params['date_from'].'&date_to_pick='.$params['date_to_pick'].'&date_to='.$params['date_to'].'&runway_condition='.$params['runway_condition'].'&max_landing_fee='.$params['max_landing_fee'].'&all='.$params['all'].'&page=' . $page_number);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

    $headers = array();
    $headers[] = 'Authority: airports.euroga.org';
    $headers[] = 'User-Agent: EuroGA-Airports-To-JSON Script';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $html = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Error: ' . curl_error($ch);
        curl_close($ch);
        die();
    }
    curl_close($ch);

    preg_match_all('/record.php\?id=([0-9]+)".*search-results-cell-name">\n(.*)\n.*search-results-cell-photo">\n(.*)\n.*search-results-cell-date">\n([0-9]{4}-[0-9]{2}-[0-9]{2})/sUm', $html, $matched);
    $matched[3] = preg_replace('/<\/div>/', 'no', $matched[3]);
    $matched[3] = preg_replace('/^((?!no).)*$/', 'yes', $matched[3]);
    $matched = array_slice($matched, 1);

    $records = [];

    if (!empty($matched)) for ($i = 0; $i < count($matched[0]); $i++) {
        $records[] = (object) [
            'id' => $matched[0][$i],
            'airport' => $matched[1][$i],
            'image' => $matched[2][$i],
            'date' => $matched[3][$i],
        ];
    }

    preg_match('/page=([0-9]+)">Last/', $html, $matched);

    return empty($matched) ? [$records, 1] : [$records, $matched[1]];
}

$params = [
    'icao' => $_GET['icao'],
    'country' => $_GET['country'],
    'date_from_pick' => $_GET['date_from_pick'],
    'date_from' => $_GET['date_from'],
    'date_to_pick' => $_GET['date_to_pick'],
    'date_to' => $_GET['date_to'],
    'runway_condition' => $_GET['runway_condition'],
    'max_landing_fee' => $_GET['max_landing_fee'],
    'all' => $_GET['all']
];

$all_records = [];

$current_page = 1;
$last_page = 1;

while ($current_page <= $last_page) {
    [$records, $last_page] = get_page($current_page++, $params);
    $all_records = array_merge($all_records, $records);
}

$all_records = ['records' => $all_records];

header('Content-Type: application/json');
echo json_encode($all_records, JSON_PRETTY_PRINT);