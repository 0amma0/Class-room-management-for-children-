<?php

use App\Models\Classes;
use App\Models\Cluster;
use App\Models\Environment;
use App\Models\Region;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('participant hierarchy filters include all classes in an environment', function () {
    $admin = User::factory()->admin()->create();
    $region = Region::create(['name' => 'West Java', 'code' => 'FILTER-WJ', 'is_active' => true]);
    $cluster = Cluster::create(['region_id' => $region->id, 'name' => 'Cluster Filter', 'code' => 'FILTER-C', 'is_active' => true]);
    $environment = Environment::create(['cluster_id' => $cluster->id, 'name' => 'Community Filter', 'code' => 'FILTER-E', 'is_active' => true]);
    $otherRegion = Region::create(['name' => 'Central Java', 'code' => 'FILTER-CJ', 'is_active' => true]);
    $otherCluster = Cluster::create(['region_id' => $otherRegion->id, 'name' => 'Other Cluster', 'code' => 'FILTER-OC', 'is_active' => true]);
    $otherEnvironment = Environment::create(['cluster_id' => $otherCluster->id, 'name' => 'Other Community', 'code' => 'FILTER-OE', 'is_active' => true]);

    $firstClass = Classes::create(['environment_id' => $environment->id, 'name' => 'Filter Class One', 'code' => 'FILTER-1', 'status' => 'active']);
    $secondClass = Classes::create(['environment_id' => $environment->id, 'name' => 'Filter Class Two', 'code' => 'FILTER-2', 'status' => 'active']);
    $otherClass = Classes::create(['environment_id' => $otherEnvironment->id, 'name' => 'Other Filter Class', 'code' => 'FILTER-3', 'status' => 'active']);
    Student::create(['class_id' => $firstClass->id, 'full_name' => 'Filter Participant One', 'status' => 'active']);
    Student::create(['class_id' => $secondClass->id, 'full_name' => 'Filter Participant Two', 'status' => 'active']);
    Student::create(['class_id' => $otherClass->id, 'full_name' => 'Other Participant', 'status' => 'active']);

    $response = $this->actingAs($admin)->get(route('admin.students', [
        'region_id' => $region->id,
        'cluster_id' => $cluster->id,
        'environment_id' => $environment->id,
    ]));

    $response->assertOk()
        ->assertSee('Filter Participant One')
        ->assertSee('Filter Participant Two')
        ->assertDontSee('Other Participant');
});
