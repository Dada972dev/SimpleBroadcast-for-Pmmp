<?php

namespace broad;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\Config;
use broad\tasks\BroadcastTask;

class Main extends PluginBase {

    private Config $config;
    private int $broadcastInterval = 400; // Default to 20 seconds

    public function onEnable(): void {
        $this->loadConfig();
        $this->scheduleBroadcastTask();
    }

    private function loadConfig(): void {
        $this->saveDefaultConfig();
        $this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
    }

    private function scheduleBroadcastTask(): void {
        $this->getScheduler()->scheduleRepeatingTask(new BroadcastTask($this), $this->broadcastInterval);
    }

    public function getMessages(): array {
        return $this->config->get("messages", []);
    }

    public function addMessage(string $message): void {
        $messages = $this->getMessages();
        $messages[] = $message;
        $this->config->set("messages", $messages);
        $this->config->save();
    }

    public function setBroadcastInterval(int $seconds): void {
        $this->broadcastInterval = $seconds * 20; // Convert seconds to ticks
        $this->getScheduler()->cancelAllTasks();
        $this->scheduleBroadcastTask();
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        if ($command->getName() === "broadcast") {
            if (!$sender->hasPermission("broadcastplugin.broadcast")) {
                $sender->sendMessage("You do not have permission to use this command.");
                return true;
            }
            if (empty($args)) {
                $sender->sendMessage("Usage: /broadcast <message>");
                return true;
            }
            $message = implode(" ", $args);
            $this->getServer()->broadcastMessage("§d[" . $sender->getName() . "]: §f" . $message);
            return true;
        } elseif ($command->getName() === "forcebroadcast") {
            if (!$sender->hasPermission("broadcastplugin.forcebroadcast")) {
                $sender->sendMessage("You do not have permission to use this command.");
                return true;
            }
            $this->forceBroadcast();
            $sender->sendMessage("Broadcast messages have been sent.");
            return true;
        } elseif ($command->getName() === "setbroadcastmessage") {
            if (!$sender->hasPermission("broadcastplugin.setbroadcastmessage")) {
                $sender->sendMessage("You do not have permission to use this command.");
                return true;
            }
            if (empty($args)) {
                $sender->sendMessage("Usage: /setbroadcastmessage <message>");
                return true;
            }
            $message = implode(" ", $args);
            $this->addMessage($message);
            $sender->sendMessage("Broadcast message added.");
            return true;
        } elseif ($command->getName() === "setbroadcasttime") {
            if (!$sender->hasPermission("broadcastplugin.setbroadcasttime")) {
                $sender->sendMessage("You do not have permission to use this command.");
                return true;
            }
            if (empty($args) || !is_numeric($args[0])) {
                $sender->sendMessage("Usage: /setbroadcasttime <seconds>");
                return true;
            }
            $seconds = (int)$args[0];
            $this->setBroadcastInterval($seconds);
            $sender->sendMessage("Broadcast interval set to $seconds seconds.");
            return true;
        }
        return false;
    }

    private function forceBroadcast(): void {
        $messages = $this->getMessages();
        foreach ($messages as $message) {
            $this->getServer()->broadcastMessage($message);
        }
    }
}