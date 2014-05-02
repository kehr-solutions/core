<?php

namespace Netzmacht\Bootstrap\Core\Subscriber;

use Netzmacht\Bootstrap\Core\Bootstrap;
use Netzmacht\Bootstrap\Core\Event\Events;
use Netzmacht\Bootstrap\Core\Event\InitializeEvent;
use Netzmacht\Bootstrap\Core\Event\ReplaceInsertTagEvent;
use Netzmacht\Bootstrap\Core\Event\RewriteCssClassesEvent;
use Netzmacht\Bootstrap\Core\Event\SelectIconSetEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class DefaultSubscriber
 * @package Netzmacht\Bootstrap\Subscriber
 */
class DefaultSubscriber implements EventSubscriberInterface
{

	/**
	 * Returns an array of event names this subscriber wants to listen to.
	 *
	 * The array keys are event names and the value can be:
	 *
	 *  * The method name to call (priority defaults to 0)
	 *  * An array composed of the method name to call and the priority
	 *  * An array of arrays composed of the method names to call and respective
	 *    priorities, or 0 if unset
	 *
	 * For instance:
	 *
	 *  * array('eventName' => 'methodName')
	 *  * array('eventName' => array('methodName', $priority))
	 *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
	 *
	 * @return array The event names to listen to
	 *
	 * @api
	 */
	public static function getSubscribedEvents()
	{
		return array(
			Events::INITIALZE => array('handleInitialize', 1000),
			Events::SELECT_ICON_SET => array('selectIconSet', 1000),
            Events::REWRITE_CSS_CLASSES => array('rewriteCssClasses', 1000),
			//Events::AUTOLOAD_TEMPLATES => array('loadDynamicTemplates', 1000)
		);
	}


	/**
	 * @param InitializeEvent $event
	 */
	public function handleInitialize(InitializeEvent $event)
	{
		$config = $event->getConfig();

		$event->setEnabled($GLOBALS['TL_CONFIG']['bootstrapEnabled']);

		// set global enabled state to config so that components can access it
		$config->set('environment.global-enabled', $event->getEnabled());
		$config->set('icons.active-icon-set', $GLOBALS['TL_CONFIG']['bootstrapIconSet']);
		$config->set('assets.use-component-js', $GLOBALS['TL_CONFIG']['bootstrapUseComponentJs']);
		$config->set('assets.use-component-css', $GLOBALS['TL_CONFIG']['bootstrapUseComponentCss']);
	}


	/**
	 * @param SelectIconSetEvent $event
	 */
	public function selectIconSet(SelectIconSetEvent $event)
	{
		$config   = $event->getConfig();
		$iconSet  = $config->get('icons.activeIconSet');
		$template = $config->get(sprintf('icons.icon-sets.%s.template', $iconSet));
		$path     = $config->get(sprintf('icons.icon-sets.%s.path', $iconSet));

		if($iconSet) {
			if($path && file_exists($path)) {
				$icons = include TL_ROOT . '/' . $path;
				$event->setIcons($icons);
			}

			$event
				->setIconSetName($iconSet)
				->setTemplate($template);
		}
	}


	/**
	 * @param \Netzmacht\Bootstrap\Core\Event\ReplaceInsertTagEvent $event
	 */
	public function replaceIconInsertTag(ReplaceInsertTagEvent $event)
	{
		if($event->getTag() == 'icon' || $event->getTag() == 'i') {
			$icon = Bootstrap::generateIcon($event->getParam(0), $event->getParam(1));
			$event->setHtml($icon);
		}
	}


    /**
     * @param RewriteCssClassesEvent $event
     */
    public function rewriteCssClasses(RewriteCssClassesEvent $event)
    {
        $config = Bootstrap::getConfigVar('templates.rewrite-css-classes', array());
        $event->addClasses($config);
    }

}