<?php
namespace MinionShop;

use pocketmine\plugin\PluginBase;
use pocketmine\command\{CommandSender, Command, CommandMap};
use pocketmine\utils\Config;

use MinionShop\commands\MinionShopCommand;

class MinionShop extends PluginBase{

    public $config;

    public function onEnable(){
        foreach([
            "EconomyAPI" => "EconomyAPI",
            "Minion" => "Minion",
            "FormAPI" => "FormAPI"] as $plugins){
            if(!$this->getServer()->getPluginManager()->getPlugin($plugins)){
                $this->getLogger()->error("Bạn chưa cài plugin ". $plugins .". Vui lòng cài đủ 3 plugin: FormAPI, EconomyAPI, Minion để plugin có thể hoạt động trơn tru.");
                $this->getServer()->getPluginManager()->disablePlugin($this);
                return;
            }
        }
        $this->getLogger()->info("§l§a> §cYoutube: §fyoutube.com/c/SoiOniichan");
        $this->getLogger()->info("§l§a> §6Github: §fgithub.com/GamerSoiCon");
        @mkdir($this->getDataFolder());
        $this->saveResource("config.yml");
        $this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        $this->saveDefaultConfig();
        $this->reloadConfig();
        $this->getServer()->getCommandMap()->register("minionshop", new MinionShopCommand("minionshop", $this));
    }
}
?>
