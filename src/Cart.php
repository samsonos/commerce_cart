<?php
/**
 * Created by Nikita Kotenko <kotenko@samsonos.com>
 * on 02.01.15 at 18:35
 */
namespace samsonos\commerce\cart;

use samson\core\CompressableService;
use samson\core\Event;


/**
 * SamsonPHP Liqpay module
 * @author Nikita Kotenko <kotenko@samsonos.com>
 * @copyright 2015 SamsonOS
 */
class Cart extends CompressableService
{
    public $id = 'cart';

	public $commerceCore;
	public $popupView = 'popup.php';
	public $cartIndexView = 'index.php';
	public $itemView = 'item.php';
	public $renderer;

	public function init(array $params=array())
	{
		parent::init( $params );
		if (isset($this->renderer)) {
			$this->renderer = & m($this->renderer);
		} else {
			$this->renderer = & $this;
		}
		Event::fire('commerce.init.module.commerce.core',array(& $this));
	}

	public function __HANDLER()
	{
		$orders = $this->commerceCore->ordersList();
		$rows = '';
		foreach ($orders as $order) {
			foreach ($order->items as $item) {
				$rows .= $this->renderer->view($this->itemView)->item($item)->product($item->Product)->output();
			}
		}
		$this->renderer->view($this->cartIndexView)->rows($rows);
	}

	public function __async_add($productId)
	{
		$response = array('status'=>0);
		$count = isset($_POST['count'])?$_POST['count'] : 1;
		if ($this->commerceCore->addOrderItem($productId, $count) !== false) {
			$response['status'] = 1;
			$response['html'] = $this->renderer->view($this->popupView)->output();
		}
		return $response;
	}
}