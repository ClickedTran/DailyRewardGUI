<?php
namespace ClickedTran\DiemDanh;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;

use ClickedTran\DiemDanh\DiemDanhGUI;

class EventListener implements Listener{
  
  public function onJoin(PlayerJoinEvent $event){
    $player = $event->getPlayer();
    $currentDate = date("Y-m-d");
    $manager = DiemDanhGUI::getInstance()->getManager();
    $manager->getDataPlayer($player);
  }
}