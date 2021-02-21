<?php

function get_record($id) {
    
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, 'https://airports.euroga.org/record.php?id=' . $id);
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

    preg_match('/<h2>(.*) (.*)<\/h2>.*Submitted by: (.*)<.*\n.*City.*<p>(.*)<\/p>.*Country.*<p>(.*)<\/p>.*Type.*<h5>(.*)<\/h5>.*MTOW.*<h5>(.*)<\/h5>.*Date of visit.*<h5>(.*)<\/h5>.*Parked for.*<h5>(.*)<\/h5>.*Runway condition.*<h5>(.*)<\/h5>.*Taxiway condition.*<h5>(.*)<\/h5>.*Ramp condition.*<h5>(.*)<\/h5>.*Landing fee.*<h5>(.*)<\/h5>.*Parking fee.*<h5>(.*)<\/h5>.*Handling fee.*<h5>(.*)<\/h5>.*Ramp check.*<h5>(.*)<\/h5>.*Handling company.*<h5>(.*)<\/h5>.*Payment method for above.*<h5>(.*)<\/h5>.*Handling mandatory.*<h5>(.*)<\/h5>.*Fuel type.*<h5>(.*)<\/h5>.*Fuel price.*<h5>(.*)<\/h5>.*VAT.*<h5>(.*)<\/h5>.*Payment method.*<h5>(.*)<\/h5>.*Immigration.*<h5>(.*)<\/h5>.*Customs.*<h5>(.*)<\/h5>.*AIP accurate.*<h5>(.*)<\/h5>.*Hangarage.*<h5>(.*)<\/h5>.*Airport website URL.*<h5><a href=".*">(.*)<\/a><\/h5>.*Airport restaurant.*<h5>(.*)<\/h5>.*Transport used.*<h5>(.*)<\/h5>.*Transport to.*<h5>(.*)<\/h5>.*Cost.*<h5>(.*)<\/h5>.*Duration.*<h5>(.*)<\/h5>.*Comments.*<h5.*>(.*)<\/h5>/sUm', $html, $matched_text);
    $matched_text = array_slice($matched_text, 1);
    
    preg_match_all('/href="(images\/[0-9]+\/[0-9]+.[\w]+)\?.*?image_record/', $html, $matched_img);
    $matched_img = array_slice($matched_img, 1);
    $matched_img = $matched_img[0];

    foreach($matched_img as $key => $img) {
        $matched_img[$key] = 'https://airports.euroga.org/' . $img;
    }
    
    $record = (object) (empty($matched_text) ? [] : [
        'icao' => trim($matched_text[0]),
        'airport' => trim($matched_text[1]),
        'author' => trim($matched_text[2]),
        'city' => trim($matched_text[3]),
        'country' => trim($matched_text[4]),
        'type' => trim($matched_text[5]),
        'mtow' => trim($matched_text[6]),
        'date' => trim($matched_text[7]),
        'parked_for' => trim($matched_text[8]),
        'rwy_condition' => trim($matched_text[9]),
        'twy_condition' => trim($matched_text[10]),
        'ramp_condition' => trim($matched_text[11]),
        'landing_fee' => trim($matched_text[12]),
        'parking_fee' => trim($matched_text[13]),
        'handling_fee' => trim($matched_text[14]),
        'ramp_check' => trim($matched_text[15]),
        'handling_company' => trim($matched_text[16]),
        'payment_method' => trim($matched_text[17]),
        'handling_mandatory' => trim($matched_text[18]),
        'fuel_type' => trim($matched_text[19]),
        'fuel_price' => trim($matched_text[20]),
        'vat' => trim($matched_text[21]),
        'payment_method_fuel' => trim($matched_text[22]),
        'immigration' => trim($matched_text[23]),
        'customs' => trim($matched_text[24]),
        'aip_accurate' => trim($matched_text[25]),
        'hangarage' => trim($matched_text[26]),
        'airport_url' => trim($matched_text[27]),
        'airport_restaurant' => trim($matched_text[28]),
        'transport_used' => trim($matched_text[29]),
        'transport_to' => trim($matched_text[30]),
        'cost' => trim($matched_text[31]),
        'duration' => trim($matched_text[32]),
        'comments' => trim($matched_text[33]),
        'images' => $matched_img
    ]);

    return $record;
}

header('Content-Type: application/json');
echo json_encode(get_record($_GET['id']), JSON_PRETTY_PRINT);