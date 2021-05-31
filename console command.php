// This is a console command, what parts do you think should be refactored and how?
// No need to code them just explaining the helpers or classes that you will use is ok
<?php
public function runForTenant(Tenant $tenant)
{
    if(Tenant::current()->hasModule('car_sharing')) {
        $this->info("Start getting information for all cars with hardware.");

        
          
        /*
          we can make ready() function to hide any details here to keep code simple 
           so, we will put this scope in  Vehicle model
            public function scopeReady($query)
            {
                return $query->whereNotNull('hardware_id');
            }
        */
        
        //before
        // $vehicles = Vehicle::whereNotNull('hardware_id')->get();

        
        //after
          $vehicles = Vehicle::ready()->get();

      



        foreach($vehicles as $vehicle){
            $manager = new Manager();
            $details = $manager->getAdapter()->getCarDetails($vehicle);


            //update location + location history
            if($vehicle->latitude != $details['position']['lat'] || $vehicle->longitude != $details['position']['lon']) {
                //update the vehicle
                $vehicle->latitude = $details['position']['lat'];
                $vehicle->longitude = $details['position']['lon'];

                //make a location history log
                $history = new VehicleLocationHistory();
                $history->vehicle_id = $vehicle->id;
                $history->latitude = $vehicle->latitude;
                $history->longitude = $vehicle->longitude;
                $history->save();
            }

            //update odometer
            $vehicle->odometer = $details['mileage'];

            //update fuel level
            $vehicle->fuel_level = $this->calculateFuelLevel($details['fuel_level']);

            if($details['ignition'] == "on") { $engineOn = true; } else { $engineOn = false; }
            if($details['central_lock'] == "locked") { $doorsLocked = true; } else { $doorsLocked = false; }
            if($details['immobilizer'] == "locked") { $immobilizer = true; } else { $immobilizer = false; }

            $vehicle->engine_on = $engineOn;
            $vehicle->doors_locked = $doorsLocked;
            $vehicle->immobilizer = $immobilizer;

            //save all data
            $vehicle->save();
            $this->info("Vehicle ".$vehicle->id." Updated.");
        }

        $this->info("Vehicle Information updated.");
    }

}
//before
// private function calculateFuelLevel($fuelLevel)
// {
    

//     if($fuelLevel == 100) { return 8; }
//     elseif($fuelLevel >= 87.5) { return 7; }
//     elseif($fuelLevel >= 75) { return 6; }
//     elseif($fuelLevel >= 62.5) { return 5; }
//     elseif($fuelLevel >= 50) { return 4; }
//     elseif($fuelLevel >= 37.5) { return 3; }
//     elseif($fuelLevel >= 25) { return 2; }
//     elseif($fuelLevel >= 12.5) { return 1; }
//     else { return 0; }
// }

/*
    I think switch is more readable and suitable here because all items get the same access time 
    ( it's implemented using a lookup table), but in  if else  takes much more time
     to reach as it 
    has to evaluate every previous condition first.
*/

//after
function calculateFuelLevel($fuelLevel)
{
   
switch ($fuelLevel) {

  case 100  :  return 8; break;
  case 87.5 :  return 7; break;
  case 75   :  return 6; break;
  case 62.5 :  return 5; break;
  case 50   :  return 4; break;  
  case 37.5 :  return 3; break;
  case 25   :  return 2; break;
  case 12.5 :  return 1; break;
  default   :  return 0; 
}
}
