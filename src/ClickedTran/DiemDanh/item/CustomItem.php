<?php
namespace ClickedTran\DiemDanh\item;

use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemTypeIds;
use pocketmine\utils\CloningRegistryTrait;

final class CustomItem {
  use CloningRegistryTrait;
  
  private function __construct(){
    //DuMaPocketMine!
  }
  
  protected static function register(string $name, Item $item) : void{
		self::_registryRegister($name, $item);
	}

	public static function getAll() : array{
		$result = self::_registryGetAll();
		return $result;
	}

	protected static function setup() : void{
	  $items = [
	    0 => ["id" => "chest_minecart", "itemid" => ItemTypeIds::newId(), "name" => "Chest Minecart"],
	    1 => ["id" => "hopper_minecart", "itemid" => ItemTypeIds::newId(), "name" => "Hopper Minecart"]
	   ];
		foreach($items as $data){
		  self::register($data["id"], new Item(new ItemIdentifier($data["itemid"]), $data["name"]));
		}
	}
}
