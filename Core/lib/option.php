<?php
use Magento\Config\Model\Config\Source\Yesno as YN;
use Magento\Framework\Phrase;
/**
 * 2015-12-28 Преобразует при необходимости простой одномерный массив в список опций.
 * @used-by \Df\Framework\Form\Element\Fieldset::select()
 * @used-by \Df\Framework\Form\Element\Select\Range::getValues()
 * @param string[] $a
 * @return array(array(string => string|int))
 */
function df_a_to_options(array $a) {return is_null($f = df_first($a)) || isset($f['value']) ? $a :
	df_map_to_options(dfa_combine_self($a))
;}

/**
 * 2018-01-29
 * @used-by \Df\Config\Source\API::map()
 * @used-by \Df\Config\Source\Block::map()
 * @used-by \Dfe\SMTP\Source\Service::map()
 * @used-by \Dfe\ZohoCRM\Source\Domain::map()
 * @param array(string => string) $tail
 * @param string|null $label [optional]
 * @return array(int => string)
 */
function df_map_0(array $tail, $label = null) {return [0 => $label ?: '-- select a value --'] + $tail;}

/**
 * 2015-02-11 Превращает массив вида ['value' => 'label'] в массив вида [['value' => '', 'label' => '']].
 * Обратная операция: @see df_options_to_map()
 * @see df_map_to_options_t()
 * @used-by df_a_to_options()
 * @used-by df_countries_options()
 * @used-by df_currencies_options()
 * @uses df_option()
 * @param array(string|int => string) $m
 * @return array(array(string => string|int))
 */
function df_map_to_options(array $m) {return array_map('df_option', array_keys($m), $m);}

/**
 * 2015-11-13 Делает то же, что и @see df_map_to_options(), но дополнительно локализует значения label'.
 * @used-by \Df\Config\Source::toOptionArray()
 * @used-by \Df\Directory\FE\Currency::getValues()
 * @used-by \Dfe\Frontend\ConfigSource\Visibility\Product\VD::toOptionArray()
 * @uses df_option()
 * @param array(string|int => string) $m
 * @return array(array(string => string|int))
 */
function df_map_to_options_t(array $m) {return array_map('df_option', array_keys($m), df_translate_a($m));}

/**
 * 2015-02-11
 * Эта функция равноценна вызову df_map_to_options(array_flip($map))
 * Превращает массив вида ['label' => 'value'] в массив вида [['value' => '', 'label' => '']].
 * 2019-05-01 Currently, it is not used.
 * @uses df_option()
 * @param array(string|int => string) $map
 * @return array(array(string => string|int))
 */
function df_map_to_options_reverse(array $map) {return array_map('df_option', $map, array_keys($map));}

/**
 * @used-by df_map_to_options()
 * @used-by df_map_to_options_reverse()
 * @used-by df_map_to_options_t()
 * @param string|int $v
 * @param string $l
 * @return array(string => string|int)
 */
function df_option($v, $l) {return ['label' => $l, 'value' => $v];}

/**
 * 2019-05-01 Currently, it is not used.
 * @param array(string => string) $o
 * @param string|null|callable $d [optional]
 * @return string|null
 */
function df_option_v(array $o, $d = null) {return dfa($o, 'value', $d);}

/**
 * 2019-05-01 Currently, it is not used.
 * Превращает массив вида [['value' => '', 'label' => '']] в массив вида ['value'].
 * @param array(string => string) $oo
 * @return string[]
 */
function df_option_values(array $oo) {return array_column($oo, 'value');}

/**
 * 2017-06-25 It translates labels of given options.
 * @used-by \Dfr\Email\Plugin\Model\ResourceModel\Template\Collection::afterToOptionArray()
 * @param array(array(string => string)) $oo
 * @return array(array(string => string|Phrase))
 */
function df_options_t(array $oo) {return array_map(function($o) {return
	['label' => __($o['label'])] +  $o
;}, $oo);}

/**
 * Превращает массив вида [['value' => '', 'label' => '']] в массив вида ['value' => 'label'].
 * Обратная операция: @see df_map_to_options()
 * 2019-05-01 Currently, it is not used.
 * @param array(array(string => string|int)) $options
 * @return array(string|int => string)
 */
function df_options_to_map(array $options) {return array_column($options, 'label', 'value');}

/**
 * 2015-11-17
 * @used-by \Df\Config\Source\EnableYN::toOptionArray()
 * @used-by \Df\Framework\Form\Element\Fieldset::yesNo()
 * @return array(array(string => string|int))
 */
function df_yes_no() {/** @var YN $o */$o = df_o(YN::class); return $o->toOptionArray();}