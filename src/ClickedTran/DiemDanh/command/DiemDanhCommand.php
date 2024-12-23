<?php
namespace ClickedTran\DiemDanh\command;

use pocketmine\command\{
  CommandSender,
  Command
};
use pocketmine\Server;
use pocketmine\player\Player;

use ClickedTran\DiemDanh\DiemDanhGUI;
use ClickedTran\DiemDanh\manager\DiemDanhManager;
use ClickedTran\DiemDanh\gui\GUIManager;

class DiemDanhCommand extends Command {
  private DiemDanhGUI $plugin;
  
  public function __construct(DiemDanhGUI $plugin){
    $this->plugin = $plugin;
    parent::__construct("diemdanh", "Open DiemDanh Menu");
    $this->setPermission("diemdanh.command");
  }
  
  public function execute(CommandSender $sender, String $label, Array $args){
    if(!$sender instanceof Player){
      $sender->sendMessage("§cPlease use command in-game!");
      return;
    }
    $gui = new GUIManager();
    $gui->openMenu($sender);
  }
}
