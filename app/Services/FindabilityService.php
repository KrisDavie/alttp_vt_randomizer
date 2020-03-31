<?php

namespace ALttP\Services;

use ALttP\Item;
use ALttP\Location;
use ALttP\Support\LocationCollection;
use ALttP\World;
use Illuminate\Support\Facades\Log;

/**
 * Service class to add information about which items can be found WITHOUT each other item.
 * This is a stats project by Hyphen-ated and not actually part of the randomizer.
 * Each place I added code for this I put a comment like //hyphen stats project
 */
class FindabilityService
{
    public function getFindabilities(World $world, $walkthrough = true): array
    {

        $all_items = Item::all($world);                
        $interesting_item_names = [
            'ProgressiveBow', 
            'Hookshot',
            'Mushroom', 
            'Powder', 
            'FireRod', 
            'IceRod', 
            'Bombos', 
            'Ether', 
            'Quake', 
            'Lamp', 
            'Hammer', 
            'OcarinaInactive', 
            'Shovel', 
            'BookOfMudora', 
            'CaneOfSomaria', 
            'MagicMirror', 
            'PegasusBoots', 
            'ProgressiveGlove', 
            'Flippers', 
            'MoonPearl', 
            'ProgressiveSword'
        ];
        
        $progression_items = $all_items->filter(function ($item) use ($interesting_item_names) {
            return in_array($item->getRawName(), $interesting_item_names);
        });
                
        array_push($interesting_item_names, "Triforce");
        
        
        $output_items = [];
        //this counts thing doesn't actually work, there's only one copy of each progressive item in $progression_items :(
        $progressive_counts = ['ProgressiveBow' => 0,
                               'ProgressiveGlove' => 0,
                               'ProgressiveSword' => 0];
        
        foreach ($progression_items as $item) {
            $findable_items_coll = $world->getItemsFindableWithout($item);
            $findable_items = $findable_items_coll->toArray();                        
                        
            $findable_item_names = [];
            
            foreach($findable_items as $found_item) {                
                array_push($findable_item_names, $found_item->getRawName());
            }           
            
            $good_found_items = array_intersect($findable_item_names, $interesting_item_names);
            
            $output_item_name = $item->getRawName();
            $progressive = 'Progressive';
            
            if (substr($output_item_name, 0, strlen($progressive)) === $progressive) {
                $count = $progressive_counts[$output_item_name] + 1;
                $output_item_name = $output_item_name . $count;
                $progressive_counts[$output_item_name] = $count;
            }
            
            $output_items[$output_item_name] = implode(", ", $good_found_items);
        }
        return $output_items;
        
    }
}
