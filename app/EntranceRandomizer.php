<?php

namespace ALttP;

use ALttP\Contracts\Randomizer as RandomizerContract;
use Log;
use Symfony\Component\Process\Process;

/**
 * Main class for randomization. All the magic happens here. We use mt_rand as it is much faster than rand. Not all PHP
 * functions support mt_rand (e.g. array_shuffle), so those had to be cloned to maintain seed integrity.
 */
class EntranceRandomizer implements RandomizerContract
{
	const LOGIC = -1;
	const VERSION = '31.1';
	protected $world;
	/** @var array */
	private $boss_shuffle_lookup = [
		'simple' => 'basic',
		'full' => 'normal',
		'random' => 'chaos',
	];
	/** @var array */
	private $goal_lookup = [
		'ganon' => 'ganon',
		'fast_ganon' => 'crystals',
		'dungeons' => 'dungeons',
		'pedestal' => 'pedestal',
		'triforce-hunt' => 'triforcehunt',
	];
	/** @var array */
	private $swords_lookup = [
		'randomized' => 'random',
		'assured' => 'assured',
		'vanilla' => 'vanilla',
		'swordless' => 'swordless',
	];

	/** @var array */
	private $dungeon_lookup = [
		'Escape' => 'H2',
		'Hyrule Castle' => 'H2',
		'Eastern Palace' => 'P1',
		'Desert Palace' => 'P2',
		'Tower of Hera' => 'P3',
		'Agahnims Tower' => 'A1',
		'Palace of Darkness' => 'D1',
		'Swamp Palace' => 'D2',
		'Skull Woods' => 'D3',
		'Thieves Town' => 'D4',
		'Ice Palace' => 'D5',
		'Misery Mire' => 'D6',
		'Turtle Rock' => 'D7',
		'Ganons Tower' => 'A2',
	];

	/** @var array */
	private $number_lookup = [
		'20' => 'Twenty',
		'10' => 'Ten',
		'1' => 'One',
		'300' => 'ThreeHundred',
		'3' => 'Three',
		'100' => 'OneHundred',
		'50' => 'Fifty',
		'5' => 'Five',
		'1/2' => 'Half',
	];

	/** @var array */
	private $bottle_lookup = [
		'Bottle (Green Potion)' => 'BottleWithGreenPotion',
		'Bottle (Blue Potion)' => 'BottleWithBluePotion',
		'Bottle (Red Potion)' => 'BottleWithRedPotion',
		'Bottle (Fairy)' => 'BottleWithFairy',
		'Bottle (Bee)' => 'BottleWithBee',
		'Bottle (Good Bee)' => 'BottleWithGoldBee',
	];

	/** @var array */
	private $pendant_lookup = [
		'Green Pendant' => 'PendantOfCourage',
		'Red Pendant' => 'PendantOfWisdom',
		'Blue Pendant' => 'PendantOfPower',
	];

	/** @var array */
	private $location_renames = [
		'Bumper Cave Ledge' => 'Bumper Cave',
		'Graveyard Cave' => 'Graveyard Ledge',
		'Mini Moldorm Cave - Generous Guy' => 'Mini Moldorm Cave - NPC',
		'Hype Cave - Generous Guy' => 'Hype Cave - NPC',
		'Bonk Rock Cave' => 'Pegasus Rocks',
		'Peg Cave' => 'Hammer Pegs',
		'Hyrule Castle - Zelda\'s Chest' => 'Hyrule Castle - Zelda\'s Cell',
		'Ganon\'s Tower - Validation Chest' => 'Ganon\'s Tower - Moldorm Chest',

	];
	/**
	 * Create a new Entrance Randomizer. This currently only works with one
	 * world. So we use the first of the array passed in.
	 *
	 * @param array  $worlds  worlds to randomize
	 *
	 * @return void
	 */
	public function __construct(array $worlds)
	{
		$this->world = reset($worlds);

		if (!$this->world instanceof World) {
			throw new \OutOfBoundsException;
		}
	}

