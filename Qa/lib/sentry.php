<?php
use Exception as E;
use Magento\Framework\DataObject;

/**
 * 2016-12-22
 * @param DataObject|mixed[]|mixed|E $v
 * @param array(string => mixed) $context [optional]
 */
function df_sentry($v, array $context = []) {
	if (true || !df_my_local()) {
		$context = df_extend([
			// 2016-22-22
			// https://docs.sentry.io/clients/php/usage/#optional-attributes
			'extra' => []
			,'tags' => [
				'PHP' => phpversion()
				,'Magento' => df_magento_version()
				// 2016-12-22
				// К сожалению, использовать «/» в имени тега нельзя.
				,'mage2pro_core' => df_package_version('mage2pro/core')
			]
		], $context);
		if ($v instanceof E) {
			// 2016-12-22
			// https://docs.sentry.io/clients/php/usage/#reporting-exceptions
			df_sentry_m()->captureException($v, $context);
		}
		else {
			$v = df_dump($v);
			// 2016-12-22
			// https://docs.sentry.io/clients/php/usage/#reporting-other-errors
			df_sentry_m()->captureMessage($v, [], $context);
		}
	}
}

/**
 * 2016-12-22
 * @return \Raven_Client
 */
function df_sentry_m() {return dfcf(function() {
	/** @var \Raven_Client $result */
	$result = new \Raven_Client(
		'https://0574710717d5422abd1c5609012698cd:32ddadc0944c4c1692adbe812776035f@sentry.io/124181'
		,[
			/**
			 * 2016-12-22
			 * Не используем стандартные префиксы: @see \Raven_Client::getDefaultPrefixes()
			 * потому что они включают себя весь @see get_include_path()
			 * в том числе и папки внутри Magento (например: lib\internal),
			 * и тогда, например, файл типа
			 * C:\work\mage2.pro\store\lib\internal\Magento\Framework\App\ErrorHandler.php
			 * будет обрезан как Magento\Framework\App\ErrorHandler.php
			 */
			'prefixes' => [BP . DIRECTORY_SEPARATOR]
		]
	);
	/**
	 * 2016-12-22
	 * «The root path to your application code.»
	 * https://docs.sentry.io/clients/php/config/#available-settings
	 * У Airbrake для Ruby есть аналогичный параметр — «root_directory»:
	 * https://github.com/airbrake/airbrake-ruby/blob/v1.6.0/README.md#root_directory
	 */
	$result->setAppPath(BP);
	return $result;
});}