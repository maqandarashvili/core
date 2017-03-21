<?php
namespace Df\Framework;
use Df\Config\Settings as S;
use Df\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface as IRequest;
// 2017-03-19
/** @see \Df\Payment\Action */
abstract class Action extends \Magento\Framework\App\Action\Action {
	/**
	 * 2017-03-19
	 * Возвращает имя модуля в формате «Dfe\Stripe».
	 * Мы должны использовать именно это имя вместо получения имени из имени текущего класса,
	 * потому что мы можем использовать virtualType,
	 * и тогда реальное имя текущего класса может не относиться к текущему модулю.
	 * @used-by s()
	 * @used-by \Df\Payment\Action\CustomerReturn::execute()
	 * @used-by \Df\Payment\W\Action::execute()
	 * @return string
	 */
	final protected function m() {return dfc($this, function() {return df_module_name_c(
		$this->request()->getControllerModule()
	);});}

	/**
	 * 2016-12-25
	 * @final I do not use the PHP «final» keyword here to allow refine the return type using PHPDoc.
	 * @return S
	 */
	protected function s() {return dfc($this, function() {return S::conventionB($this->m());});}

	/**
	 * 2017-03-19
	 * @used-by module()
	 * @return IRequest|Http
	 */
	private function request() {return $this->getRequest();}
}