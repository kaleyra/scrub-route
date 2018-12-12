<?php
// create connection
$servername = "172.16.1.219";
$username   = "voice_dev";
$password   = "voicedev@123";
$db         = "dialstreet_voice";

$GLOBALS['table']      = [
                'country'         => 'infi_intl_countries',
                'channel_price'   => 'infi_intl_network_channel_price',
                'networks'        => 'infi_intl_networks',
                'network_series'  => 'infi_intl_network_series',
                'location'        => 'infi_buzz_locations_view'
            ];
