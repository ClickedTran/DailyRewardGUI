<?php

namespace ClickedTran\DiemDanh;

use pocketmine\plugin\PluginBase;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\ItemFlags;

use pocketmine\data\bedrock\item\ItemTypeNames;
use pocketmine\data\bedrock\item\SavedItemData;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;

use pocketmine\world\format\io\GlobalItemDataHandlers;

use muqsit\InvMenu\InvMenuHandler;

use ClickedTran\DiemDanh\command\DiemDanhCommand;
use ClickedTran\DiemDanh\manager\DiemDanhManager;
use ClickedTran\DiemDanh\item\CustomItem;

class DiemDanhGUI extends PluginBase{
  public const FAKE_ENCHANTMENT = -1;
  public static $instance;
  public static function getInstance() : DiemDanhGUI{
    return self::$instance;
  }
  
  public function onLoad() : void{
    $this->registerItems();
    $this->registerEnchant();
  }
  
  public function onEnable() : void{
    $this->getServer()->getCommandMap()->register("DiemDanhGUI", new DiemDanhCommand($this));
    $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
    $this->getManager($this);
    
    self::$instance = $this;
    if(!InvMenuHandler::isRegistered()) InvMenuHandler::register($this);
  }
  
  public function getManager() : DiemDanhManager{
    return new DiemDanhManager($this);
  }
  
  public function registerEnchant(){
    return EnchantmentIdMap::getInstance()->register(self::FAKE_ENCHANTMENT, new Enchantment("", -1, 1, ItemFlags::ALL, ItemFlags::NONE));
  }
  
  public function registerItems(){
    $items = [
      0 => ["item" => CustomItem::CHEST_MINECART(), "name" => "chest_minecart", "vanilla" => ItemTypeNames::CHEST_MINECART],
      1 => ["item" => CustomItem::HOPPER_MINECART(), "name" => "hopper_minecart", "vanilla" => ItemTypeNames::HOPPER_MINECART]
    ];
    foreach($items as $data){
      $this->registerItem($data["vanilla"], $data["item"], [$data["name"]]);
    }
  }
  
  public function registerItem(string $id, Item $item, array $names){
    GlobalItemDataHandlers::getDeserializer()->map($id, fn() => clone $item);
		GlobalItemDataHandlers::getSerializer()->map($item, fn() => new SavedItemData($id));
		
		foreach($names as $name){
		  StringToItemParser::getInstance()->register($name, fn() => clone $item);
		}
  }
}
