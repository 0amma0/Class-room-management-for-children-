<?php

use App\Models\Classes;
use App\Models\Cluster;
use App\Models\Environment;
use App\Models\Region;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the community page filters environments through active region and cluster selections', function () {
    $region = Region::create(['name' => 'West Java', 'code' => 'JB', 'is_active' => true]);
    $cluster = Cluster::create(['region_id' => $region->id, 'name' => 'Cluster Selected', 'is_active' => true]);
    $environment = Environment::create(['cluster_id' => $cluster->id, 'name' => 'Community Selected', 'is_active' => true]);
    Classes::create(['environment_id' => $environment->id, 'name' => 'Active Class', 'status' => 'active']);

    $this->get(route('regions', [
        'region_id' => $region->id,
        'cluster_id' => $cluster->id,
        'environment_id' => $environment->id,
    ]))
        ->assertOk()
        ->assertSee('Our Communities in Indonesia')
        ->assertSee('West Java Region')
        ->assertSee('1 active communities · schematic map, not to geographic scale')
        ->assertSee('Community Selected')
        ->assertSee('Active Class');
});
