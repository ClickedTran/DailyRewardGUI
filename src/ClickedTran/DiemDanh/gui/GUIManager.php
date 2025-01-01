<?php
namespace ClickedTran\DiemDanh\gui;

use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\item\{
  StringToItemParser,
  LegacyStringToItemParser
};
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\item\enchantment\EnchantmentInstance;

use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\{
  InvMenuTransaction,
  InvMenuTransactionResult
};
use onebone\economyapi\EconomyAPI;
use ClickedTran\DiemDanh\DiemDanhGUI;

use DateTime;

class GUIManager{
  
  public function openMenu(Player $player) : void{
    date_default_timezone_set(DiemDanhGUI::getInstance()->getConfig()->get("timezone"));
    $menu = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
    $menu->setName("DiemDanhGUI");
    $inv = $menu->getInventory();
    
    $manager = DiemDanhGUI::getInstance()->getManager();
    $cfg = $manager->getCFG();
    $dataPlayer = $manager->getDataPlayer($player);
    
    $currentDate = date("Y-m-d");
    $currentMonth = date("Y-m");
    $currentDay = (int)date("d");
    
    $dayInMonth = range(1, date("t"));
    $checkedDay = array_filter($manager->getPlayerLoginDay($player), function($date) use ($currentMonth){
      return strpos($date, $currentMonth) === 0;
    });
    $i = 0;
    for($i == 0; $i < 54; $i++){
      $inv->setItem($i, StringToItemParser::getInstance()->parse("iron_bars")->setCustomName("§r§l§d"));
    }
    
    foreach($dayInMonth as $days){
      $item = StringToItemParser::getInstance()->parse("chest_minecart");
      
      $item->setCustomName("§aNgày§b ".$days);
      if(in_array("$currentMonth-$days", $checkedDay)){
        $item = StringToItemParser::getInstance()->parse("hopper_minecart");
       // $item->addEnchantment(new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId(DiemDanhGUI::FAKE_ENCHANTMENT)));
        $item->setCustomName("§aNgày§b ".$days);
        $item->setLore(["§bĐÃ ĐIỂM DANH"]);
        $item->getNamedTag()->setString("has_complete", "$currentMonth-$days");
      }elseif($days > $currentDay){
        $item->setCustomName("§aNgày§b ".$days." §4( §9Không thể điểm danh§4 )");
        $item->setLore(["§bCHƯA TỚI NGÀY"]);
        $item->getNamedTag()->setString("next_day", "$currentMonth-$days");
      }elseif($days < $currentDay){
        $item->setLore(["§bĐã quá hạn điểm danh", "§bBấm để điểm danh lại", "§bPhí điểm danh bù: §c".$cfg->get("cost") * $days]);
        $item->getNamedTag()->setString("missed_day", "$currentMonth-$days");
      }else{
        $item->setLore(["§bBấm để điểm danh"]);
        $item->getNamedTag()->setString("checkin_day", "$currentMonth-$days");
      }
      $inv->setItem($days - 1, $item);
    }
    $menu->setListener(function(InvMenuTransaction $transaction) use ($currentDate, $dataPlayer, $player, $manager) : InvMenuTransactionResult{
      $item = $transaction->getItemClicked();
      $day = $manager->getPlayerLoginDay($player);
      if($item->getNamedTag()->getTag("checkin_day") !== null){
        $checkin = $item->getNamedTag()->getString("checkin_day");
        if(!in_array($checkin, $day)){
          $lastLogin = $manager->getPlayerLastLogin($player);
          if($lastLogin === ""){
            $manager->setPlayerLoginStreak($player, 1);
          }else{
            $newDate = new DateTime($checkin);
            $newLast = new DateTime($lastLogin);
            if($newLast->format("Y-m") !== $newDate->format("Y-m")){
              $manager->setPlayerLoginStreak($player, 1);
              $manager->getDataPlayer($player)->set("last_login", "");
              $day = [];
            }elseif($newDate->getTimestamp() - $newLast->getTimestamp() > 86400){
              $manager->setPlayerLoginStreak($player, 1);
              $manager->getDataPlayer($player)->set("last_login", "");
              $day = [];
            }else{
              $manager->updatePlayerLoginStreak($player);
            }
          }
          $day[] = $checkin;
          $updateLastLogin = $checkin;
          $streak = $manager->getPlayerLoginStreak($player);
          $manager->setPlayerLoginDay($player, $day);
          $manager->updatePlayerLastLogin($player, $updateLastLogin);
          $manager->randomReward($player);
          $manager->randomStreakReward($player, $streak);
            $player->sendMessage("§aBạn đã điểm danh liên tiếp §b".$streak." §angày, phần quà đã gửi về túi đồ!");
          $player->removeCurrentWindow();
        }
        return $transaction->discard();
      }
      
      if($item->getNamedTag()->getTag("has_complete") !== null){
        $hasComplete = $item->getNamedTag()->getString("has_complete");
        if(in_array($hasComplete, $day)){
          $player->sendMessage("§aBạn đã điểm danh hôm nay rồi!");
          $player->removeCurrentWindow();
        }
        return $transaction->discard();
      }
      
      if($item->getNamedTag()->getTag("missed_day") !== null){
        $missedDay = $item->getNamedTag()->getString("missed_day");
        if(in_array($missedDay, $day)){
          $player->sendMessage("§aBạn đã điểm danh ngày này rồi!");
          $player->removeCurrentWindow();
        }else{
          $day[] = $missedDay;
          $cost = $manager->getCFG()->get("cost") * explode(" ", $item->getCustomName())[1];
          if(EconomyAPI::getInstance()->myMoney($player) < $cost){
            $player->sendMessage("Bro khong du tien de diem danh bu");
            $player->removeCurrentWindow();
          }else{
            EconomyAPI::getInstance()->reduceMoney($player, $cost);
            $manager->setPlayerLoginDay($player, $day);
            $manager->randomReward($player);
          $player->sendMessage("§aBạn đã điểm danh bù thành công, phần quà đã gửi về túi đồ!");
            $player->removeCurrentWindow();
          }
        }
        return $transaction->discard();
      }
      
      if($item->getNamedTag()->getTag("next_day") !== null){
        $nextDay= $item->getNamedTag()->getString("next_day");
        if(strtotime($nextDay) > strtotime($currentDate)){
          $player->sendMessage("Ban khong the diem danh truoc ngay duoc");
          $player->removeCurrentWindow();
        }
        return $transaction->discard();
      }
      
      return $transaction->discard();
    });
    $menu->send($player);
  }
}
