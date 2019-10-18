<?php

namespace App\Contracts;

interface ZoneServiceContract {
    public function getZones();
    public function addZones();
    public function deleteZones();
    public function updateZone();
}
