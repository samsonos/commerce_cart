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
	public $popupView = 'popup';
	public $cartIndexView = 'cart/index';
	public $itemView = 'cart/item';
	public $cartEmptyView = 'cart/empty';
	public $template;
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
		s()->active($this->renderer);
		if (isset($this->template)) {
			s()->template($this->template);
		}
		$orders = $this->commerceCore->ordersList();
		$rows = '';
		$amount = 0;
		foreach ($orders as $order) {
			foreach ($order->items as $item) {
				$rows .= $this->renderer->view($this->itemView)->item($item)->product($item->Product)->amount($item->Price*$item->Quantity)->output();
				$amount += $item->Price*$item->Quantity;
			}
		}
		if ($rows !== '') {
			$this->renderer->view($this->cartIndexView)->rows($rows)->amount($amount);
		} else {
			$this->renderer->view($this->cartEmptyView);
		}

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