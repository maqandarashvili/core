<?php
namespace Df\Core\Model\Text;
class Regex extends \Df\Core\O {
	/**
	 * @used-by rm_preg_match()
	 * @used-by rm_preg_match_int()
	 * Возвращает:
	 * 1) string, если текст соответствует регулярному выражению
	 * 2) string[], если текст соответствует регулярному выражению,
	 * и регулярное выражение содержит несколько пар круглых скобок.
	 * 3) null, если текст не соответствует регулярному выражению
	 * 4) false, если при соответствии произошёл внутренний сбой функции @see preg_match()
	 * @throws \Exception
	 * @return string|string[]|null|bool
	 */
	public function match() {
		if (!isset($this->{__METHOD__})) {
			/** @var string|null|bool $result */
			/** @var int|bool $matchResult */
			/** @var string[] $matches */
			// Собачка нужна, чтобы подавить warning.
			$matchResult = @preg_match($this->getPattern(), $this->getSubject(), $matches);
			if (false !== $matchResult) {
				if (1 === $matchResult) {
					/**
					 * Раньше тут стояло:
					 * $result = df_a($matchResult, 1);
					 * что не совсем правильно,
					 * потому что если регулярное выражение не содержит круглые скобки,
					 * то результирующий массив будет содержать всего один элемент.
					 * ПРИМЕР
					 * регулярное выражение: #[А-Яа-яЁё]#mu
					 * исходный текст: Категория Яндекс.Маркета
					 * результат: Array([0] => К)
					 *
					 * 2015-03-23
					 * Добавил поддержку нескольких пар круглых скобок.
					 */
					$result = count($matches) < 3 ? rm_last($matches) : rm_tail($matches);
				}
				else {
					if (!$this->needThrowOnNotMatch()) {
						$result = null;
					}
					else {
						$this->throwNotMatch();
					}
				}
			}
			else {
				if ($this->needThrowOnError()) {
					$this->throwInternalError();
				}
				else {
					$result = false;
				}
			}
			$this->{__METHOD__} = rm_n_set($result);
		}
		return rm_n_get($this->{__METHOD__});
	}

	/** @return int|null|bool */
	public function matchInt() {
		/** @var string|int|null|bool $matchedResult */
		$result = $this->match();
		if ($this->test() && !ctype_digit($result)) {
			$this->throwNotMatch();
		}
		return (int)$result;
	}

	/** @return bool */
	public function test() {
		if (!isset($this->{__METHOD__})) {
			$this->{__METHOD__} = !is_null($this->match()) && (false !== $this->match());
		}
		return $this->{__METHOD__};
	}

	/** @return string */
	private function getPattern() {return $this[self::$P__PATTERN];}

	/** @return bool */
	private function getReportFileName() {return 'regular-expression-subject.txt';}

	/** @return bool */
	private function getReportFilePath() {
		return df_concat_path(BP, 'var', 'log', $this->getReportFileName());
	}

	/** @return string */
	private function getSubject() {return $this[self::$P__SUBJECT];}

	/** @return int */
	private function getSubjectMaxLinesToReport() {return 5;}

	/** @return string */
	private function getSubjectReportPart() {
		if (!isset($this->{__METHOD__})) {
			$this->{__METHOD__} =
				!$this->isSubjectTooLongToReport()
				? $this->getSubject()
				: df_concat_n(array_slice(
					$this->getSubjectSplitted(), 0, $this->getSubjectMaxLinesToReport()
				))
			;
		}
		return $this->{__METHOD__};
	}

	/** @return string[] */
	private function getSubjectSplitted() {
		if (!isset($this->{__METHOD__})) {
			$this->{__METHOD__} = df_explode_n($this->getSubject());
		}
		return $this->{__METHOD__};
	}

	/** @return bool */
	private function isSubjectMultiline() {
		if (!isset($this->{__METHOD__})) {
			$this->{__METHOD__} = df_t()->isMultiline($this->getSubject());
		}
		return $this->{__METHOD__};
	}

	/** @return bool */
	private function isSubjectTooLongToReport() {
		if (!isset($this->{__METHOD__})) {
			$this->{__METHOD__} =
					$this->isSubjectMultiline()
				&&
					($this->getSubjectMaxLinesToReport() < count($this->getSubjectSplitted()))
			;
		}
		return $this->{__METHOD__};
	}

	/** @return bool */
	private function needThrowOnError() {return $this[self::$P__THROW_ON_ERROR];}

	/** @return bool */
	private function needThrowOnNotMatch() {return $this[self::$P__THROW_ON_NOT_MATCH];}

