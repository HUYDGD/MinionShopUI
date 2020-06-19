<?php
namespace MinionShop;
use pocketmine\server\Server;
use pocketmine\plugin\PluginBase;
use pocketmine\command\{CommandSender, Command, ConsoleCommandSender};
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\item\Item;
use pocketmine\inventory\{Inventory, BaseInventory};
use pocketmine\utils\Config;
use onebone\economyapi\EconomyAPI;
use CLADevs\Minion\Main;
use jojoe77777\FormAPI\{FormAPI, SimpleForm, CustomForm};
class MinionShop extends PluginBase implements Listener{
    public $config;
    /* EDIT VALUE HERE! */
    public function onEnable(): void{
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
        $this->getLogger()->info("Plugin đã được bật!");
        $this->getLogger()->info("§c-§a-§2-§e-§6-§d-§c-§a-§2-§e-§6-§d-§c-§a-§2-§e-§6-§d-§c-§a-§2-§e-§6-§d-§c-");
        $this->getLogger()->info("§l- Youtube: @SoiOniichan -");
        $this->getLogger()->info("§c-§a-§2-§e-§6-§d-§c-§a-§2-§e-§6-§d-§c-§a-§2-§e-§6-§d-§c-§a-§2-§e-§6-§d-§c-");
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        @mkdir($this->getDataFolder());
        $this->saveResource("config.yml");
        $this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        $this->saveDefaultConfig();
        $this->reloadConfig();
    }
    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool{
        if($sender instanceof Player){
        switch(strtolower($cmd->getName())){
                case "minionshop":
                case "ms":
                $this->mainForm($sender);
                break;
    }
        }else{
                $sender->sendMessage($this->config->get("prefix"). $this->config->get("error-consoleSender"));
    }
                return true;
    }
    /* MAIN FORM */
    public function mainForm(Player $player){
        $form = new SimpleForm(function(Player $player, int $data = null){
            if($data === null){
                return true;
            }
            switch($data){
                case 0:
                    $this->buyMinionForm($player);
                break;
                case 1:
                    $this->sellMinionForm($player);
                break;
                case 2:
                    $this->priceListForm($player);
                break;
            }
        });
        $form->setTitle($this->config->get("title"));
        $money = EconomyAPI::getInstance()->myMoney($player);
        $form->setContent("§l§fTài khoản: §a". $player->getName() ." §f| CREDITS: §e". $money ."$");
        $form->addButton("§l§aMUA MINION\n§f[NHẤN VÀO ĐỂ MUA]");
        $form->addButton("§l§cBÁN MINION\n§f[NHẤN VÀO ĐỂ BÁN]");
        $form->addButton("§l§eBẢNG GIÁ MINION\n§f[NHẤN VÀO ĐỂ XEM]");
        $form->sendToPlayer($player);
        return $form;
    }
    /* BUY FORM */
    public function buyMinionForm(Player $player){
        $form = new CustomForm(function (Player $player, ?array $data){
        if($data[1] === null){
            $this->mainForm($player);
            return true;
        }
        $prices = $this->config->get("price") * $data[1];
        $money = EconomyAPI::getInstance()->myMoney($player);
        if($money < $prices){
            $player->sendMessage($this->config->get("prefix"). $this->config->get("error-notEnoughMoney"));
        }else{
            EconomyAPI::getInstance()->reduceMoney($player, $prices);
            $i = 0;
            while($i < $data[1]){
            $i++;
            $this->getServer()->getPluginManager()->getPlugin("Minion")->giveItem($player);
            }
            $amount = str_replace("{amount}", $data[1], $this->config->get("success-boughtMinion"));
            $buy = str_replace("{buy}", $prices, $this->config->get("success-deductionMoney"));
            $player->sendMessage($this->config->get("prefix"). $amount);
            $player->sendMessage($this->config->get("prefix"). $buy);
        }
    });
        $form->setTitle($this->config->get("title"));
        $money = EconomyAPI::getInstance()->myMoney($player);
        $form->addLabel("§l§fTài khoản: §a". $player->getName() ." §f| CREDITS: §e". $money ."$");
        $form->addSlider("§lSố lượng", 1, $this->config->get("amountBuyMinion"), 1);
        $form->sendToPlayer($player);
        return $form;
    }
    /* SELL FORM */
    public function sellMinionForm(Player $player){
        $form = new CustomForm(function (Player $player, ?array $data){
        if($data[1] === null){
            $this->mainForm($player);
            return true;
        }
        if($player->getInventory()->contains(Item::get(399, 0, $data[1]))){
        $i = 0;
        while($i < $data[1]){
        $player->getInventory()->removeItem(Item::get(399, 0, 1));
        $i++;
        }
        $money = $this->config->get("sell") * $data[1];
        $this->getServer()->getPluginManager()->getPlugin("EconomyAPI")->addMoney($player, $money);
        $amount = str_replace("{amount}", $data[1], $this->config->get("success-sellMinion"));
        $sell = str_replace(["{sell}", "{player}"], [$money, $player->getName()], $this->config->get("success-addMoney"));
        $player->sendMessage($this->config->get("prefix"). $amount);
        $player->sendMessage($this->config->get("prefix"). $sell);
        }else{
        $player->sendMessage($this->config->get("prefix"). $this->config->get("error-notFoundMinion"));
        }
        return true;
    });
        $form->setTitle($this->config->get("title"));
        $money = EconomyAPI::getInstance()->myMoney($player);
        $form->addLabel("§l§fTài khoản: §a". $player->getName() ." §f| CREDITS: §e". $money ."$");
        $form->addSlider("§lSố lượng", 1, $this->config->get("amountSellMinion"), 1);
        $form->sendToPlayer($player);
        return $form;
    }
    /* PRICE FORM */
    public function priceListForm(Player $player){
        $form = new SimpleForm(function(Player $player, int $data = null){
            if($data === null){
                $this->mainForm($player);
                return true;
            }
            switch($data){
                case 0:
                    $this->mainForm($player);
                break;
            }
        });
        $form->setTitle($this->config->get("title"));
        $money = EconomyAPI::getInstance()->myMoney($player);
        $form->setContent("§l§fTài khoản: §a". $player->getName() ." §f| CREDITS: §e". $money ."$\n§l§f> §aGiá mua Minion: §e". $this->config->get("price") ."$ / 1 con\n§l§f> §aGiá bán Minion: §e". $this->config->get("sell") ."$ / 1 con");
        $form->addButton("§l§eTRỞ LẠI.\n[NHẤN VÀO ĐỂ TRỞ LẠI]");
        $form->sendToPlayer($player);
        return $form;
    }
}
?>