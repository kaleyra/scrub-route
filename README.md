Run php create.php for create file based on country level
create file name country-{country-code}.txt
Ex: {"cid":"1","code":91,"match":5,"srs":{"97400":{"nid":"813","lid":""},"93":{"nid":"805","lid":""}}}


Finding network :
$scrub = Scrub::index($info['code'], $info['national']);
it will return an array of network id and location id if availaiblee