	/**
	 * @throws \Exception
	 * @return void
	 */
	private function throwInternalError() {
		/** @var int $numericCode */
		$numericCode = preg_last_error();
		/** @var string $errorCodeForUser */
		if (!$numericCode) {
			/**
			 * Обратите внимание, что при простых сбоях
			 * @see preg_last_error() возвращает 0 (PREG_NO_ERROR).
			 * Например, при таком:
			 * rm_preg_test('#(#', 'тест');
			 */
			$errorCodeForUser = '';
		}
		else {
			/**
			 * А вот при сложных сбоях
			 * @see preg_last_error() возвращает уже какой-то полезный для диагностики код.
			 * Пример из документации:
			 * rm_preg_test('/(?:\D+|<\d+>)*[!?]/', 'foobar foobar foobar');
			 * http://php.net/manual/function.preg-last-error.php
			 */
			/** @var string|null $textCode */
			$textCode = $this->translateErrorCode($numericCode);
			$errorCodeForUser = ' ' . ($textCode ? $textCode : 'с кодом ' . $numericCode);
		}
		/** @var string $message */
		if (!$this->isSubjectMultiline()) {
			$message = strtr(
				"При применении регулярного выражения «{pattern}» к строке «{subject}»"
				." произошёл сбой{errorCodeForUser}."
				,array(
					'{pattern}' => $this->getPattern()
					, '{subject}' => $this->getSubject()
					, '{errorCodeForUser}' => $errorCodeForUser
				)
			);
		}
		else {
			if (!$this->isSubjectTooLongToReport()) {
				$message = strtr(
					"При применении регулярного выражения «{pattern}»"
					." произошёл сбой{errorCodeForUser}."
					."\nТекст, к которому применялось регулярное выражение:"
					."\nНАЧАЛО ТЕКСТА:\n{subjectToReport}\nКОНЕЦ ТЕКСТА"
					,array(
						'{pattern}' => $this->getPattern()
						,'{errorCodeForUser}' => $errorCodeForUser
						,'{subjectToReport}' => $this->getSubject()
					)
				);
			}
			else {
				rm_report($this->getReportFileName(), $this->getSubject());
				$message = strtr(
					"При применении регулярного выражения «{pattern}»"
					." произошёл сбой{errorCodeForUser}."
					."\nТекст, к которому применялось регулярное выражение,"
					." смотрите в файле {reportFilePath}."
					."\nПервые {reportMaxLines} строк текста:"
					."\nНАЧАЛО:\n{subjectToReport}\nКОНЕЦ"
					,array(
						'{pattern}' => $this->getPattern()
						,'{errorCodeForUser}' => $errorCodeForUser
						,'{reportFilePath}' => $this->getReportFilePath()
						,'{reportMaxLines}' => $this->getSubjectMaxLinesToReport()
						,'{subjectToReport}' => $this->getSubjectReportPart()
					)
				);
			}
		}
		df_error($message);
	}

	/**
	 * @throws \Exception
	 * @return void
	 */
	private function throwNotMatch() {
		/** @var string $message */
		if (!$this->isSubjectMultiline()) {
			$message = strtr(
				"Строка «{subject}» не отвечает регулярному выражению «{pattern}»."
				,array(
					'{pattern}' => $this->getPattern()
					, '{subject}' => $this->getSubject()
				)
			);
		}
		else {
			if (!$this->isSubjectTooLongToReport()) {
				$message = strtr(
					"Указанный ниже текст не отвечает регулярному выражению «{pattern}»:"
					."\nНАЧАЛО ТЕКСТА:\n{subjectToReport}\nКОНЕЦ ТЕКСТА"
					,array(
						'{pattern}' => $this->getPattern()
						,'{subjectToReport}' => $this->getSubject()
					)
				);
			}
			else {
				rm_report($this->getReportFileName(), $this->getSubject());
				$message = strtr(
					"Текст не отвечает регулярному выражению «{pattern}»."
					."\nТекст смотрите в файле {reportFilePath}."
					."\nПервые {reportMaxLines} строк текста:"
					."\nНАЧАЛО:\n{subjectToReport}\nКОНЕЦ"
					,array(
						'{pattern}' => $this->getPattern()
						,'{reportFilePath}' => $this->getReportFilePath()
						,'{reportMaxLines}' => $this->getSubjectMaxLinesToReport()
						,'{subjectToReport}' => $this->getSubjectReportPart()
					)
				);
			}
		}
		df_error($message);
	}

	/**
	 * @param int $errorCode
	 * @return string|null
	 */
	private function translateErrorCode($errorCode) {return df_a(self::getErrorCodeMap(), $errorCode);}

	/** @var string */
	private static $P__PATTERN = 'pattern';
	/** @var string */
	private static $P__SUBJECT = 'subject';
	/** @var string */
	private static $P__THROW_ON_ERROR = 'throw_on_error';
	/** @var string */
	private static $P__THROW_ON_NOT_MATCH = 'throw_on_not_match';

	/**
	 * @param string $pattern
	 * @param string $subject
	 * @param bool $throwOnError [optional]
	 * @param bool $throwOnNotMatch [optional]
	 * @return \Df\Core\Model\Text\Regex
	 */
	public static function i($pattern, $subject, $throwOnError = true, $throwOnNotMatch = false) {
		return new self(array(
			self::$P__PATTERN => $pattern
			, self::$P__SUBJECT => $subject
			, self::$P__THROW_ON_ERROR => $throwOnError
			, self::$P__THROW_ON_NOT_MATCH => $throwOnNotMatch
		));
	}

	/**
	 * Возвращает соответствие между числовыми кодами,
	 * возвращаемыми функцией @see preg_last_error(),
	 * и их символьными соответствиями:
		PREG_NO_ERROR
		PREG_INTERNAL_ERROR
		PREG_BACKTRACK_LIMIT_ERROR
		PREG_RECURSION_LIMIT_ERROR
		PREG_BAD_UTF8_ERROR
		PREG_BAD_UTF8_OFFSET_ERROR
	 * @return array(int => string)
	 */
	private static function getErrorCodeMap() {
		/** @var array(int => string) $result */
		static $result;
		if (!$result) {
			/** @var array(string => array(string => int)) $constants */
			$constants = get_defined_constants(true);
			foreach ($constants['pcre'] as $textCode => $numericCode) {
				/** @var string $textCode */
				/** @var int $numericCode */
				if (rm_ends_with($textCode, '_ERROR')) {
					$result[$numericCode] = $textCode;
				}
			}
		}
		return $result;
	}
}