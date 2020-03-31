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
        //example rough output
        $hammerless_items = $world->getItemsFindableWithout(Item::get('Hammer', $world));
        return ['Hammer' => $hammerless_items->__toString()];
        
        //todo: do it for all these items, filter the output to only things we care about
        //todo: figure out how the hell progressive items work
        $progression_items = [
            Item::get('ProgressiveBow', $world),
            Item::get('Hookshot', $world),
            Item::get('Mushroom', $world),
            Item::get('Powder', $world),
            Item::get('FireRod', $world),
            Item::get('IceRod', $world),
            Item::get('Bombos', $world),
            Item::get('Ether', $world),
            Item::get('Quake', $world),
            Item::get('Lamp', $world),
            Item::get('Hammer', $world),
            Item::get('OcarinaInactive', $world),
            Item::get('Shovel', $world),
            Item::get('BookOfMudora', $world),
            Item::get('CaneOfSomaria', $world),
            Item::get('MagicMirror', $world),
            Item::get('PegasusBoots', $world),
            Item::get('ProgressiveGlove', $world),
            Item::get('Flippers', $world),
            Item::get('MoonPearl', $world),
            Item::get('ProgressiveSword', $world),
        ];
        $output_items = $progression_items->copy();
        $output_items->array_push(Item::get('Triforce', $world));
        
    }
}