	/**
	 * Fill all empty Locations with Items using logic from the World. This is achieved by first setting up base
	 * portions of the world. Then taking the remaining empty locations we order them, and try to fill them in
	 * order in a way that opens more locations.
	 *
	 * @return void
	 */
	public function randomize(): void
	{
		$flags = [];
		if ($this->world->config('dungeonItems') === 'full') {
			$flags[] = '--keysanity';
		}

		$mode = 'standard';
		if ($this->world instanceof World\Open) {
			$mode = 'open';
		} elseif ($this->world instanceof World\Inverted) {
			$mode = 'inverted';
		}
		if ($this->world instanceof World\Retro) {
			$mode = 'open';
			$flags[] = '--retro';
		}

		switch ($this->world->config('logic')) {
			case 'no_logic':
				$logic = 'nologic';

				break;
			case 'none':
			default:
				$logic = 'noglitches';
		}

		if ($this->world->config('enemizer.bossShuffle') !== 'none') {
			$flags = array_merge($flags, [
				'--shufflebosses',
				$this->boss_shuffle_lookup[$this->world->config('enemizer.bossShuffle')],
			]);
		}

		if ($this->world->config('spoil.Hints') === 'on') {
			$flags[] = '--hint';
		}

		if ($this->world->config('meta.noRom')) {
			$flags[] = '--suppress_rom';
		}

		$proc = new Process(
			array_merge(
				[
					'python3.9',
					base_path('vendor/z3/entrancerandomizer/EntranceRandomizer.py'),
					'--mode',
					$mode,
					'--logic',
					$logic,
					'--accessibility',
					$this->world->config('accessibility'),
					'--swords',
					$this->swords_lookup[$this->world->config('mode.weapons')],
					'--goal',
					$this->goal_lookup[$this->world->config('goal')],
					'--difficulty',
					$this->world->config('item.pool'),
					'--item_functionality',
					$this->world->config('item.functionality'),
					'--shuffle',
					$this->world->config('entrances'),
					'--crystals_ganon',
					$this->world->config('crystals.ganon'),
					'--crystals_gt',
					$this->world->config('crystals.tower'),
					'--securerandom',
					'--jsonout',
					'--loglevel',
					'error',
				],
				$flags
			)
		);

		Log::debug($proc->getCommandLine());
		$proc->run();

		if (!$proc->isSuccessful()) {
			Log::error($proc->getOutput());
			Log::error($proc->getErrorOutput());
			print_r('Failed to generate ER');
			print_r($proc->getOutput());
			// create a new  array 
			$emptyArray = [];
			// add error key to array
			$emptyArray['error'] = $proc->getErrorOutput();
			//  set spoiler to empty array
			$this->world->setSpoiler($emptyArray);
			return;
		}

		$er = json_decode($proc->getOutput());
		// print_r($er);

		// loop over keys in er, skip 'Entrances', 'Special' and 'meta'
		foreach (json_decode($er->spoiler, true) as $key => $value) {
			if ($key === 'Entrances' || $key === 'Special' || $key === 'Shops' || $key === 'meta' || $key === 'playthrough' || $key === 'paths') {
				continue;
			}
			// loop over locations in er[$key]
			// print_r($value);
			foreach ($value as $location => $item) {
				if ($item === 'Beat Agahnim 2') {
					continue;
				}
				if (strpos($location, 'Ganons') !== false) {
					// replace Ganons -> Ganon's
					$location = str_replace('Ganons', 'Ganon\'s', $location);
				}
				// Check if location is in rename array
				if (array_key_exists($location, $this->location_renames)) {
					$location = $this->location_renames[$location];
				}
				// If this item name contains Big Key, Small Key, Compass or Map, rename to match our convention
				// We need to extract the dungeon name from the brackets first and map it to our convention
				// Then we can replace the item name with the new name
				if (strpos($item, 'Big Key') !== false) {
					$dungeon = substr($item, strpos($item, '(') + 1, -1);
					$dungeon = $this->dungeon_lookup[$dungeon];
					$item = 'BigKey' . $dungeon;
				} elseif (strpos($item, 'Key') !== false) {
					$dungeon = substr($item, strpos($item, '(') + 1, -1);
					$dungeon = $this->dungeon_lookup[$dungeon];
					$item = 'Key' . $dungeon;
				} elseif (strpos($item, 'Compass') !== false) {
					$dungeon = substr($item, strpos($item, '(') + 1, -1);
					$dungeon = $this->dungeon_lookup[$dungeon];
					$item = 'Map' . $dungeon;
				} elseif (strpos($item, 'Map') !== false) {
					$dungeon = substr($item, strpos($item, '(') + 1, -1);
					$dungeon = $this->dungeon_lookup[$dungeon];
					$item = 'Map' . $dungeon;
				} elseif (strpos($item, 'Bottle ') !== false) {
					$item = $this->bottle_lookup[$item];
				} elseif (strpos($item, 'Pendant') !== false) {
					$item = $this->pendant_lookup[$item];
				} elseif (strpos($item, 'Single') !== false) {
					$item = str_replace('Single ', '', $item);
				} elseif (strpos($item, 'Magic Upgrade (1/2)') !== false) {
					$item = 'HalfMagic';
				} elseif (strpos($item, '(') !== false) {
					$number = substr($item, strpos($item, '(') + 1, -1);
					$item = substr($item, 0, strpos($item, ' ('));
					$item = $this->number_lookup[$number] . $item;
				} elseif (strpos($item, 'Sanctuary') !== false) {
					$item = str_replace('Sanctuary ', '', $item);
				} elseif (strpos($item, 'Blue ') !== false) {
					$item = str_replace('Blue ', '', $item);
				} elseif (strpos($item, 'Powder') !== false) {
					$item = str_replace('Magic ', '', $item);
				} elseif (strpos($item, 'Ocarina') !== false) {
					$item = $item . 'Inactive';
				}
				// capitalise each word
				$item = ucwords($item);
				// remove all spaces
				$item = str_replace(' ', '', $item);
				// try to get location, continue if error raised
				try {
					$loc = $this->world->getLocation($location);
				} catch (\Exception $e) {
					continue;
				}
				// set item in location
				$loc->setItem(Item::get($item, $this->world));
			}
		}

		if (!$this->world->config('meta.noRom')) {
			$patch = $er->patch;
			array_walk($patch, function (&$write, $address) {
				$write = [$address => $write];
			});
			$this->world->setOverridePatch(array_values((array) $patch));
		}

		// possible temp fix
		$spoiler = json_decode($er->spoiler, true);
		$spoiler['meta']['build'] = Rom::BUILD;
		$spoiler['meta']['logic'] = 'er-no-glitches-' . static::VERSION;

		$this->world->setSpoiler($spoiler);

		if ($this->world->config('enemizer.bossShuffle') !== 'none') {
			$this->world->getRegion('Eastern Palace')->setBoss(Boss::get($spoiler['Bosses']['Eastern Palace'], $this->world));
			$this->world->getRegion('Desert Palace')->setBoss(Boss::get($spoiler['Bosses']['Desert Palace'], $this->world));
			$this->world->getRegion('Tower of Hera')->setBoss(Boss::get($spoiler['Bosses']['Tower Of Hera'], $this->world));
			$this->world->getRegion('Palace of Darkness')->setBoss(Boss::get($spoiler['Bosses']['Palace Of Darkness'], $this->world));
			$this->world->getRegion('Swamp Palace')->setBoss(Boss::get($spoiler['Bosses']['Swamp Palace'], $this->world));
			$this->world->getRegion('Skull Woods')->setBoss(Boss::get($spoiler['Bosses']['Skull Woods'], $this->world));
			$this->world->getRegion('Thieves Town')->setBoss(Boss::get($spoiler['Bosses']['Thieves Town'], $this->world));
			$this->world->getRegion('Ice Palace')->setBoss(Boss::get($spoiler['Bosses']['Ice Palace'], $this->world));
			$this->world->getRegion('Misery Mire')->setBoss(Boss::get($spoiler['Bosses']['Misery Mire'], $this->world));
			$this->world->getRegion('Turtle Rock')->setBoss(Boss::get($spoiler['Bosses']['Turtle Rock'], $this->world));
			$this->world->getRegion('Ganons Tower')->setBoss(Boss::get($spoiler['Bosses']['Ganons Tower Basement'], $this->world), 'bottom');
			$this->world->getRegion('Ganons Tower')->setBoss(Boss::get($spoiler['Bosses']['Ganons Tower Middle'], $this->world), 'middle');
			$this->world->getRegion('Ganons Tower')->setBoss(Boss::get($spoiler['Bosses']['Ganons Tower Top'], $this->world), 'top');
		}
	}

	/**
	 * Get all the worlds being randomized.
	 *
	 * @return array
	 */
	public function getWorlds(): array
	{
		return [$this->world];
	}
}