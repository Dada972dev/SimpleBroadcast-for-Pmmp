<?php

namespace broad\tasks;

use pocketmine\scheduler\Task;
use broad\Main;

class BroadcastTask extends Task {

    private Main $plugin;

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }

    public function onRun(): void {
        $messages = $this->plugin->getMessages();
        foreach ($messages as $message) {
            $this->plugin->getServer()->broadcastMessage($message);
        }
    }
}