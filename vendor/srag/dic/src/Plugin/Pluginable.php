<?php

namespace srag\DIC\LearningObjectiveSuggestions\Plugin;

/**
 * Interface Pluginable
 *
 * @package srag\DIC\LearningObjectiveSuggestions\Plugin
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
interface Pluginable
{

    /**
     * @return PluginInterface
     */
    public function getPlugin() : PluginInterface;


    /**
     * @param PluginInterface $plugin
     *
     * @return static
     */
    public function withPlugin(PluginInterface $plugin)/*: static*/ ;
}
