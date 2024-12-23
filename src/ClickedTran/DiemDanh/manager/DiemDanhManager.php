<?php
namespace ClickedTran\DiemDanh\manager;

use pocketmine\Server;
use pocketmine\utils\{
  Config,
  SingletonTrait
};
use pocketmine\console\ConsoleCommandSender;
use pocketmine\player\Player;
use pocketmine\item\{
  Item,
  StringToItemParser,
  LegacyStringToItemParser
};
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\item\enchantment\StringToEnchantmentParser;

use ClickedTran\DiemDanh\DiemDanhGUI;

class DiemDanhManager {
  
  public DiemDanhGUI $plugin;
  private ?Config $user = null;
  
  public function __construct(DiemDanhGUI $plugin){
    $this->plugin = $plugin;
    $this->plugin->saveDefaultConfig();
  }
  
  public function getCFG(){
    return $this->plugin->getConfig();
  }
  
  public function getDataPlayer(Player $player) : Config{
    @mkdir($this->plugin->getDataFolder()."users/");
    if($this->user == null){
      $this->user = new Config($this->plugin->getDataFolder() . "users/".strtolower($player->getName()).".yml", Config::YAML,
      [
        "days" => [],
        "streak" => 0,
        "last_login" => ""
      ]);
    }
    return $this->user;
  }
  
  public function getPlayerLoginDay(Player $player){
    return $this->getDataPlayer($player)->get("days");
  }
  
  public function setPlayerLoginDay(Player $player, array $string) : void{
    $this->getDataPlayer($player)->set("days", $string);
    $this->getDataPlayer($player)->save();
  }
  
  public function getPlayerLoginStreak(Player $player) : int{
    return $this->getDataPlayer($player)->get("streak");
  }
  
  public function setPlayerLoginStreak(Player $player, int $streak){
    $this->getDataPlayer($player)->set("streak", $streak);
    $this->getDataPlayer($player)->save();
  }
  
  public function updatePlayerLoginStreak(Player $player){
    $this->getDataPlayer($player)->set("streak", $this->getPlayerLoginStreak($player) + 1);
    $this->getDataPlayer($player)->save();
  }
  
  public function getPlayerLastLogin(Player $player){
    return $this->getDataPlayer($player)->get("last_login");
  }
  
  public function updatePlayerLastLogin(Player $player, string $time) : void{
    $this->getDataPlayer($player)->set("last_login", $time);
    $this->getDataPlayer($player)->save();
  }
  
  public function randomReward(Player $player){
    $rewards = $this->getCFG()->getNested("rewards.normal");
    $randomItem = $rewards[array_rand($rewards)];
    $items = $randomItem["item"];
    $ex = explode(":", $items);
    $item = StringToItemParser::getInstance()->parse($ex[0]) ?? LegacyStringToItemParser::getInstance()->parse($ex[0].':'.$ex[1]);
    $item->setCount($ex[2]);
    if(isset($randomItem["name"])){
      $item->setCustomName($randomItem["name"]);
    }
    
    if(isset($randomItem["lore"])){
      $item->setLore($randomItem["lore"]);
    }
    
    $enchants = [];
    if(isset($randomItem["enchantments"])){
      foreach($randomItem["enchantments"] as $enchant){
        $item->addEnchantment(new EnchantmentInstance(StringToEnchantmentParser::getInstance()->parse($enchant["name"]), $enchant["level"]));
      }
    }
    
    if(isset($randomItem["commands"])){
      foreach($randomItem["commands"] as $cmd){
        $this->plugin->getServer()->dispatchCommand(new ConsoleCommandSender($this->plugin->getServer(), $this->plugin->getServer()->getLanguage()), str_replace("{player}", $player->getName(), $cmd));
      }
    }
    $player->getInventory()->addItem($item);
  }
  
  public function randomStreakReward(Player $player, int $streak){
    $reward = $this->getCFG()->getNested("rewards.vip.".$streak);
    //foreach($rewards as $reward){
    if($streak <= $reward){
      $randomItem = $reward[array_rand($reward)];
      $items = $randomItem["item"];
      $ex = explode(":", $items);
      $item = StringToItemParser::getInstance()->parse($ex[0]) ?? LegacyStringToItemParser::getInstance()->parse($ex[0].':'.$ex[1]);
      $item->setCount($ex[2]);
      if(isset($randomItem["name"])){
        $item->setCustomName($randomItem["name"]);
      }
    
      if(isset($randomItem["lore"])){
        $item->setLore($randomItem["lore"]);
       }
    
      $enchants = [];
      if(isset($randomItem["enchantments"])){
        foreach($randomItem["enchantments"] as $enchant){
          $item->addEnchantment(new EnchantmentInstance(StringToEnchantmentParser::getInstance()->parse($enchant["name"]), $enchant["level"]));
          }
        }
    
      if(isset($randomItem["commands"])){
         foreach($randomItem["commands"] as $cmd){
        $this->plugin->getServer()->dispatchCommand(new ConsoleCommandSender($this->plugin->getServer(), $this->plugin->getServer()->getLanguage()), str_replace("{player}", $player->getName(), $cmd));
        }
     }
      $player->getInventory()->addItem($item);
    //}
    }
  }
}
