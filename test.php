<?php

declare(strict_types=1);

use Subster\PhpSdk\DataObjects\CreateCustomerData;
use Subster\PhpSdk\DataObjects\UpdateCustomerData;
use Subster\PhpSdk\SubsterConnector;

require __DIR__.'/vendor/autoload.php';

$token = '1|s7Zo006nl1G8IDFYDchnD7sDZo0z5dPXIh3Xrw4G0213d390';

$client = new SubsterConnector($token, baseUrl: 'https://subster.test/api/v1/');

// $data = CreateCustomerData::from([
//    'email' => 'c.email@gmail.fake',
//    'name' => 'Anton Antonov',
// ]);
//
// $customer = $client->customers()->create($data);

$data = UpdateCustomerData::from([
    'id' => '01945a32-2ffd-73ef-92b4-dae942abb3f6',
    'name' => 'New name',
]);

$customer = $client->customers()->update($data);

var_dump($customer);
