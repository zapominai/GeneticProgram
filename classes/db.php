<?php

class db {
	const JSON_MODE_AUTO          = 0;
	const JSON_MODE_REPLACE_QUOTE = 1;
	const JSON_MODE_WIN2UTF       = 2;
	const JSON_MODE_SPECIALCHARS  = 4;
	const JSON_MODE_STRIP_TAGS    = 8;
	const JSON_MODE_ADD_SLASHES   = 16;
	const JSON_MODE_RAW           = 32;

	const JSON_MODE_AJAX = 11;
		/*
		db::JSON_MODE_REPLACE_QUOTE |
		db::JSON_MODE_WIN2UTF       |
		// db::JSON_MODE_SPECIALCHARS | // 15
		db::JSON_MODE_STRIP_TAGS;
		*/
	const JSON_MODE_DECLARE = 27;
		/*
		db::JSON_MODE_REPLACE_QUOTE | 
		db::JSON_MODE_WIN2UTF       |
		//db::JSON_MODE_SPECIALCHARS  | //31 
		db::JSON_MODE_STRIP_TAGS    |
		db::JSON_MODE_ADD_SLASHES;
		*/

	const JSON_MODE_DECLARE_RAW = 19;
		/*
		db::JSON_MODE_REPLACE_QUOTE |
		db::JSON_MODE_WIN2UTF       |
		db::JSON_MODE_ADD_SLASHES;
		*/

	public static function collection2json($mode, $collectionIm) {
        if (!$collectionIm) return null;

        $collection = unserialize(serialize($collectionIm)); // чтобы walk не похерил связи

        if ($mode & db::JSON_MODE_REPLACE_QUOTE) Utils::walk_collection($collection, json_replace_quote);
        if ($mode & db::JSON_MODE_STRIP_TAGS   ) Utils::walk_collection($collection, strip_tags        );
        if ($mode & db::JSON_MODE_SPECIALCHARS ) Utils::walk_collection($collection, htmlspecialchars  );

        $collection = json_encode($collection);

        $collection = str_replace("'", "\\u0027", $collection);
        $collection = str_replace(";", "\\u003B", $collection);

        if ($mode & db::JSON_MODE_ADD_SLASHES) return myaddslashes($collection);

        return $collection;
	}

	public static function lastId() {
		global $pdo;
		return $pdo->lastInsertId();
	}

    public static function json($mode, $sql, $params = array()) {
		$args = func_get_args();

		$params = !is_array($params) ? Utils::params2array(func_num_args(), $args, 2) : $params;
		$result = db::query($sql, $params);

		return $result ? db::collection2json($mode, $result) : '[]';
	}

    public static function queryVal($sql, $params = array()) {
		$args = func_get_args();

		$params = !is_array($params) ? Utils::params2array(func_num_args(), $args) : $params;
		$result = db::queryRow($sql, $params);

		if ($result) {
			$key = key($result);
			return $result->$key;
		} else {
			return null;
		}
	}

    public static function queryRow($sql, $params = array()) {
		$args = func_get_args();
		$params = !is_array($params) ? Utils::params2array(func_num_args(), $args) : $params;

		$result = db::query($sql, $params);

		if (count($result) > 0) {
			return $result[0];
		}

		return null;
	}

	// Выполняем запросы возвращаем массив объектов
	// Если записей нет, то вернётся пустой массив
    // возвращает null если ошибка (false нельзя, т.к. пустой массив тоже false)
	public static function query($sql, $param = array()) {
		global $pdo;

		$numArgs = func_num_args();
		$args = func_get_args();

		$param = !is_array($param) ? Utils::params2array($numArgs, $args) : $param;

        foreach ($param as $index => $value) {
            if (!is_array($value)) continue;

            $paramNames = [];
            foreach ($value as $i => $val) {
                $paramNames[] = $index . $i;
            }

            $inQuery = implode(',', $paramNames);
            $sql = str_replace($index, $inQuery, $sql);
        }

		$stmt = $pdo->prepare($sql);

		foreach ($param as $index => $value) {
			if (is_numeric($index)) $index = intval($index) + 1; // Для не именованных параметров

            if (is_array($value)) {
                foreach ($value as $i => $val) {
                    db::bindParam($stmt, $index . $i, $val);
                }
            } else {
                db::bindParam($stmt, $index, $value);
            }
        }

		if ($stmt->execute()) {
			$result = $stmt->fetchAll(PDO::FETCH_CLASS);
			return $result;
		}

        $errorInfo = $stmt->errorInfo();

        ob_start();
        var_dump($sql);
        var_dump($param);
        $stmt->debugDumpParams();
        $query = ob_get_clean();

        log::error('Ошибка в запросе: %s (%s | %s | %s)',
            $query, $errorInfo[0], $errorInfo[1], $errorInfo[2]
        );

        return null;
	}

    /**
     * @param PDOStatement $stmt
     * @param $index
     * @param $value
     */
    public static function bindParam(&$stmt, $index, $value) {
        $stmt->bindValue($index, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
}

// Для подготовки json
function json_replace_quote(&$string) {
	$string = str_replace('\\"', "'", $string);
	return str_replace("\\'", "'", $string);
}

function myaddslashes(&$string) {
	return str_replace("\\", "\\\\", $string);
}